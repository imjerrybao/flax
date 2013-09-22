<?php
namespace Icecave\Flax\Serialization\Exception;

use Exception;
use Icecave\Flax\TypeCheck\TypeCheck;
use RuntimeException;

/**
 * Indicates an error while decoding a serialized Hessian value.
 */
class DecodeException extends RuntimeException
{
    /**
     * @param string         $message  The exception message.
     * @param Exception|null $previous The previous exception, if any.
     */
    public function __construct($message, Exception $previous = null)
    {
        TypeCheck::get(__CLASS__, func_get_args());

        parent::__construct($message, 0, $previous);
    }
}
