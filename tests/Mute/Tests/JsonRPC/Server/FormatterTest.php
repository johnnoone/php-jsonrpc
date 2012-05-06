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

namespace Mute\Tests\JsonRPC\Server;

use PHPUnit_Framework_TestCase;
use Mute\JsonRPC\Exceptions\RPCError;
use Mute\JsonRPC\Server\Formatter;

class FormatterTest extends PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $formatter = new Formatter;
        $data = $formatter->format('toto', array('id' => 'foo'));
        $this->assertEquals(array(
            "jsonrpc" => "2.0",
            "id" => "foo",
            "response" => "toto",
        ), $data);

        $data = $formatter->format(new RPCError, array('id' => 'foo'));
        $this->assertEquals(array(
            "jsonrpc" => "2.0",
            "id" => "foo",
            "error" => array(
                'code' => -32000,
                'message' => 'Server error'
            ),
        ), $data);

        $data = $formatter->format(new \Exception, array('id' => 'foo'));
        $this->assertEquals(array(
            "jsonrpc" => "2.0",
            "id" => "foo",
            "error" => array(
                'code' => -32603,
                'message' => 'Internal error',
            ),
        ), $data);

        $data = $formatter->format(new \Exception('jo'), array('id' => 'foo'));
        $this->assertEquals(array(
            "jsonrpc" => "2.0",
            "id" => "foo",
            "error" => array(
                'code' => -32603,
                'message' => 'Internal error',
                'data' => array(
                    'message' => 'jo'
                )
            ),
        ), $data);

        $data = $formatter->format(new \Exception('foo', null, new \Exception('bar')), array('id' => 'foo'));
        $this->assertEquals(array(
            "jsonrpc" => "2.0",
            "id" => "foo",
            "error" => array(
                'code' => -32603,
                'message' => 'Internal error',
                'data' => array(
                    'message' => 'foo',
                    'data' => array(
                        'message' => 'bar',
                    )
                )
            ),
        ), $data);
    }

    public function testFormatException()
    {
        $formatter = new Formatter;

        $exception = $this->getMock('Exception', array('jsonSerialize'));
        $exception->expects($this->any())
            ->method('jsonSerialize')
            ->will($this->returnValue(42));

        $data = $formatter->formatException($exception);
        $this->assertEquals(42, $data);

        $exception = $this->getMock('Exception', array('getData'));
        $exception->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(42));

        $data = $formatter->formatException($exception);
        $this->assertEquals(array('data' => 42), $data);

        $exception = new \Exception('foo');

        $data = $formatter->formatException($exception);
        $this->assertEquals(array('message' => 'foo'), $data);
    }

    public function testFormatNull()
    {
        $formatter = new Formatter;
        $data = $formatter->format(null);
        $this->assertNull($data);
    }

    public function testFormatEmpty()
    {
        $formatter = new Formatter;
        $data = $formatter->format(null, array('id' => null));
        $this->assertNull($data);
    }
}
