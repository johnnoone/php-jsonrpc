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
use Mute\JsonRPC\Server\Parser;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseError()
    {
        $this->setExpectedException('Mute\JsonRPC\Exceptions\ParseError');
        $parser = new Parser;
        $data = $parser->parse('toto');
    }

    public function testInvalidRequest()
    {
        $this->setExpectedException('Mute\JsonRPC\Exceptions\InvalidRequest');
        $parser = new Parser;
        $data = $parser->parse('[]');
    }
}
