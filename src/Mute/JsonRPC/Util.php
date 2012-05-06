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
use Mute\JsonRPC\Exceptions\InternalError;
use Mute\JsonRPC\Exceptions\InvalidParams;
use Mute\JsonRPC\Exceptions\InvalidRequest;
use Mute\JsonRPC\Exceptions\InternalRequest;
use Mute\JsonRPC\Exceptions\MethodNotFound;
use Mute\JsonRPC\Exceptions\ParseError;
use Mute\JsonRPC\Exceptions\RPCError;
use Mute\JsonRPC\Exceptions\ServerError;

class Util
{
    public static function jsonEncode($data)
    {
        $data = self::jsonInspect($data);

        return json_encode($data);
    }

    protected static function jsonInspect($data)
    {
        if (is_object($data) && is_callable(array($data, 'jsonSerialize'))) {
            $data = $data->jsonSerialize();
        }
        elseif (is_array($data) or $data instanceof Traversable) {
            foreach ($data as $k => &$value) {
               $value = self::jsonInspect($value);
           }
        }

        return $data;
    }

    public static function jsonDecode($data)
    {
        $data = json_decode($data, true);
        $data = self::jsonDeflate($data);

        return $data;
    }


    public static function jsonDeflate($data, $root=null)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::jsonDeflate($value, $key);
            }
            if ($root == 'error' && array_key_exists('code', $data)) {
                $add_except = null;
                $add_data = null;
                if (array_key_exists('data', $data)) {
                    if ($data['data'] instanceof Exception) {
                        $add_except = $data['data'];
                    } else {
                        $add_data = $data['data'];
                    }
                }
                $code = $data['code'];
                if ($code == -32700) {
                    $new = new ParseError($add_data, $add_except);
                }
                elseif ($code == -32600) {
                    $new = new InvalidRequest($add_data, $add_except);
                }
                elseif ($code == -32601) {
                    $new = new MethodNotFound($add_data, $add_except);
                }
                elseif ($code == -32602) {
                    $new = new InvalidParams($add_data, $add_except);
                }
                elseif ($code == -32603) {
                    $new = new InternalError($add_data, $add_except);
                }
                elseif (-32000 >= $code && $code >= -32099) {
                    $new = new ServerError($add_data, $add_except);
                }
                elseif ($add_data) {
                    $new = new RPCError($code, @$data['message'], $add_data, $add_except);
                }
                else {
                    $new = new Exception($code, @$data['message'], $add_except);
                }

                $data = $new;
            }
        }

        return $data;
    }

    public static function isList(array $data = null)
    {
        if ($data) {
            $keys = array_filter(array_keys($data), 'is_int');
            return count($data) === count($keys);
        }

        return false;
    }
}
