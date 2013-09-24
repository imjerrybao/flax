<?php
namespace Icecave\Flax\TypeCheck\Validator\Icecave\Flax\Exception;

class AbstractHessianFaultExceptionTypeCheck extends \Icecave\Flax\TypeCheck\AbstractValidator
{
    public function validateConstruct(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('properties', 0, 'Icecave\\Collections\\Map');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
        }
    }

    public function properties(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

}
