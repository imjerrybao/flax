<?php
namespace Icecave\Flax\TypeCheck\Validator\Icecave\Flax\Message;

class DecoderTypeCheck extends \Icecave\Flax\TypeCheck\AbstractValidator
{
    public function validateConstruct(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function reset(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function feed(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('buffer', 0, 'string');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
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

    public function tryFinalize(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function finalize(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function feedByte(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('byte', 0, 'integer');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_int($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'byte',
                0,
                $arguments[0],
                'integer'
            );
        }
    }

    public function emitValue(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'mixed');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function handleVersion(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('byte', 0, 'integer');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_int($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'byte',
                0,
                $arguments[0],
                'integer'
            );
        }
    }

    public function handleMessageType(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('byte', 0, 'integer');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_int($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'byte',
                0,
                $arguments[0],
                'integer'
            );
        }
    }

    public function handleReply(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('byte', 0, 'integer');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_int($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'byte',
                0,
                $arguments[0],
                'integer'
            );
        }
    }

    public function handleFault(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('byte', 0, 'integer');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_int($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'byte',
                0,
                $arguments[0],
                'integer'
            );
        }
    }

}
