<?php
namespace Icecave\Flax\Exception;

use Exception;
use Icecave\Collections\Map;
use Icecave\Flax\TypeCheck\TypeCheck;

abstract class AbstractHessianFaultException extends Exception
{
    /**
     * @param Map            $properties Properties of the fault.
     * @param Exception|null $previous   The previous exception, if any.
     */
    public function __construct(Map $properties, Exception $previous = null)
    {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        $this->properties = $properties;

        parent::__construct(
            $properties->getWithDefault('message', 'Hessian fault.'),
            0,
            $previous
        );
    }

    /**
     * @return Map Properties of the fault.
     */
    public function properties()
    {
        $this->typeCheck->properties(func_get_args());

        return $this->properties;
    }

    private $typeCheck;
    private $properties;
}
