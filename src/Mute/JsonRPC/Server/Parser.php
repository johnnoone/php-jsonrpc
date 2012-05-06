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

namespace Mute\JsonRPC\Server;

use Mute\JsonRPC\Exceptions\ParseError;
use Mute\JsonRPC\Exceptions\InvalidRequest;
use Mute\JsonRPC\Util;

class Parser
{
    public function parse($data)
    {
        $parsed = Util::jsonDecode($data);
        if (false === is_array($parsed)) {
            throw new ParseError;
        } elseif (empty($parsed)) {
            throw new InvalidRequest;
        }
        $batch = Util::isList($parsed);
        $requests = $batch ? $parsed : array($parsed);

        return array($batch, $requests);
    }
}
