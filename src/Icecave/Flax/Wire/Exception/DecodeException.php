<?php
namespace Icecave\Flax\Wire\Exception;

use RuntimeException;
use Exception;

class DecodeException extends RuntimeException
{
    public function __construct($message, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
