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

namespace Mute\JsonRPC\Client;

class Batch
{
    protected $requests;

    public function __construct(array $requests)
    {
        $this->requests = $requests;
    }

    public function jsonSerialize()
    {
        $data = array();
        foreach ($this->requests as $request) {
            $data[] = $request->jsonSerialize();
        }

        return $data;
    }
}
