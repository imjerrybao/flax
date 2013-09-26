<?php
namespace Icecave\Flax;

use Icecave\Flax\TypeCheck\TypeCheck;

/**
 * A wrapper around an object that allows you to overide the class name used during Hessian serialization.
 */
class Object
{
    /**
     * @param string $className The class name to use for serialization.
     * @param object $object    The actual object to serialize.
     */
    public function __construct($className, $object)
    {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        $this->className = $className;
        $this->object = $object;
    }

    /**
     * Get the class name to use for serialization.
     *
     * @return string The class name to use for serialization.
     */
    public function className()
    {
        $this->typeCheck->object(func_get_args());

        return $this->className;
    }

    /**
     * Get the internal obejct.
     *
     * @return string The internal object.
     */
    public function object()
    {
        $this->typeCheck->object(func_get_args());

        return $this->object;
    }

    private $typeCheck;
    private $className;
    private $object;
}
