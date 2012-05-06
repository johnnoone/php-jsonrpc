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

namespace Mute\JsonRPC\Exceptions;

use Exception;

class RPCError extends Exception
{
    protected $message = 'Server error';
    protected $code = -32000;
    protected $data;

    public function __construct($data=null, Exception $previous = null)
    {
        $this->data = $data;
        parent::__construct($this->message, $this->code, $previous);
    }

    public function getData()
    {
        return $this->data;
    }

    public function jsonSerialize()
    {
        $data = array(
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'data' => $this->getData(),
        );

        return array_filter($data);
    }
}
