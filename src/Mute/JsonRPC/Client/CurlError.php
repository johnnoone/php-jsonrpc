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

class CurlError extends \Exception
{
    public function __construct($ch)
    {
        $message = curl_error($ch);
        $code = curl_errno($ch);
        parent::__construct($message, $code);
    }
}
