<?php
namespace Icecave\Flax\Wire\Exception;

use RuntimeException;
use Exception;

class DecodeException extends RuntimeException
{
    /**
     * @param string         $message
     * @param Exception|null $previous
     */
    public function __construct($message, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
