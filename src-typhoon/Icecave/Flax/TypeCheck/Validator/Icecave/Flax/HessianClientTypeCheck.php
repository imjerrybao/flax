<?php
namespace Icecave\Flax\TypeCheck\Validator\Icecave\Flax;

class HessianClientTypeCheck extends \Icecave\Flax\TypeCheck\AbstractValidator
{
    public function validateConstruct(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('httpClient', 0, 'Guzzle\\Http\\ClientInterface');
        } elseif ($argumentCount > 6) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(6, $arguments[6]);
        }
    }

    public function setLogger(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('logger', 0, 'Psr\\Log\\LoggerInterface');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function validateCall(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 2) {
            if ($argumentCount < 1) {
                throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('name', 0, 'string');
            }
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('arguments', 1, 'array');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
        }
        $value = $arguments[0];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'name',
                0,
                $arguments[0],
                'string'
            );
        }
    }

    public function invoke(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('name', 0, 'string');
        }
        $value = $arguments[0];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'name',
                0,
                $arguments[0],
                'string'
            );
        }
    }

    public function invokeArray(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('name', 0, 'string');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
        }
        $value = $arguments[0];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'name',
                0,
                $arguments[0],
                'string'
            );
        }
    }

    public function doRequest(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 2) {
            if ($argumentCount < 1) {
                throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('name', 0, 'string');
            }
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('arguments', 1, 'array');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
        }
        $value = $arguments[0];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'name',
                0,
                $arguments[0],
                'string'
            );
        }
    }

    public function createFaultException(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('properties', 0, 'Icecave\\Collections\\Map');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

}
