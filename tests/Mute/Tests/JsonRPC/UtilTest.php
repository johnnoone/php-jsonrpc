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
use Mute\JsonRPC\Util;

class JsonSerializable
{
    public function __construct($data)
    {
        $this->data = $data;
    }
    function jsonSerialize()
    {
        return $this->data;
    }
}

class UtilTest extends PHPUnit_Framework_TestCase
{
    public function testDecode()
    {
        $data = array(
            'toto' => 'foo'
        );

        $json = json_encode($data);
        $response = Util::jsonDecode($json);
        $this->assertEquals(array(
            'toto' => 'foo'
        ), $response);

        $data = array(
            'error' => array(
                'code' => 1234
            )
        );
    }

    public function testEncode()
    {
        $mock = $this->getMock('Observer', array('jsonSerialize'));
        $mock->expects($this->any())
            ->method('jsonSerialize')
            ->will($this->returnValue(array('foo' => 'bar')));

        $data = array(
            'toto' => 'foo',
            'bar' => $mock
        );

        $response = Util::jsonEncode($data);
        $this->assertEquals('{"toto":"foo","bar":{"foo":"bar"}}', $response);
    }

    public function testParseError()
    {
        $data = array(
            'error' => array(
                'code' => -32700
            )
        );

        $response = Util::jsonDeflate($data);
        $this->assertInstanceOf('Mute\JsonRPC\Exceptions\ParseError', $response['error']);
    }

    public function testInvalidRequest()
    {
        $data = array(
            'error' => array(
                'code' => -32600
            )
        );

        $response = Util::jsonDeflate($data);
        $this->assertInstanceOf('Mute\JsonRPC\Exceptions\InvalidRequest', $response['error']);
    }

    public function testMethodNotFound()
    {
        $data = array(
            'error' => array(
                'code' => -32601
            )
        );

        $response = Util::jsonDeflate($data);
        $this->assertInstanceOf('Mute\JsonRPC\Exceptions\MethodNotFound', $response['error']);
    }

    public function testInvalidParams()
    {
        $data = array(
            'error' => array(
                'code' => -32602
            )
        );

        $json = json_encode($data);
        $response = Util::jsonDeflate($data);
        $this->assertInstanceOf('Mute\JsonRPC\Exceptions\InvalidParams', $response['error']);
    }

    public function testInternalError()
    {
        $data = array(
            'error' => array(
                'code' => -32603
            )
        );

        $response = Util::jsonDeflate($data);
        $this->assertInstanceOf('Mute\JsonRPC\Exceptions\InternalError', $response['error']);
    }

    public function testServerError()
    {
        $data = array(
            'error' => array(
                'code' => -32000
            )
        );

        $response = Util::jsonDeflate($data);
        $this->assertInstanceOf('Mute\JsonRPC\Exceptions\ServerError', $response['error']);
    }

    public function testRPCError()
    {
        $data = array(
            'error' => array(
                'code' => 42,
                'data' => 'foo'
            )
        );

        $response = Util::jsonDeflate($data);
        $this->assertInstanceOf('Mute\JsonRPC\Exceptions\RPCError', $response['error']);
    }

    public function testException()
    {
        $data = array(
            'error' => array(
                'code' => 42,
                'data' => new \Exception('foo')
            )
        );

        $response = Util::jsonDeflate($data);
        $this->assertInstanceOf('Exception', $response['error']);
    }

    public function testList()
    {
        $data = null;
        $response = Util::isList($data);
        $this->assertFalse($response);

        $data = array('foo', 'bar');
        $response = Util::isList($data);
        $this->assertTrue($response);

        $data = array('foo' => 0, 'bar' => 1);
        $response = Util::isList($data);
        $this->assertFalse($response);
    }
}
