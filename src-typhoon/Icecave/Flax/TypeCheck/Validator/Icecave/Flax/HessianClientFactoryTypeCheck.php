<?php
namespace Icecave\Flax\TypeCheck\Validator\Icecave\Flax;

class HessianClientFactoryTypeCheck extends \Icecave\Flax\TypeCheck\AbstractValidator
{
    public function create(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('url', 0, 'string');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
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
    }

}
