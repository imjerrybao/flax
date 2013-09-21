<?php
namespace Icecave\Flax\TypeCheck\Validator\Icecave\Flax\Wire;

class ValueDecoderTypeCheck extends \Icecave\Flax\TypeCheck\AbstractValidator
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

    public function emitCollectionType(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'string|integer');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!(\is_string($value) || \is_int($value))) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'value',
                0,
                $arguments[0],
                'string|integer'
            );
        }
    }

    public function emitVectorElement(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'mixed');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function emitFixedVectorSize(array $arguments)
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

    public function emitFixedVectorElement(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'mixed');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function emitMapKey(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'mixed');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function emitMapValue(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'mixed');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function emitClassDefinitionName(array $arguments)
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

    public function emitClassDefinitionSize(array $arguments)
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

    public function emitClassDefinitionField(array $arguments)
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

    public function emitObjectInstanceType(array $arguments)
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

    public function emitObjectInstanceField(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'mixed');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function emitReference(array $arguments)
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

    public function startCompactString(array $arguments)
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

    public function startString(array $arguments)
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

    public function startCompactBinary(array $arguments)
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

    public function startBinary(array $arguments)
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

    public function startInt32Compact2(array $arguments)
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

    public function startInt32Compact3(array $arguments)
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

    public function startInt64Compact2(array $arguments)
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

    public function startInt64Compact3(array $arguments)
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

    public function startFixedLengthVector(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function startTypedVector(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function startTypedFixedLengthVector(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function startCompactTypedFixedLengthVector(array $arguments)
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

    public function startCompactFixedLengthVector(array $arguments)
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

    public function startTypedMap(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function startClassDefinition(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function startObjectInstance(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('classDefIndex', 0, 'integer');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_int($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'classDefIndex',
                0,
                $arguments[0],
                'integer'
            );
        }
    }

    public function startCompactObjectInstance(array $arguments)
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

    public function handleBegin(array $arguments)
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

    public function handleBeginScalar(array $arguments)
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

    public function handleBeginString(array $arguments)
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

    public function handleBeginStringStrict(array $arguments)
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

    public function handleBeginBinary(array $arguments)
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

    public function handleBeginInt32(array $arguments)
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

    public function handleBeginInt32Strict(array $arguments)
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

    public function handleBeginInt64(array $arguments)
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

    public function handleBeginDouble(array $arguments)
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

    public function handleBeginTimestamp(array $arguments)
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

    public function handleBeginVector(array $arguments)
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

    public function handleBeginMap(array $arguments)
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

    public function handleBeginObject(array $arguments)
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

    public function handleStringSize(array $arguments)
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

    public function handleStringData(array $arguments)
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

    public function handleStringChunkSize(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 2) {
            if ($argumentCount < 1) {
                throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('byte', 0, 'integer');
            }
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('isFinal', 1, 'boolean');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
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
        $value = $arguments[1];
        if (!\is_bool($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'isFinal',
                1,
                $arguments[1],
                'boolean'
            );
        }
    }

    public function handleStringChunkData(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 2) {
            if ($argumentCount < 1) {
                throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('byte', 0, 'integer');
            }
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('isFinal', 1, 'boolean');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
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
        $value = $arguments[1];
        if (!\is_bool($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'isFinal',
                1,
                $arguments[1],
                'boolean'
            );
        }
    }

    public function handleStringChunkContinuation(array $arguments)
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

    public function handleBinarySize(array $arguments)
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

    public function handleBinaryData(array $arguments)
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

    public function handleBinaryChunkSize(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 2) {
            if ($argumentCount < 1) {
                throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('byte', 0, 'integer');
            }
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('isFinal', 1, 'boolean');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
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
        $value = $arguments[1];
        if (!\is_bool($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'isFinal',
                1,
                $arguments[1],
                'boolean'
            );
        }
    }

    public function handleBinaryChunkData(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 2) {
            if ($argumentCount < 1) {
                throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('byte', 0, 'integer');
            }
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('isFinal', 1, 'boolean');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
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
        $value = $arguments[1];
        if (!\is_bool($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'isFinal',
                1,
                $arguments[1],
                'boolean'
            );
        }
    }

    public function handleBinaryChunkContinuation(array $arguments)
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

    public function handleDouble1(array $arguments)
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

    public function handleDouble2(array $arguments)
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

    public function handleDouble4(array $arguments)
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

    public function handleDouble8(array $arguments)
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

    public function handleInt32(array $arguments)
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

    public function handleInt64(array $arguments)
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

    public function handleTimestampMilliseconds(array $arguments)
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

    public function handleTimestampMinutes(array $arguments)
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

    public function handleNextCollectionElement(array $arguments)
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

    public function handleCollectionType(array $arguments)
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

    public function state(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function pushState(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('state', 0, 'Icecave\\Flax\\Wire\\ValueDecoderState');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
        }
    }

    public function setState(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('state', 0, 'Icecave\\Flax\\Wire\\ValueDecoderState');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function popState(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function popStateAndEmitValue(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('value', 0, 'mixed');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
    }

    public function popStateAndEmitBuffer(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function popStateAndEmitResult(array $arguments)
    {
        if (\count($arguments) > 0) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(0, $arguments[0]);
        }
    }

    public function appendStringData(array $arguments)
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

    public function appendBinaryData(array $arguments)
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

    public function appendInt16Data(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('byte', 0, 'integer');
        } elseif ($argumentCount > 2) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(2, $arguments[2]);
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
        if ($argumentCount > 1) {
            $value = $arguments[1];
            if (!\is_bool($value)) {
                throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                    'signed',
                    1,
                    $arguments[1],
                    'boolean'
                );
            }
        }
    }

    public function appendInt32Data(array $arguments)
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

    public function appendInt64Data(array $arguments)
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

    public function trace(array $arguments)
    {
        $argumentCount = \count($arguments);
        if ($argumentCount < 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\MissingArgumentException('str', 0, 'string');
        } elseif ($argumentCount > 1) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentException(1, $arguments[1]);
        }
        $value = $arguments[0];
        if (!\is_string($value)) {
            throw new \Icecave\Flax\TypeCheck\Exception\UnexpectedArgumentValueException(
                'str',
                0,
                $arguments[0],
                'string'
            );
        }
    }

}
