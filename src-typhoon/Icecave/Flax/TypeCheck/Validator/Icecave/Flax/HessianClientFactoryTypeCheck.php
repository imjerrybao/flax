<?php
namespace Icecave\Flax\TypeCheck\Validator\Icecave\Flax;

class HessianClientFactoryTypeCheck extends \Icecave\Flax\TypeCheck\AbstractValidator
{
    public function connect(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('url', 0, 'string');
        } elseif ($argumentCount > 3) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(3, $arguments[3]);
        }
        $value = $arguments[0];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'url',
                0,
                $arguments[0],
                'string'
            );
        }
        if ($argumentCount > 1) {
            $value = $arguments[1];
            if (!(\is_string($value) || $value === null)) {
                throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                    'username',
                    1,
                    $arguments[1],
                    'string|null'
                );
            }
        }
        if ($argumentCount > 2) {
            $value = $arguments[2];
            if (!\is_string($value)) {
                throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                    'password',
                    2,
                    $arguments[2],
                    'string'
                );
            }
        }
    }

}
