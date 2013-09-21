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
            $this->feedCharacter($buffer[$index]);
        }
    }

    public function feedCharacter($character)
    {
        list(, $unsignedByte) = unpack('C', $character);
        list(, $signedByte)   = unpack('c', $character);

        switch ($this->state()) {
            case ValueDecoderState::STRING_SIZE():
                return $this->handleStringSize($character, $signedByte, $unsignedByte);
            case ValueDecoderState::STRING_DATA():
                return $this->handleStringData($character, $signedByte, $unsignedByte);
            case ValueDecoderState::STRING_CHUNK_SIZE():
                return $this->handleStringChunkSize($character, $signedByte, $unsignedByte, false);
            case ValueDecoderState::STRING_CHUNK_DATA():
                return $this->handleStringChunkData($character, $signedByte, $unsignedByte, false);
            case ValueDecoderState::STRING_CHUNK_FINAL_SIZE():
                return $this->handleStringChunkSize($character, $signedByte, $unsignedByte, true);
            case ValueDecoderState::STRING_CHUNK_FINAL_DATA():
                return $this->handleStringChunkData($character, $signedByte, $unsignedByte, true);
            case ValueDecoderState::STRING_CHUNK_CONTINUATION():
                return $this->handleStringChunkContinuation($character, $signedByte, $unsignedByte);

            case ValueDecoderState::BINARY_SIZE():
                return $this->handleBinarySize($character, $signedByte, $unsignedByte);
            case ValueDecoderState::BINARY_DATA():
                return $this->handleBinaryData($character, $signedByte, $unsignedByte);
            case ValueDecoderState::BINARY_CHUNK_SIZE():
                return $this->handleBinaryChunkSize($character, $signedByte, $unsignedByte, false);
            case ValueDecoderState::BINARY_CHUNK_DATA():
                return $this->handleBinaryChunkData($character, $signedByte, $unsignedByte, false);
            case ValueDecoderState::BINARY_CHUNK_FINAL_SIZE():
                return $this->handleBinaryChunkSize($character, $signedByte, $unsignedByte, true);
            case ValueDecoderState::BINARY_CHUNK_FINAL_DATA():
                return $this->handleBinaryChunkData($character, $signedByte, $unsignedByte, true);
            case ValueDecoderState::BINARY_CHUNK_CONTINUATION():
                return $this->handleBinaryChunkContinuation($character, $signedByte, $unsignedByte);

            case ValueDecoderState::INT32():
                return $this->handleInt32($character, $signedByte, $unsignedByte);
            case ValueDecoderState::INT64():
                return $this->handleInt64($character, $signedByte, $unsignedByte);

            case ValueDecoderState::DOUBLE_1():
                return $this->handleDouble1($character, $signedByte, $unsignedByte);
            case ValueDecoderState::DOUBLE_2():
                return $this->handleDouble2($character, $signedByte, $unsignedByte);
            case ValueDecoderState::DOUBLE_4():
                return $this->handleDouble4($character, $signedByte, $unsignedByte);
            case ValueDecoderState::DOUBLE_8():
                return $this->handleDouble8($character, $signedByte, $unsignedByte);

            case ValueDecoderState::TIMESTAMP_MILLISECONDS():
                return $this->handleTimestampMilliseconds($character, $signedByte, $unsignedByte);
            case ValueDecoderState::TIMESTAMP_MINUTES():
                return $this->handleTimestampMinutes($character, $signedByte, $unsignedByte);
        }

        return $this->handleBegin($character, $signedByte, $unsignedByte);
    }

    public function finalize()
    {
        return $this->value;
    }

    private function handleBegin($character, $signedByte, $unsignedByte)
    {
        switch ($unsignedByte) {
            case Constants::NULL_VALUE:
                return $this->emitValue(null);
            case Constants::BOOLEAN_TRUE:
                return $this->emitValue(true);
            case Constants::BOOLEAN_FALSE:
                return $this->emitValue(false);
            case Constants::STRING_CHUNK:
                return $this->pushState(ValueDecoderState::STRING_CHUNK_SIZE());
            case Constants::STRING_CHUNK_FINAL:
                return $this->pushState(ValueDecoderState::STRING_CHUNK_FINAL_SIZE());
            case Constants::BINARY_CHUNK:
                return $this->pushState(ValueDecoderState::BINARY_CHUNK_SIZE());
            case Constants::BINARY_CHUNK_FINAL:
                return $this->pushState(ValueDecoderState::BINARY_CHUNK_FINAL_SIZE());
            case Constants::INT32_4:
                return $this->pushState(ValueDecoderState::INT32());
            case Constants::INT64_4:
                return $this->pushState(ValueDecoderState::INT32());
            case Constants::INT64_8:
                return $this->pushState(ValueDecoderState::INT64());
            case Constants::DOUBLE_ZERO:
                return $this->emitValue(0.0);
            case Constants::DOUBLE_ONE:
                return $this->emitValue(1.0);
            case Constants::DOUBLE_1:
                return $this->pushState(ValueDecoderState::DOUBLE_1());
            case Constants::DOUBLE_2:
                return $this->pushState(ValueDecoderState::DOUBLE_2());
            case Constants::DOUBLE_4:
                return $this->pushState(ValueDecoderState::DOUBLE_4());
            case Constants::DOUBLE_8:
                return $this->pushState(ValueDecoderState::DOUBLE_8());
            case Constants::TIMESTAMP_MILLISECONDS:
                return $this->pushState(ValueDecoderState::TIMESTAMP_MILLISECONDS());
            case Constants::TIMESTAMP_MINUTES:
                return $this->pushState(ValueDecoderState::TIMESTAMP_MINUTES());
            // case Constants::CLASS_DEFINITION:
            //     return $this->pushState(ValueDecoderState::CLASS_DEFINITION());
            // case Constants::OBJECT_INSTANCE:
            //     return $this->pushState(ValueDecoderState::OBJECT_INSTANCE());
            // case Constants::REFERENCE:
            //     return $this->pushState(ValueDecoderState::REFERENCE());
            // case Constants::VECTOR_TYPED:
            //     return $this->pushState(ValueDecoderState::VECTOR_TYPED());
            // case Constants::VECTOR_TYPED_FIXED:
            //     return $this->pushState(ValueDecoderState::VECTOR_TYPED_FIXED());
            // case Constants::VECTOR:
            //     return $this->pushState(ValueDecoderState::VECTOR());
            // case Constants::VECTOR_FIXED:
            //     return $this->pushState(ValueDecoderState::VECTOR_FIXED());
            // case Constants::MAP_TYPED:
            //     return $this->pushState(ValueDecoderState::MAP_TYPED());
            // case Constants::MAP:
            //     return $this->pushState(ValueDecoderState::MAP());
        }

        if (Constants::STRING_COMPACT_START <= $unsignedByte && $unsignedByte <= Constants::STRING_COMPACT_END) {
            return $this->beginCompactString($character, $signedByte, $unsignedByte);
        } elseif (Constants::STRING_START <= $unsignedByte && $unsignedByte <= Constants::STRING_END) {
            return $this->beginString($character, $signedByte, $unsignedByte);
        } elseif (Constants::BINARY_COMPACT_START <= $unsignedByte && $unsignedByte <= Constants::BINARY_COMPACT_END) {
            return $this->beginCompactBinary($character, $signedByte, $unsignedByte);
        } elseif (Constants::BINARY_START <= $unsignedByte && $unsignedByte <= Constants::BINARY_END) {
            return $this->beginBinary($character, $signedByte, $unsignedByte);
        } elseif (Constants::INT32_1_START <= $unsignedByte && $unsignedByte <= Constants::INT32_1_END) {
            return $this->emitValue($unsignedByte - Constants::INT32_1_OFFSET);
        } elseif (Constants::INT32_2_START <= $unsignedByte && $unsignedByte <= Constants::INT32_2_END) {
            return $this->beginInt32Compact2($character, $signedByte, $unsignedByte);
        } elseif (Constants::INT32_3_START <= $unsignedByte && $unsignedByte <= Constants::INT32_3_END) {
            return $this->beginInt32Compact3($character, $signedByte, $unsignedByte);
        } elseif (Constants::INT64_1_START <= $unsignedByte && $unsignedByte <= Constants::INT64_1_END) {
            return $this->emitValue($unsignedByte - Constants::INT64_1_OFFSET);
        } elseif (Constants::INT64_2_START <= $unsignedByte && $unsignedByte <= Constants::INT64_2_END) {
            return $this->beginInt64Compact2($character, $signedByte, $unsignedByte);
        } elseif (Constants::INT64_3_START <= $unsignedByte && $unsignedByte <= Constants::INT64_3_END) {
            return $this->beginInt64Compact3($character, $signedByte, $unsignedByte);
        // } elseif (Constants::OBJECT_INSTANCE_COMPACT_START <= $unsignedByte && $unsignedByte <= Constants::OBJECT_INSTANCE_COMPACT_END) {
        //     return $this->beginCompactObjectInstance($character, $signedByte, $unsignedByte);
        // } elseif (Constants::VECTOR_TYPED_FIXED_COMPACT_START <= $unsignedByte && $unsignedByte <= Constants::VECTOR_TYPED_FIXED_COMPACT_END) {
        //     return $this->beginCompactTypedFixedLengthVector($character, $signedByte, $unsignedByte);
        // } elseif (Constants::VECTOR_FIXED_COMPACT_START <= $unsignedByte && $unsignedByte <= Constants::VECTOR_FIXED_COMPACT_END) {
        //     return $this->beginCompactFixedLengthVector($character, $signedByte, $unsignedByte);
        }

        throw new Exception\DecodeException('Invalid byte at start of value: 0x' . dechex($unsignedByte) . '.');
    }

    private function emitValue($value)
    {
        $this->value = $value;
    }

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

    private function beginCompactString($character, $signedByte, $unsignedByte)
    {
        if (Constants::STRING_COMPACT_START === $unsignedByte) {
            $this->emitValue('');
        } else {
            $this->pushState(ValueDecoderState::STRING_DATA());
            $this->currentContext->expectedSize = $unsignedByte - Constants::STRING_COMPACT_START;
        }
    }

    private function beginString($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::STRING_SIZE());
        $this->currentContext->buffer .= pack('c', $unsignedByte - Constants::STRING_START);
    }

    private function beginCompactBinary($character, $signedByte, $unsignedByte)
    {
        if (Constants::BINARY_COMPACT_START === $unsignedByte) {
            $this->emitValue('');
        } else {
            $this->pushState(ValueDecoderState::BINARY_DATA());
            $this->currentContext->expectedSize = $unsignedByte - Constants::BINARY_COMPACT_START;
        }
    }

    private function beginBinary($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::BINARY_SIZE());
        $this->currentContext->buffer .= pack('c', $unsignedByte - Constants::BINARY_START);
    }

    private function beginInt32Compact2($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $unsignedByte - Constants::INT32_2_OFFSET;
        $this->currentContext->buffer = pack('ccc', $value >> 16, $value >> 8, $value);
    }

    private function beginInt32Compact3($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $unsignedByte - Constants::INT32_3_OFFSET;
        $this->currentContext->buffer = pack('cc', $value >> 8, $value);
    }

    private function beginInt64Compact2($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $unsignedByte - Constants::INT64_2_OFFSET;
        $this->currentContext->buffer = pack('ccc', $value >> 16, $value >> 8, $value);
    }

    private function beginInt64Compact3($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $unsignedByte - Constants::INT64_3_OFFSET;
        $this->currentContext->buffer = pack('cc', $value >> 8, $value);
    }

    // private function beginCompactObjectInstance($character, $signedByte, $unsignedByte)
    // {

    // }

    // private function beginCompactTypedFixedLengthVector($character, $signedByte, $unsignedByte)
    // {

    // }

    // private function beginCompactFixedLengthVector($character, $signedByte, $unsignedByte)
    // {

    // }

    private function handleStringSize($character, $signedByte, $unsignedByte)
    {
        $this->currentContext->expectedSize = $this->appendInt16Data($character, false);

        if (0 === $this->currentContext->expectedSize) {
            $this->popStateAndEmitValue('');
        } else {
            $this->currentContext->buffer = '';
            $this->setState(ValueDecoderState::STRING_DATA());
        }
    }

    private function handleStringData($character, $signedByte, $unsignedByte)
    {
        if ($this->appendStringData($character)) {
            $this->popStateAndEmitBuffer();
        }
    }

    public function handleStringChunkSize($character, $signedByte, $unsignedByte, $isFinal)
    {
        $this->currentContext->expectedSize = $this->appendInt16Data($character, false);

        if (null === $this->currentContext->expectedSize) {
            return;
        }

        $this->currentContext->buffer = '';

        if ($isFinal) {
            $this->setState(ValueDecoderState::STRING_CHUNK_FINAL_DATA());
        } else {
            $this->setState(ValueDecoderState::STRING_CHUNK_DATA());
        }
    }

    public function handleStringChunkData($character, $signedByte, $unsignedByte, $isFinal)
    {
        if (!$this->appendStringData($character)) {
            return;
        }

        $this->currentContext->chunkBuffer .= $this->currentContext->buffer;
        $this->currentContext->buffer = '';

        if ($isFinal) {
            $this->popStateAndEmitValue($this->currentContext->chunkBuffer);
        } else {
            $this->setState(ValueDecoderState::STRING_CHUNK_CONTINUATION());
        }
    }

    public function handleStringChunkContinuation($character, $signedByte, $unsignedByte)
    {
        switch ($unsignedByte) {
            case Constants::STRING_CHUNK:
                return $this->setState(ValueDecoderState::STRING_CHUNK_SIZE());
            case Constants::STRING_CHUNK_FINAL:
                return $this->setState(ValueDecoderState::STRING_CHUNK_FINAL_SIZE());
        }

        throw new Exception\DecodeException('Invalid byte at start string chunk: 0x' . dechex($unsignedByte) . '.');
    }

    private function handleBinarySize($character, $signedByte, $unsignedByte)
    {
        $this->currentContext->expectedSize = $this->appendInt16Data($character, false);

        if (0 === $this->currentContext->expectedSize) {
            $this->popStateAndEmitValue('');
        } else {
            $this->currentContext->buffer = '';
            $this->setState(ValueDecoderState::BINARY_DATA());
        }
    }

    private function handleBinaryData($character, $signedByte, $unsignedByte)
    {
        if ($this->appendBinaryData($character)) {
            $this->popStateAndEmitBuffer();
        }
    }

    public function handleBinaryChunkSize($character, $signedByte, $unsignedByte, $isFinal)
    {
        $this->currentContext->expectedSize = $this->appendInt16Data($character, false);

        if (null === $this->currentContext->expectedSize) {
            return;
        }

        $this->currentContext->buffer = '';

        if ($isFinal) {
            $this->setState(ValueDecoderState::BINARY_CHUNK_FINAL_DATA());
        } else {
            $this->setState(ValueDecoderState::BINARY_CHUNK_DATA());
        }
    }

    public function handleBinaryChunkData($character, $signedByte, $unsignedByte, $isFinal)
    {
        if (!$this->appendBinaryData($character)) {
            return;
        }

        $this->currentContext->chunkBuffer .= $this->currentContext->buffer;
        $this->currentContext->buffer = '';

        if ($isFinal) {
            $this->popStateAndEmitValue($this->currentContext->chunkBuffer);
        } else {
            $this->setState(ValueDecoderState::BINARY_CHUNK_CONTINUATION());
        }
    }

    public function handleBinaryChunkContinuation($character, $signedByte, $unsignedByte)
    {
        switch ($unsignedByte) {
            case Constants::BINARY_CHUNK:
                return $this->setState(ValueDecoderState::BINARY_CHUNK_SIZE());
            case Constants::BINARY_CHUNK_FINAL:
                return $this->setState(ValueDecoderState::BINARY_CHUNK_FINAL_SIZE());
        }

        throw new Exception\DecodeException('Invalid byte at start binary chunk: 0x' . dechex($unsignedByte) . '.');
    }

    private function handleDouble1($character, $signedByte, $unsignedByte)
    {
        $this->emitValue(floatval($signedByte));
    }

    private function handleDouble2($character, $signedByte, $unsignedByte)
    {
        $value = $this->appendInt16Data($character);

        if (null !== $value) {
            $this->popStateAndEmitValue(floatval($value));
        }
    }

    private function handleDouble4($character, $signedByte, $unsignedByte)
    {
        $this->currentContext->buffer .= $character;

        if (4 === strlen($this->currentContext->buffer)) {
            list(, $value) = unpack(
                'f',
                Utility::convertEndianness($this->currentContext->buffer)
            );
            $this->popStateAndEmitValue($value);
        }
    }

    private function handleDouble8($character, $signedByte, $unsignedByte)
    {
        $this->currentContext->buffer .= $character;

        if (8 === strlen($this->currentContext->buffer)) {
            list(, $value) = unpack(
                'd',
                Utility::convertEndianness($this->currentContext->buffer)
            );
            $this->popStateAndEmitValue($value);
        }
    }

    public function handleInt32($character, $signedByte, $unsignedByte)
    {
        $value = $this->appendInt32Data($character);

        if (null !== $value) {
            $this->popStateAndEmitValue($value);
        }
    }

    public function handleInt64($character, $signedByte, $unsignedByte)
    {
        $value = $this->appendInt64Data($character);

        if (null !== $value) {
            $this->popStateAndEmitValue($value);
        }
    }

    public function handleTimestampMilliseconds($character, $signedByte, $unsignedByte)
    {
        $value = $this->appendInt64Data($character);

        if (null !== $value) {
            $this->popStateAndEmitValue(DateTime::fromUnixTime($value / 1000));
        }
    }

    public function handleTimestampMinutes($character, $signedByte, $unsignedByte)
    {
        $value = $this->appendInt32Data($character);

        if (null !== $value) {
            $this->popStateAndEmitValue(DateTime::fromUnixTime($value * 60));
        }
    }

    private function state()
    {
        if ($this->stack->isEmpty()) {
            return ValueDecoderState::BEGIN();
        }

        return $this->currentContext->state;
    }

    /**
     * @param mixed       $value
     * @param ParserState $state
     */
    private function pushState(ValueDecoderState $state)
    {
        $context = new stdClass;
        $context->state = $state;
        $context->buffer = '';
        $context->chunkBuffer = '';
        $context->expectedSize = 0;

        $this->stack->push($context);

        $this->currentContext = $context;
    }

    private function setState(ValueDecoderState $state)
    {
        $this->currentContext->state = $state;
    }

    private function popState()
    {
        $this->stack->pop();

        if ($this->stack->isEmpty()) {
            $this->currentContext = null;
        } else {
            $this->currentContext = $this->stack->next();
        }
    }

    private function popStateAndEmitValue($value)
    {
        $this->popState();
        $this->emitValue($value);
    }

    private function popStateAndEmitBuffer()
    {
        $this->popStateAndEmitValue($this->currentContext->buffer);
    }

    private function appendStringData($character)
    {
        $this->currentContext->buffer .= $character;

        // Check if we've even read enough bytes to possibly be complete ...
        if (strlen($this->currentContext->buffer) < $this->currentContext->expectedSize) {
            return false;
        // Check if we have a valid utf8 string ...
        } elseif (!mb_check_encoding($this->currentContext->buffer, 'utf8')) {
            return false;
        // Check if we've read the right number of multibyte characters ...
        } elseif (mb_strlen($this->currentContext->buffer, 'utf8') < $this->currentContext->expectedSize) {
            return false;
        }

        return true;
    }

    private function appendBinaryData($character)
    {
        $this->currentContext->buffer .= $character;

        if (strlen($this->currentContext->buffer) < $this->currentContext->expectedSize) {
            return false;
        }

        return true;
    }

    private function appendInt16Data($character, $signed = true)
    {
        $this->currentContext->buffer .= $character;

        if (2 === strlen($this->currentContext->buffer)) {
            list(, $value) = unpack(
                $signed ? 's' : 'S',
                Utility::convertEndianness($this->currentContext->buffer)
            );

            return $value;
        }

        return null;
    }

    private function appendInt32Data($character)
    {
        $this->currentContext->buffer .= $character;

        if (4 === strlen($this->currentContext->buffer)) {
            list(, $value) = unpack(
                'l',
                Utility::convertEndianness($this->currentContext->buffer)
            );

            return $value;
        }

        return null;
    }

    public function appendInt64Data($character)
    {
        $this->currentContext->buffer .= $character;

        if (8 === strlen($this->currentContext->buffer)) {
            return Utility::unpackInt64($this->currentContext->buffer);
        }

        return null;
    }

    private $typeCheck;
    private $stack;
    private $currentContext;
    private $value;
}
