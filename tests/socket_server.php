#!/usr/bin/env php
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

$port = ($argc == 2) ? $argv[1] : '9000';

$socket = stream_socket_server("tcp://0.0.0.0:$port", $errno, $errstr) or die("$errstr ($errno)\n");

echo "Server is running on 0.0.0.0:$port\n";

while ($conn = stream_socket_accept($socket, -1)) {
	$request = '';
    do {
        $request .= fread($conn, 1024);
    }
    while (substr($request, -4) !== "\r\n\r\n");


    $response = strpos($request, 'return error')

        ? "HTTP/1.1 500 Internal Server Error\r\n"
        . "Content-Type: text/plain;charset=utf-8\r\n"
        . "Content-Length: 15\r\n\r\n"
        . "error requested"

        : "HTTP/1.1 200 OK\r\n"
        . "Content-Type: text/plain;charset=utf-8\r\n"
        . "Content-Length: 4\r\n\r\n"
        . "foo!";

	fwrite($conn, $response);
	fclose($conn);

}
fclose($socket);
