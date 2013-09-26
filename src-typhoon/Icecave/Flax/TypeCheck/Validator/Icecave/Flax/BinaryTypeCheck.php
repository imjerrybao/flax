<?php
namespace Icecave\Flax\TypeCheck\Validator\Icecave\Flax;

class BinaryTypeCheck extends \Icecave\Flax\TypeCheck\AbstractValidator
{
    public function validateConstruct(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        if ($argumentCount > 0) {
            $value = $arguments[0];
            if (!\is_string($value)) {
                throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                    'data',
                    0,
                    $arguments[0],
                    'string'
                );
            }
        }
    }

    public function data(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

}
