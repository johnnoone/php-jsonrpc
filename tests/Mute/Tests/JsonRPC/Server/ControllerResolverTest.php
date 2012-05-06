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
use Mute\JsonRPC\Server\ControllerResolver;

class ControllerResolverTest extends PHPUnit_Framework_TestCase
{
    public function testMethodNotFound()
    {
        $this->setExpectedException('Mute\JsonRPC\Exceptions\MethodNotFound');
        $resolver = new ControllerResolver;
        $data = $resolver->resolve('toto');
    }

    public function testInvalidParams()
    {
        $this->setExpectedException('Mute\JsonRPC\Exceptions\InvalidParams');
        $resolver = new ControllerResolver;
        $resolver->expose('foo', function ($foo, $bar) {});
        $data = $resolver->resolve('foo');
    }

    public function testInvalidCallable()
    {
        $this->setExpectedException('Mute\JsonRPC\Exceptions\InternalError');
        $resolver = new ControllerResolver;
        $resolver->expose('foo', null);
        $data = $resolver->resolve('foo');
    }

    public function testInternalError()
    {
        $this->setExpectedException('Mute\JsonRPC\Exceptions\InternalError');
        $resolver = new ControllerResolver;
        $resolver->expose('foo', function () { throw new \Exception; });
        $data = $resolver->resolve('foo');
    }

    public function testDefaultParams()
    {
        $resolver = new ControllerResolver;
        $resolver->expose('foo', function ($foo, $bar) {
            return array($foo, $bar, 'baz'); }, array('foo', 'bar'));
        $data = $resolver->resolve('foo');
        $this->assertEquals(array('foo', 'bar', 'baz'), $data);

        $resolver->expose('foo', function ($foo='foo', $bar='bar') {
            return array($foo, $bar, 'baz'); });
        $data = $resolver->resolve('foo');
        $this->assertEquals(array('foo', 'bar', 'baz'), $data);
    }

    public function testResolveClosure()
    {
        $resolver = new ControllerResolver;
        $resolver->expose('foo', function ($foo, $bar) {
            return array($foo, $bar, 'baz'); }, array('foo'));
        $data = $resolver->resolve('foo', array('bar' => 'bar'));
        $this->assertEquals(array('foo', 'bar', 'baz'), $data);

        $resolver->expose('bar', function ($foo, $bar) {
            return array($foo, $bar, 'baz'); }, array('foo' => 'foo'));
        $data = $resolver->resolve('bar', array('foo1', 'bar'));
        $this->assertEquals(array('foo1', 'bar', 'baz'), $data);

        $resolver->expose('baz', function () {
            return func_get_args(); });
        $data = $resolver->resolve('baz', array('foo3', 'bar3', 'baz3'));
        $this->assertEquals(array('foo3', 'bar3', 'baz3'), $data);
    }

    public function testResolveClass()
    {
        $resolver = new ControllerResolver;
        $resolver->expose('foo', array(__NAMESPACE__ . '\MockController', 'action'), array('foo'));
        $data = $resolver->resolve('foo', array('bar' => 'bar'));
        $this->assertEquals(array('foo', 'bar', 'baz'), $data);

        $resolver->expose('bar', __NAMESPACE__ . '\MockController::action', array('foo'));
        $data = $resolver->resolve('bar', array('bar' => 'bar'));
        $this->assertEquals(array('foo', 'bar', 'baz'), $data);

        $resolver->expose('foo', array(__NAMESPACE__ . '\MockController', 'staticAction'), array('foo'));
        $data = $resolver->resolve('foo', array('bar' => 'bar'));
        $this->assertEquals(array('foo', 'bar', 'baz'), $data);

        $resolver->expose('bar', __NAMESPACE__ . '\MockController::staticAction', array('foo'));
        $data = $resolver->resolve('bar', array('bar' => 'bar'));
        $this->assertEquals(array('foo', 'bar', 'baz'), $data);
    }

    public function testResolveFunction()
    {
        $resolver = new ControllerResolver;
        $resolver->expose('foo', __NAMESPACE__ . '\mock_controller', array('foo'));
        $data = $resolver->resolve('foo', array('bar' => 'bar'));
        $this->assertEquals(array('foo', 'bar', 'baz'), $data);
    }
}

class MockController
{
    public function action($foo, $bar)
    {
        return array($foo, $bar, 'baz');
    }

    public static function staticAction($foo, $bar)
    {
        return array($foo, $bar, 'baz');
    }
}

function mock_controller($foo, $bar)
{
    return array($foo, $bar, 'baz');
}