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

namespace Mute\Tests\JsonRPC;

use PHPUnit_Framework_TestCase;
use Mute\JsonRPC\Server;

class ServerTest extends PHPUnit_Framework_TestCase
{
    public function testServer()
    {
        $server = new Server;
        $server->controllers->expose('foo', function () { return 'bar'; });
        $response = json_decode($server->handle('foo'), true);
        $expected = json_decode('{"jsonrpc":"2.0","id":null,"error":{"code":-32700,"message":"Parse error"}}', true);
        $this->assertEquals($expected, $response);

        $response = json_decode($server->handle('{"jsonrpc":"2.0", "method": "foo", "id": "bar"}'), true);
        $expected = json_decode('{"jsonrpc":"2.0","id":"bar","response":"bar"}', true);
        $this->assertEquals($expected, $response);

        $response = json_decode($server->handle('[{"jsonrpc":"2.0", "method": "foo", "id": "bar"}, {"jsonrpc":"2.0", "method": "foo", "id": "baz"}]'), true);
        $expected = json_decode('[{"jsonrpc":"2.0","id":"bar","response":"bar"}, {"jsonrpc":"2.0","id":"baz","response":"bar"}]', true);
        $this->assertEquals($expected, $response);

        $response = json_decode($server->handle('[{"jsonrpc":"2.0", "method": "foo"}, {"jsonrpc":"2.0", "method": "foo", "id": "baz"}]'), true);
        $expected = json_decode('[{"jsonrpc":"2.0","id":"baz","response":"bar"}]', true);

        $this->assertEquals($expected, $response);
    }

    public function testResolveRPCError()
    {
        $this->setExpectedException('Mute\JsonRPC\Exceptions\RPCError');
        $server = new Server;
        $server->controllers->expose('foo', function () {
            throw new \Mute\JsonRPC\Exceptions\RPCError;
        });
        $server->resolve(array('method' => 'foo'));
    }

    public function testResolveException()
    {
        $this->setExpectedException('Mute\JsonRPC\Exceptions\ServerError');
        $controllers = $this->getMockBuilder('Mute\JsonRPC\Server\ControllerResolver', 'resolve')
            ->disableOriginalConstructor()
            ->getMock();
        $controllers->expects($this->any())
            ->method('resolve')
            ->will($this->returnCallback(function() {
                throw new \Exception;
            }));
        $server = new Server($controllers);
        $server->resolve(array('method' => 'foo'));
    }

    public function testServerError()
    {
        $controllers = $this->getMockBuilder('Mute\JsonRPC\Server\ControllerResolver', 'resolve')
            ->disableOriginalConstructor()
            ->getMock();
        $controllers->expects($this->any())
            ->method('resolve')
            ->will($this->returnCallback(function() {
                throw new \Exception;
            }));
        $server = new Server($controllers);
        $response = json_decode($server->handle(array('method' => 'foo')), true);
        $expected = json_decode('{"jsonrpc":"2.0","id":null,"error":{"code":-32000,"message":"Server error"}}', true);

        $this->assertEquals($expected, $response);
    }
}
