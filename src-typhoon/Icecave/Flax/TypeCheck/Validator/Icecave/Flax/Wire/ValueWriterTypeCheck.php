<?php
namespace Icecave\Flax\TypeCheck\Validator\Icecave\Flax\Wire;

class ValueWriterTypeCheck extends \Icecave\Flax\TypeCheck\AbstractValidator
{
    public function write(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 2) {
            if ($argumentCount < 1) {
                throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('buffer', 0, 'string');
            }
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 1, 'mixed');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
        }
        $value = $arguments[0];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'buffer',
                0,
                $arguments[0],
                'string'
            );
        }
    }

    public function writeBinary(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 2) {
            if ($argumentCount < 1) {
                throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('buffer', 0, 'string');
            }
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 1, 'string');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
        }
        $value = $arguments[0];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'buffer',
                0,
                $arguments[0],
                'string'
            );
        }
        $value = $arguments[1];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'value',
                1,
                $arguments[1],
                'string'
            );
        }
    }

    public function encode(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'mixed');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function encodeBinary(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'string');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'value',
                0,
                $arguments[0],
                'string'
            );
        }
    }

    public function encodeInteger(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'integer');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_int($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'value',
                0,
                $arguments[0],
                'integer'
            );
        }
    }

    public function encodeBoolean(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'boolean');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_bool($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'value',
                0,
                $arguments[0],
                'boolean'
            );
        }
    }

    public function encodeString(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'string');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'value',
                0,
                $arguments[0],
                'string'
            );
        }
    }

    public function encodeDouble(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'float');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_float($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'value',
                0,
                $arguments[0],
                'float'
            );
        }
    }

    public function encodeArray(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'array');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function encodeObject(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'object');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_object($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'value',
                0,
                $arguments[0],
                'object'
            );
        }
    }

    public function encodeNull(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

}
