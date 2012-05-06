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
use Mute\JsonRPC\Exceptions\ParseError;
use Mute\JsonRPC\Exceptions\InvalidRequest;
use Mute\JsonRPC\Exceptions\RPCError;
use Mute\JsonRPC\Util;

class Formatter
{
    public function format($response, $request=null)
    {
        if ($request && empty($request['id'])) {
            // notification, just pass
            return null;
        }
        if ($response instanceof RPCError) {
            return array(
                'jsonrpc' => '2.0',
                'id' => @$request['id'],
                'error' => $response->jsonSerialize(),
            );
        }
        if ($response instanceof Exception) {
            $data = $this->formatException($response);

            $response = array(
                'jsonrpc' => '2.0',
                'id' => @$request['id'],
                'error' => array(
                    'code' => -32603,
                    'message' => 'Internal error',
                ),
            );
            if ($data) {
                $response['error']['data'] = $data;
            }

            return $response;
        }
        if ($request) {
            return array(
                'jsonrpc' => '2.0',
                'id' => @$request['id'],
                'response' => $response
            );
        }

        return null;
    }

    public function formatException(Exception $exception=null)
    {
        if ($exception) {
            if (is_callable(array($exception, 'jsonSerialize'))) {

                return $exception->jsonSerialize();
            }

            if (is_callable(array($exception, 'getData'))) {
                $data = $exception->getData();
            }
            else {
                $data = $this->formatException($exception->getPrevious());
            }

            $output = array(
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'data' => $data,
            );

            $output = array_filter($output);
            if ($output) {
                return $output;
            }
        }
    }
}
