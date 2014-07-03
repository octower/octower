<?php

namespace Octower\Ssh\Exceptions;

use Exception;

class SshConnectionFailedException extends \RuntimeException
{
    public function __construct($message = "The SSH connection failed.", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}