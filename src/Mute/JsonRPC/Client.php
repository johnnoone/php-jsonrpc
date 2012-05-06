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

use Mute\JsonRPC\Util;

class Client
{
    protected $transport;

    public function __construct($transport)
    {
        if ($transport instanceof Client\TransportInterface) {
            $this->transport = $transport;
        } else {
            // obviously it's an uri
            $this->transport = new Client\HttpTransport($transport);
        }
    }

    public function request($method, array $params=null, $notification = false)
    {
        $request = $this->prepare($method, $params, $notification);
        $contents = $this->call($request);

        return $this->parseResponse($contents);
    }

    public function notify($method, array $params = null)
    {
        return $this->request($method, $params, true);
    }

    public function prepare($method, array $params = null, $notification = false)
    {
        if ($method instanceof Client\Request) {
            return clone $method;
        }
        if (is_array($method)) {
            $args = $method + array('', null, false);
            list($method, $params, $notification) = $args;
        }
        return new Client\Request($method, $params, $notification);
    }

    public function batch(array $requests)
    {
        foreach ($requests as $i => $request) {
            $requests[$i] = $this->prepare($request);
        }

        $contents = $this->call(new Client\Batch($requests));
        $responses = $this->parseResponse($contents);

        if ($contents && !$responses) foreach ($requests as $key => $request) {
            $id = $request->isNotification() ? null : $request->getId();
            $response = null;
            foreach ($contents as $i => $content) if ($id == $content['id']) {
                $response = $this->parseResponse($content);
                unset($contents[$i]);
                break;
            }

            $responses[$key] = $response;
        }

        return $responses;
    }

    protected function call($data)
    {
        $request = Util::jsonEncode($data);
        $response = $this->transport->call($request);

        return Util::jsonDecode($response, true);
    }

    protected function parseResponse($response)
    {
        if (!$response) {
            return;
        }
        if (array_key_exists('error', $response)) {
            return $response['error'];
        }
        if (array_key_exists('result', $response)) {
            return $response['result'];
        }
    }
}
