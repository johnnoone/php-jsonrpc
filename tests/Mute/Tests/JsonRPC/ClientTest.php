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
use Mute\JsonRPC\Client\Request;
use Mute\JsonRPC\Client;

class ClientTest extends PHPUnit_Framework_TestCase
{
    function testUri()
    {
        $client = new Client('http://foo.bar.baz');
    }

    function testPositional()
    {
        $transport = $this->getMockBuilder('Mute\JsonRPC\Client\TransportInterface', 'call')
            ->disableOriginalConstructor()
            ->getMock();
        $transport->expects($this->any())
            ->method('call')
            ->will($this->returnCallback(function($request) {
                $data = json_decode($request, true);
                $response = array(
                    'jsonrpc' => '2.0',
                    'result' => $data['params'][0] - $data['params'][1],
                    'id' => 1);

                return json_encode($response);
            }));

        $client = new Client($transport);

        // --> {"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}
        // <-- {"jsonrpc": "2.0", "result": 19, "id": 1}

        $response = $client->request('substract', array(42, 23));
        $this->assertEquals($response, 19);
        // or
        $response = $client->request(new Request('substract', array(42, 23)));
        $this->assertEquals($response, 19);

        // --> {"jsonrpc": "2.0", "method": "subtract", "params": [23, 42], "id": 2}
        // <-- {"jsonrpc": "2.0", "result": -19, "id": 2}

        $response = $client->request('substract', array(23, 42));
        $this->assertEquals(-19, $response);
        // or
        $response = $client->request(new Request('substract', array(23, 42)));
        $this->assertEquals(-19, $response);
    }

    function testNamedArguments()
    {
        $transport = $this->getMockBuilder('Mute\JsonRPC\Client\TransportInterface', 'call')
            ->disableOriginalConstructor()
            ->getMock();
        $transport->expects($this->any())
            ->method('call')
            ->will($this->returnCallback(function($request) {
                $data = json_decode($request, true);
                $response = array(
                    'jsonrpc' => '2.0',
                    'result' => $data['params']['minuend'] - $data['params']['subtrahend'],
                    'id' => $data['id']);

                return json_encode($response);
            }));

        $client = new Client($transport);

        // --> {"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 3}
        // <-- {"jsonrpc": "2.0", "result": 19, "id": 3}

        $response = $client->request('substract', array('subtrahend' => 23, 'minuend' => 42));
        $this->assertEquals(19, $response);
        // or
        $response = $client->request(new Request('substract', array('subtrahend' => 23, 'minuend' => 42)));
        $this->assertEquals(19, $response);


        // --> {"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 4}
        // <-- {"jsonrpc": "2.0", "result": 19, "id": 4}

        $response = $client->request('substract', array('minuend' => 42, 'subtrahend' => 23));
        $this->assertEquals(19, $response);
        // or
        $response = $client->request(new Request('substract', array('subtrahend' => 23, 'minuend' => 42)));
        $this->assertEquals(19, $response);
    }

    function testNotification()
    {
        $transport = $this->getMockBuilder('Mute\JsonRPC\Client\TransportInterface', 'call')
            ->disableOriginalConstructor()
            ->getMock();
        $transport->expects($this->any())
            ->method('call')
            ->will($this->returnCallback(function($request) {
                return null;
            }));

        $client = new Client($transport);

        // --> {"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}

        $response = $client->notify('update', array(1, 2, 3, 4, 5));
        $this->assertEquals(null, $response);
        // or
        $response = $client->request('update', array(1, 2, 3, 4, 5), true);
        $this->assertEquals(null, $response);
        // or
        $response = $client->request(new Request('update', array(1, 2, 3, 4, 5), true));
        $this->assertEquals(null, $response);

        // --> {"jsonrpc": "2.0", "method": "foobar"}

        $response = $client->notify('foobar');
        $this->assertEquals(null, $response);
        // or
        $response = $client->request('foobar', null, true);
        $this->assertEquals(null, $response);
        // or
        $response = $client->request(new Request('foobar', null, true));
        $this->assertEquals(null, $response);
    }

    function testError()
    {
        $transport = $this->getMockBuilder('Mute\JsonRPC\Client\TransportInterface', 'call')
            ->disableOriginalConstructor()
            ->getMock();
        $transport->expects($this->any())
            ->method('call')
            ->will($this->returnCallback(function($request) {
                $data = json_decode($request, true);
                $response = array(
                    'jsonrpc' => '2.0',
                    'error' =>  array(
                        'code' => -32601,
                        'message' => 'Method not found'),
                    'id' => $data['id']);

                return json_encode($response);
            }));

        $client = new Client($transport);

        // --> {"jsonrpc": "2.0", "method": "foobar", "id": "1"}
        // <-- {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found."}, "id": "1"}

        $response = $client->request('foobar');
        $this->assertInstanceOf('Mute\JsonRPC\Exceptions\RPCError', $response);
    }

    function testBatch()
    {
        $transport = $this->getMockBuilder('Mute\JsonRPC\Client\TransportInterface', 'call')
            ->disableOriginalConstructor()
            ->getMock();
        $transport->expects($this->any())
            ->method('call')
            ->will($this->returnCallback(function($request) {
                $batch = json_decode($request, true);
                $response = array();
                foreach ($batch as $data) {
                    if ($data['method'] == 'sum') {
                        $response[] = array(
                            'jsonrpc' => '2.0',
                            'result' => array_sum($data['params']),
                            'id' => $data['id']);
                    } elseif ($data['method'] == 'subtract') {
                        $response[] = array(
                            'jsonrpc' => '2.0',
                            'result' => $data['params'][0] - $data['params'][1],
                            'id' => $data['id']);
                    } elseif ($data['method'] == 'notify_hello') {
                    } elseif ($data['method'] == 'foo.get') {
                        $response[] = array(
                            'jsonrpc' => '2.0',
                            'error' =>  array(
                                'code' => -32601,
                                'message' => 'Method not found'),
                            'id' => $data['id']);
                    } elseif ($data['method'] == 'get_data') {
                        $response[] = array(
                            'jsonrpc' => '2.0',
                            'result' => array("hello", 5),
                            'id' => $data['id']);
                    }
                }

                return json_encode($response);
            }));

        $client = new Client($transport);

        // --> [
        //         {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
        //         {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},
        //         {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},
        //         {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
        //         {"jsonrpc": "2.0", "method": "get_data", "id": "9"}
        //     ]
        // <-- [
        //         {"jsonrpc": "2.0", "result": 7, "id": "1"},
        //         {"jsonrpc": "2.0", "result": 19, "id": "2"},
        //         {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found."}, "id": "5"},
        //         {"jsonrpc": "2.0", "result": ["hello", 5], "id": "9"}
        //     ]

        $responses = $client->batch(array(
            0 => $client->prepare('sum', array(1, 2, 4)),
            'notif' => array('notify_hello', array(7), true),
            2 => $client->prepare('subtract', array(42, 23)),
            'err' => new Request('foo.get', array('name' => 'myself')),
            4 => 'get_data',
        ));

        $this->assertEquals(7, $responses[0]);
        $this->assertEquals(null, $responses['notif']);
        $this->assertEquals(19, $responses[2]);
        $this->assertInstanceOf('Mute\JsonRPC\Exceptions\RPCError', $responses['err']);
        $this->assertEquals(array('hello', 5), $responses[4]);
    }
}
