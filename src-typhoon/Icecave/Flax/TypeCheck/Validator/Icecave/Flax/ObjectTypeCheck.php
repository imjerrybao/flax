<?php
namespace Icecave\Flax\TypeCheck\Validator\Icecave\Flax;

class ObjectTypeCheck extends \Icecave\Flax\TypeCheck\AbstractValidator
{
    public function validateConstruct(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 2) {
            if ($argumentCount < 1) {
                throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('className', 0, 'string');
            }
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('object', 1, 'object');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
        }
        $value = $arguments[0];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'className',
                0,
                $arguments[0],
                'string'
            );
        }
        $value = $arguments[1];
        if (!\is_object($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'object',
                1,
                $arguments[1],
                'object'
            );
        }
    }

    public function className(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function object(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

}
