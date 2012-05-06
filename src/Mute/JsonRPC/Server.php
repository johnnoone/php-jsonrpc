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

namespace Mute\JsonRPC;

use Exception;
use Mute\JsonRPC\Server\ControllerResolver;
use Mute\JsonRPC\Server\Parser;
use Mute\JsonRPC\Server\Formatter;
use Mute\JsonRPC\Util;

class Server
{
    public $controllers;
    public $parser;
    public $formatter;

    public function __construct(ControllerResolver $controllers=null)
    {
        $this->controllers = $controllers
            ? $controllers
            : new ControllerResolver;
        $this->parser = new Parser;
        $this->formatter = new Formatter;
    }

    public function handle($data)
    {
        $batch = false;
        $responses = array();
        try {
            list($batch, $requests) = $this->parse($data);
            foreach ($requests as &$request) {
                try {
                    $response = $this->resolve($request);
                }
                catch (Exceptions\RPCError $response) {}
                $responses[] = $this->format($response, $request);
            }
        }
        catch (Exceptions\RPCError $exception) {
            $batch = false;
            $responses[] = $this->format($exception);
        }
        catch (Exception $exception) {
            $batch = false;
            $exception = new Exceptions\ServerError(null, $exception);
            $responses[] = $this->format($exception);
        }

        $output = $batch
            ? array_values(array_filter($responses))
            : current($responses);

        return Util::jsonEncode($output);
    }

    public function parse($data)
    {
        return $this->parser->parse($data);
    }

    public function resolve($request)
    {
        if (!is_array($request)) throw new Exceptions\InvalidRequest;

        $method = null;
        $params = array();
        extract($request);

        if (!$method or !is_array($params)) throw new Exceptions\InvalidRequest;

        try {
            return $this->controllers->resolve($method, $params);
        }
        catch (Exceptions\RPCError $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            throw new Exceptions\ServerError(null, $exception);
        }
    }

    public function format($response, $request=null)
    {
        return $this->formatter->format($response, $request);
    }
}
