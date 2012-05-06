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

namespace Mute\Tests\JsonRPC\Client;

use PHPUnit_Framework_TestCase;
use Mute\JsonRPC\Client\HttpTransport;

class HttpTransportTest extends PHPUnit_Framework_TestCase
{
    public function testOk()
    {
        $transport = new HttpTransport('http://127.0.0.1:9000/');
        $data = $transport->call('foo');
    }

    public function testCurlError()
    {
        $this->setExpectedException('Mute\JsonRPC\Client\CallError');
        $transport = new HttpTransport('http://127.0.0.1:9002/');
        $data = $transport->call('foo');
    }

    public function testHttpError()
    {
        $this->setExpectedException('Mute\JsonRPC\Client\CallError');
        $transport = new HttpTransport('http://127.0.0.1:9000/');
        $data = $transport->call('return error');
    }

    protected static $server;

    public static function setUpBeforeClass()
    {
        $cmd = 'php -f ' . __DIR__ . '/../../../../socket_server.php 9000';
        self::$server = new Process($cmd);
    }
    public static function tearDownAfterClass()
    {
        self::$server->stop();
    }
}

# http://www.php.net/manual/en/function.exec.php#88704
class Process
{
    private $pid;
    private $command;

    public function __construct($cl=false)
    {
        if ($cl != false){
            $this->command = $cl;
            $this->runCom();
        }
    }
    private function runCom(){
        $command = 'nohup ' . $this->command . ' > /dev/null 2>&1 & echo $!';
        exec($command ,$op);
        $this->pid = (int) $op[0];
    }

    public function setPid($pid){
        $this->pid = $pid;
    }

    public function getPid(){
        return $this->pid;
    }

    public function status(){
        $command = 'ps -p ' . $this->pid;
        exec($command, $op);

        return isset($op[1]);
    }

    public function start(){
        if ($this->command != '')$this->runCom();
        else return true;
    }

    public function stop(){
        $command = 'kill '.$this->pid;
        exec($command);

        return ($this->status() == false);
    }
}
