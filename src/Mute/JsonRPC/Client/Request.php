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

namespace Mute\JsonRPC\Client;

class Request
{
    protected static $zid = 1;

    protected $id;
    protected $method;
    protected $params;
    protected $notification;

    function __construct($method, array $params = null, $notification = false)
    {
        $this->id = null;
        $this->method = $method;
        $this->params = $params;
        $this->notification = $notification;
    }

    public function __clone()
    {
        $this->id = null;
    }

    public function getId()
    {
        if (!$this->notification) {
            if (!$this->id) {
                $this->id = self::$zid++;
            }
            return $this->id;
        }
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function isNotification()
    {
        return (bool) $this->notification;
    }

    public function jsonSerialize()
    {
        $data = array(
            'jsonrpc' => '2.0',
            'id' => $this->getId(),
            'method' => $this->getMethod(),
            'params' => $this->getParams(),
        );

        return array_filter($data);
    }
}
