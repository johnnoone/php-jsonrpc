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

class HttpTransport implements TransportInterface
{
    function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function call($request)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->uri);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request . "\r\n\r\n");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-length: ' . strlen($request)));

        try {
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new CurlError($ch);
            }
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 != $http_code) {
                throw new HttpError;
            }

            curl_close($ch);
        }
        catch (\Exception $e) {
            if (!$e instanceof CallError) {
                $e = new CallError(null, null, $e);
            }
            throw $e;
        }

        return $response;
    }
}

