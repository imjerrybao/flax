<?php
namespace Icecave\Flax\Exception;

use Exception;
use Icecave\Collections\Map;
use Icecave\Flax\TypeCheck\TypeCheck;
use RuntimeException;

/**
 * Indicates that a fault occurred while making a remote procedure call.
 */
class RPCFaultException extends RuntimeException
{
    /**
     * @param Map         $properties
     * @param Exception|null $previous The previous exception, if any.
     */
    public function __construct(Map $properties, Exception $previous = null)
    {
        // TypeCheck::get(__CLASS__, func_get_args());

        $this->properties = $properties;

        parent::__construct('Hessian RPC fault.', 0, $previous);
    }

    public function properties()
    {
        return $this->properties;
    }

    private $properties;
}
