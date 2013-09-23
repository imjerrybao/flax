<?php
namespace Icecave\Flax\Exception;

use Exception;
use Icecave\Flax\TypeCheck\TypeCheck;
use LogicException;

/**
 * Indicates an error while encoding a value.
 */
class EncodeException extends LogicException
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
