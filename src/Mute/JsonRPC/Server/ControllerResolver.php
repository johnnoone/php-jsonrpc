<?php

/**
 * This file is part of Mute\JsonRPC.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Xavier Barbosa <clint.northwood@gmail.com>
 * @copyright Copyright (c) 2012, Xavier Barbosa
 * @license http://www.gnu.org/licenses/gpl.html
 **/

namespace Mute\JsonRPC\Server;

use Exception;
use Mute\JsonRPC\Util;
use Mute\JsonRPC\Exceptions\InternalError;
use Mute\JsonRPC\Exceptions\InvalidParams;
use Mute\JsonRPC\Exceptions\MethodNotFound;
use Mute\JsonRPC\Exceptions\RPCError;

class ControllerResolver
{
    protected $controllers;
    protected $defaults;

    public function __construct()
    {
        $this->controllers = array();
        $this->defaults = array();
    }

    public function expose($method, $controller, array $defaults=null)
    {
        $this->controllers[$method] = $controller;
        $this->defaults[$method] = $defaults;

        return $this;
    }

    public function resolve($method, array $params=null)
    {
        if (false == array_key_exists($method, $this->controllers)) {
            throw new MethodNotFound;
        }
        $controller = $this->controllers[$method];

        if (is_string($controller) && strpos($controller, '::')) {
            $controller = explode('::', $controller, 2);
            $a = 'toto';
        }

        // a closure
        if ($controller instanceof \Closure) {
            $reflection = new \ReflectionFunction($controller);
        }

        // a class
        elseif (is_array($controller)) {
            list($obj, $action) = $controller;
            $reflection = new \ReflectionMethod($obj, $action);
            if (false == $reflection->isStatic() && is_string($obj)) {
                $controller[0] = new $obj;
            }
        }

        // a function
        elseif (is_string($controller)) {
            $reflection = new \ReflectionFunction($controller);
        }
        else {
            throw new InternalError(array(
                'invalid callable' => $controller
            ));
        }

        $defaults = $this->defaults[$method];

        try {
            $arguments = $this->arguments($reflection, $params, $defaults);
            return call_user_func_array($controller, $arguments);
            return $this->render($reflection, $params, $defaults);
        }
        catch (RPCError $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            throw new InternalError(null, $exception);
        }
    }

    protected function arguments(\ReflectionFunctionAbstract $controller, array $params=null, array $defaults=null)
    {
        $arguments = array();

        if (Util::isList($defaults)) {
            $arguments = array_merge($arguments, array_values($defaults));
            $defaults = array();
        } else {
            $defaults = (array) $defaults;
        }

        if (Util::isList($params)) {
            $arguments = array_merge($arguments, array_values($params));
            $params = array();
        } else {
            $params = (array) $params;
        }

        $params = array_merge($defaults, $params);

        $count = $controller->getNumberOfParameters();
        if ($count > count($arguments)) {
            foreach ($controller->getParameters() as $parameter) {
                $position = $parameter->getPosition();
                $method = $parameter->getName();
                if (array_key_exists($method, $params)) {
                    $arguments[$position] = $params[$method];
                    unset($params[$method]);
                    continue;
                }
                elseif (!isset($arguments[$position])) {
                    if ($parameter->isDefaultValueAvailable()) {
                       $arguments[$position] = $parameter->getDefaultValue();
                       continue;
                    }

                    throw new InvalidParams;
                }
            }
        }

        return $arguments;
    }
}
