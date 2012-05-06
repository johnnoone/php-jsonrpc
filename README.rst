
============
Mute\\JsonRPC
============

:Author: Xavier Barbosa <clint.northwood@gmail.com>

Overview
--------

Mute\\JsonRPC implements JsonRPC 2.0 protocol in php 5.3.

It can be used has client or server.
Currently, only HTTP transport is implemented.
See initial specs http://www.jsonrpc.org/specification


On the server side
------------------

Controllers can be any callable.

.. parsed-literal::

    <?php
    use Mute\\JsonRPC\\Server;

    $server = new Server;

    $controller = function ($minuend, $subtrahend) {
        return $minuend - $subtrahend;
    };
    $server->controllers->expose('subtract', $controller);

    function bogusController() {
        throw new \\Exception;
    }
    $server->controllers->expose('bogus', 'bogusController');

    class Foo {
        function bar() {
            return 'baz';
        }
    }
    $server->controllers->expose('foo.method', 'Foo::bar');

Once all controllers have been declared, handle client's request

.. parsed-literal::

    <?php
    $request = '[{"jsonrpc":"2.0", "method": "foo"}, {"jsonrpc":"2.0", "method": "foo", "id": "baz"}]';
    $response = $server->handle($request);

On the client side
------------------

Declare the endpoint

.. parsed-literal::

    <?php
    use Mute\\JsonRPC\\Client;
    use Mute\\JsonRPC\\Client\\Request;

    $uri = 'http://endpoint';
    $client = new \\Mute\\JsonRPC\\Client($uri);

Requests can be an array

    array($method, array $params=null, $notification=false);

or a Client\\Request instance

    new Client\\Request($method, array $params=null, $notification=false);

Each request should returns a response.
A response can be a response scalar, an array, or a Exceptions\\RPCError instance

.. parsed-literal::

    <?php
    $response = $client->request('substract', array(42, 23));
    assert($response == 19);

    $response = $client->request('substract', array(23, 42));
    assert($response == -19);

    $response = $client->request('substract', array('subtrahend' => 23, 'minuend' => 42));
    assert($response == 19);

    $response = $client->request('substract', array('minuend' => 42, 'subtrahend' => 23));
    assert($response == 19);

Batched requests preserves keys

.. parsed-literal::

    <?php
    $responses = $client->batch(array(
        array('sum', array(1, 2, 4)),
        array('notify_hello', array(7), true),
        array('subtract', array(42, 23)),
        array('foo.get', array('name' => 'myself')),
        array('get_data'),
    ));

More examples can be found into tests/ directory.
