<?php
namespace Icecave\Flax\Wire;

use Icecave\Collections\Map;
use Icecave\Collections\Stack;
use Icecave\Chrono\DateTime;
use Icecave\Flax\TypeCheck\TypeCheck;
use stdClass;

class ValueDecoder
{
    public function __construct()
    {
        // $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        $this->reset();
    }

    public function reset()
    {
        // $this->typeCheck->reset(func_get_args());

        $this->stack = new Stack;
    }

    public function feed($buffer)
    {
        // $this->typeCheck->feed(func_get_args());

        $length = strlen($buffer);

        for ($index = 0; $index < $length; ++$index) {
            $this->feedByte(ord($buffer[$index]));
        }
    }

    public function feedByte($byte)
    {
        if (!$this->stack->isEmpty()) {
            switch ($this->stack->next()->state) {
                case ValueDecoderState::STRING_COMPACT_DATA():
                    return $this->handleStringCompactData($byte);
                // case ValueDecoderState::STRING_CHUNK_SIZE():
                //     return $this->handleStringChunkSize($byte, false);
                // case ValueDecoderState::STRING_CHUNK_DATA():
                //     return $this->handleStringChunkData($byte, false);
                // case ValueDecoderState::STRING_CHUNK_FINAL_SIZE():
                //     return $this->handleStringChunkSize($byte, true);
                // case ValueDecoderState::STRING_CHUNK_FINAL_DATA():
                //     return $this->handleStringChunkData($byte, true);
                case ValueDecoderState::DOUBLE_1():
                    return $this->handleDouble1($byte);
                case ValueDecoderState::DOUBLE_2():
                    return $this->handleDouble2($byte);
                case ValueDecoderState::DOUBLE_4():
                    return $this->handleDouble4($byte);
                case ValueDecoderState::DOUBLE_8():
                    return $this->handleDouble8($byte);
                case ValueDecoderState::INT32():
                    return $this->handleInt32($byte);
                case ValueDecoderState::INT64():
                    return $this->handleInt64($byte);
                case ValueDecoderState::TIMESTAMP_MILLISECONDS():
                    return $this->handleTimestampMilliseconds($byte);
                case ValueDecoderState::TIMESTAMP_MINUTES():
                    return $this->handleTimestampMinutes($byte);
            }
        }

        return $this->handleBegin($byte);
    }

    public function finalize()
    {
        return $this->value;
    }

    public function values()
    {
        // $this->typeCheck->values(func_get_args());

    }

    private function handleBegin($byte)
    {
        switch ($byte) {
            case Constants::NULL_VALUE:
                return $this->emitValue(null);
            case Constants::BOOLEAN_TRUE:
                return $this->emitValue(true);
            case Constants::BOOLEAN_FALSE:
                return $this->emitValue(false);
            // case Constants::STRING_CHUNK:
            //     return $this->push(ValueDecoderState::STRING_CHUNK_SIZE());
            // case Constants::STRING_CHUNK_FINAL:
            //     return $this->push(ValueDecoderState::STRING_CHUNK_FINAL_SIZE());
            // case Constants::BINARY_CHUNK:
            //     return $this->push(ValueDecoderState::BINARY_CHUNK_SIZE());
            // case Constants::BINARY_CHUNK_FINAL:
            //     return $this->push(ValueDecoderState::BINARY_CHUNK_FINAL_SIZE());
            case Constants::INT32_4:
                return $this->push(ValueDecoderState::INT32());
            case Constants::INT64_4:
                return $this->push(ValueDecoderState::INT32());
            case Constants::INT64_8:
                return $this->push(ValueDecoderState::INT64());
            case Constants::DOUBLE_ZERO:
                return $this->emitValue(0.0);
            case Constants::DOUBLE_ONE:
                return $this->emitValue(1.0);
            case Constants::DOUBLE_1:
                return $this->push(ValueDecoderState::DOUBLE_1());
            case Constants::DOUBLE_2:
                return $this->push(ValueDecoderState::DOUBLE_2());
            case Constants::DOUBLE_4:
                return $this->push(ValueDecoderState::DOUBLE_4());
            case Constants::DOUBLE_8:
                return $this->push(ValueDecoderState::DOUBLE_8());
            case Constants::TIMESTAMP_MILLISECONDS:
                return $this->push(ValueDecoderState::TIMESTAMP_MILLISECONDS());
            case Constants::TIMESTAMP_MINUTES:
                return $this->push(ValueDecoderState::TIMESTAMP_MINUTES());
            // case Constants::CLASS_DEFINITION:
            //     return $this->push(ValueDecoderState::CLASS_DEFINITION());
            // case Constants::OBJECT_INSTANCE:
            //     return $this->push(ValueDecoderState::OBJECT_INSTANCE());
            // case Constants::REFERENCE:
            //     return $this->push(ValueDecoderState::REFERENCE());
            // case Constants::VECTOR_TYPED:
            //     return $this->push(ValueDecoderState::VECTOR_TYPED());
            // case Constants::VECTOR_TYPED_FIXED:
            //     return $this->push(ValueDecoderState::VECTOR_TYPED_FIXED());
            // case Constants::VECTOR:
            //     return $this->push(ValueDecoderState::VECTOR());
            // case Constants::VECTOR_FIXED:
            //     return $this->push(ValueDecoderState::VECTOR_FIXED());
            // case Constants::MAP_TYPED:
            //     return $this->push(ValueDecoderState::MAP_TYPED());
            // case Constants::MAP:
            //     return $this->push(ValueDecoderState::MAP());
        }

        if (Constants::STRING_COMPACT_START <= $byte && $byte <= Constants::STRING_COMPACT_END) {
            return $this->beginCompactString($byte);
        // } elseif (Constants::STRING_START <= $byte && $byte <= Constants::STRING_END) {
        //     return $this->beginString($byte);
        // } elseif (Constants::BINARY_COMPACT_START <= $byte && $byte <= Constants::BINARY_COMPACT_END) {
        //     return $this->beginCompactBinary($byte);
        // } elseif (Constants::BINARY_START <= $byte && $byte <= Constants::BINARY_END) {
        //     return $this->beginBinary($byte);
        } elseif (Constants::INT32_1_START <= $byte && $byte <= Constants::INT32_1_END) {
            return $this->emitValue($byte - Constants::INT32_1_OFFSET);
        } elseif (Constants::INT32_2_START <= $byte && $byte <= Constants::INT32_2_END) {
            return $this->beginInt32Compact2($byte);
        } elseif (Constants::INT32_3_START <= $byte && $byte <= Constants::INT32_3_END) {
            return $this->beginInt32Compact3($byte);
        } elseif (Constants::INT64_1_START <= $byte && $byte <= Constants::INT64_1_END) {
            return $this->emitValue($byte - Constants::INT64_1_OFFSET);
        } elseif (Constants::INT64_2_START <= $byte && $byte <= Constants::INT64_2_END) {
            return $this->beginInt64Compact2($byte);
        } elseif (Constants::INT64_3_START <= $byte && $byte <= Constants::INT64_3_END) {
            return $this->beginInt64Compact3($byte);
        // } elseif (Constants::OBJECT_INSTANCE_COMPACT_START <= $byte && $byte <= Constants::OBJECT_INSTANCE_COMPACT_END) {
        //     return $this->beginCompactObjectInstance($byte);
        // } elseif (Constants::VECTOR_TYPED_FIXED_COMPACT_START <= $byte && $byte <= Constants::VECTOR_TYPED_FIXED_COMPACT_END) {
        //     return $this->beginCompactTypedFixedLengthVector($byte);
        // } elseif (Constants::VECTOR_FIXED_COMPACT_START <= $byte && $byte <= Constants::VECTOR_FIXED_COMPACT_END) {
        //     return $this->beginCompactFixedLengthVector($byte);
        }

        throw new Exception\DecodeException('Invalid byte at start of value: 0x' . dechex($byte) . '.');
    }

    private function emitValue($value)
    {
        $this->value = $value;
    }

    // private function beginChunkedString($isFinal = false)
    // {

    // }

    // private function beginChunkedBinary($isFinal = false)
    // {

    // }

    // private function beginClassDefinition()
    // {

    // }

    // private function beginObjectInstance()
    // {

    // }

    // private function beginReference()
    // {

    // }

    // private function beginTypedVector()
    // {

    // }

    // private function beginTypedFixedLengthFector()
    // {

    // }

    // private function beginVector()
    // {

    // }

    // private function beginFixedLengthVector()
    // {

    // }

    // private function beginTypedMap()
    // {

    // }

    // private function beginMap()
    // {

    // }

    private function beginCompactString($byte)
    {
        if (0x00 === $byte) {
            $this->emitValue('');
        } else {
            $context = $this->push(ValueDecoderState::STRING_COMPACT_DATA());
            $context->expectedSize = $byte;
        }
    }

    // private function beginString($byte)
    // {

    // }

    // private function beginCompactBinary($byte)
    // {

    // }

    // private function beginBinary($byte)
    // {

    // }

    private function beginInt32Compact2($byte)
    {
        $context = $this->push(ValueDecoderState::INT32());
        $value = $byte - Constants::INT32_2_OFFSET;
        $context->buffer = pack('ccc', $value >> 16, $value >> 8, $value);
    }

    private function beginInt32Compact3($byte)
    {
        $context = $this->push(ValueDecoderState::INT32());
        $value = $byte - Constants::INT32_3_OFFSET;
        $context->buffer = pack('cc', $value >> 8, $value);
    }

    private function beginInt64Compact2($byte)
    {
        $context = $this->push(ValueDecoderState::INT32());
        $value = $byte - Constants::INT64_2_OFFSET;
        $context->buffer = pack('ccc', $value >> 16, $value >> 8, $value);
    }

    private function beginInt64Compact3($byte)
    {
        $context = $this->push(ValueDecoderState::INT32());
        $value = $byte - Constants::INT64_3_OFFSET;
        $context->buffer = pack('cc', $value >> 8, $value);
    }

    // private function beginCompactObjectInstance($byte)
    // {

    // }

    // private function beginCompactTypedFixedLengthVector($byte)
    // {

    // }

    // private function beginCompactFixedLengthVector($byte)
    // {

    // }

    private function handleStringCompactData($byte)
    {
        if ($this->appendStringData($byte)) {
            $context = $this->pop();
            $this->emitValue($context->buffer);
        }
    }

    private function handleDouble1($byte)
    {
        $this->emitValue(
            floatval(Utility::byteToSigned($byte))
        );
    }

    private function handleDouble2($byte)
    {
        $context = $this->context();
        $context->buffer .= pack('c', $byte);

        if (2 === strlen($context->buffer)) {
            list(, $value) = unpack(
                's',
                Utility::convertEndianness($context->buffer)
            );
            $this->emitValue(floatval($value));
            $this->pop();
        }
    }

    private function handleDouble4($byte)
    {
        $context = $this->context();
        $context->buffer .= pack('c', $byte);

        if (4 === strlen($context->buffer)) {
            list(, $value) = unpack(
                'f',
                Utility::convertEndianness($context->buffer)
            );
            $this->emitValue($value);
            $this->pop();
        }
    }

    private function handleDouble8($byte)
    {
        $context = $this->context();
        $context->buffer .= pack('c', $byte);

        if (8 === strlen($context->buffer)) {
            list(, $value) = unpack(
                'd',
                Utility::convertEndianness($context->buffer)
            );
            $this->emitValue($value);
        }
    }

    public function handleInt32($byte)
    {
        $value = $this->appendInt32Data($byte);

        if (null !== $value) {
            $this->emitValue($value);
            $this->pop();
        }
    }

    public function handleInt64($byte)
    {
        $value = $this->appendInt64Data($byte);

        if (null !== $value) {
            $this->emitValue($value);
            $this->pop();
        }
    }

    public function handleTimestampMilliseconds($byte)
    {
        $value = $this->appendInt64Data($byte);

        if (null !== $value) {
            $this->emitValue(DateTime::fromUnixTime($value / 1000));
            $this->pop();
        }
    }

    public function handleTimestampMinutes($byte)
    {
        $value = $this->appendInt32Data($byte);

        if (null !== $value) {
            $this->emitValue(DateTime::fromUnixTime($value * 60));
            $this->pop();
        }
    }

    /**
     * @param mixed       $value
     * @param ParserState $state
     */
    private function push(ValueDecoderState $state)
    {
        $context = new stdClass;
        $context->state = $state;
        $context->buffer = '';
        $context->expectedSize = 0;

        $this->stack->push($context);

        return $context;
    }

    private function pop()
    {
        return $this->stack->pop();
    }

    private function context()
    {
        return $this->stack->next();
    }

    private function appendStringData($byte)
    {
        $context = $this->context();
        $context->buffer .= pack('c', $byte);

        // Check if we've even read enough bytes to possibly be complete ...
        if (strlen($context->buffer) < $context->expectedSize) {
            return false;
        // Check if we have a valid utf8 string ...
        } elseif (!mb_check_encoding($context->buffer, 'utf8')) {
            return false;
        // Check if we've read the right number of multibyte characters ...
        } elseif (mb_strlen($context->buffer, 'utf8') !== $context->expectedSize) {
            return false;
        }

        return true;
    }

    private function appendInt32Data($byte)
    {
        $context = $this->context();
        $context->buffer .= pack('c', $byte);

        if (4 === strlen($context->buffer)) {
            list(, $value) = unpack(
                'l',
                Utility::convertEndianness($context->buffer)
            );

            return $value;
        }

        return null;
    }

    public function appendInt64Data($byte)
    {
        $context = $this->context();
        $context->buffer .= pack('c', $byte);

        if (8 === strlen($context->buffer)) {
            return Utility::unpackInt64($context->buffer);
        }

        return null;
    }

    private $typeCheck;
    private $stack;
    private $value;
}
