<?php
namespace Icecave\Flax;

interface HessianClientInterface
{
    /**
     * Invoke a Hessian operation.
     *
     * @param string       $name      The name of the operation to invoke.
     * @param array<mixed> $arguments Arguments to the operation.
     */
    public function __call($name, array $arguments);

    /**
     * Invoke a Hessian operation.
     *
     * @param string       $name      The name of the operation to invoke.
     * @param array<mixed> $arguments Arguments to the operation.
     */
    public function invoke($name, array $arguments = array());
}
