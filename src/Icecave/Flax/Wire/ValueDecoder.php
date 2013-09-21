<?php
namespace Icecave\Flax\Wire;

use Icecave\Chrono\DateTime;
use Icecave\Collections\Map;
use Icecave\Collections\Stack;
use Icecave\Collections\Vector;
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

            case ValueDecoderState::VECTOR():
                return $this->handleNextCollectionElement($character, $signedByte, $unsignedByte);
            case ValueDecoderState::VECTOR_SIZE():
                return $this->handleBeginInt32($character, $signedByte, $unsignedByte);

            case ValueDecoderState::MAP_KEY():
                return $this->handleNextCollectionElement($character, $signedByte, $unsignedByte);
            // case ValueDecoderState::MAP_VALUE():
            //     return $this->handleMapValue($character, $signedByte, $unsignedByte);
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
            case HessianConstants::NULL_VALUE:
                return $this->emitValue(null);
            case HessianConstants::BOOLEAN_TRUE:
                return $this->emitValue(true);
            case HessianConstants::BOOLEAN_FALSE:
                return $this->emitValue(false);
            case HessianConstants::STRING_CHUNK:
                return $this->pushState(ValueDecoderState::STRING_CHUNK_SIZE());
            case HessianConstants::STRING_CHUNK_FINAL:
                return $this->pushState(ValueDecoderState::STRING_CHUNK_FINAL_SIZE());
            case HessianConstants::BINARY_CHUNK:
                return $this->pushState(ValueDecoderState::BINARY_CHUNK_SIZE());
            case HessianConstants::BINARY_CHUNK_FINAL:
                return $this->pushState(ValueDecoderState::BINARY_CHUNK_FINAL_SIZE());
            case HessianConstants::INT64_4:
                return $this->pushState(ValueDecoderState::INT32());
            case HessianConstants::INT64_8:
                return $this->pushState(ValueDecoderState::INT64());
            case HessianConstants::DOUBLE_ZERO:
                return $this->emitValue(0.0);
            case HessianConstants::DOUBLE_ONE:
                return $this->emitValue(1.0);
            case HessianConstants::DOUBLE_1:
                return $this->pushState(ValueDecoderState::DOUBLE_1());
            case HessianConstants::DOUBLE_2:
                return $this->pushState(ValueDecoderState::DOUBLE_2());
            case HessianConstants::DOUBLE_4:
                return $this->pushState(ValueDecoderState::DOUBLE_4());
            case HessianConstants::DOUBLE_8:
                return $this->pushState(ValueDecoderState::DOUBLE_8());
            case HessianConstants::TIMESTAMP_MILLISECONDS:
                return $this->pushState(ValueDecoderState::TIMESTAMP_MILLISECONDS());
            case HessianConstants::TIMESTAMP_MINUTES:
                return $this->pushState(ValueDecoderState::TIMESTAMP_MINUTES());
            // case HessianConstants::CLASS_DEFINITION:
            //     return $this->pushState(ValueDecoderState::CLASS_DEFINITION());
            // case HessianConstants::OBJECT_INSTANCE:
            //     return $this->pushState(ValueDecoderState::OBJECT_INSTANCE());
            // case HessianConstants::REFERENCE:
            //     return $this->pushState(ValueDecoderState::REFERENCE());
            // case HessianConstants::VECTOR_TYPED:
            //     return $this->pushState(ValueDecoderState::VECTOR_TYPED());
            // case HessianConstants::VECTOR_TYPED_FIXED:
            //     return $this->pushState(ValueDecoderState::VECTOR_TYPED_FIXED());
            case HessianConstants::VECTOR:
                return $this->pushState(ValueDecoderState::VECTOR(), new Vector);
            case HessianConstants::VECTOR_FIXED:
                return $this->pushState(ValueDecoderState::VECTOR_SIZE(), new Vector);
            // case HessianConstants::MAP_TYPED:
            //     return $this->pushState(ValueDecoderState::MAP_TYPED());
            case HessianConstants::MAP:
                return $this->pushState(ValueDecoderState::MAP_KEY(), new Map);
        }

        if ($this->handleBeginInt32($character, $signedByte, $unsignedByte)) {
            return;
        } elseif (HessianConstants::STRING_COMPACT_START <= $unsignedByte && $unsignedByte <= HessianConstants::STRING_COMPACT_END) {
            return $this->beginCompactString($character, $signedByte, $unsignedByte);
        } elseif (HessianConstants::STRING_START <= $unsignedByte && $unsignedByte <= HessianConstants::STRING_END) {
            return $this->beginString($character, $signedByte, $unsignedByte);
        } elseif (HessianConstants::BINARY_COMPACT_START <= $unsignedByte && $unsignedByte <= HessianConstants::BINARY_COMPACT_END) {
            return $this->beginCompactBinary($character, $signedByte, $unsignedByte);
        } elseif (HessianConstants::BINARY_START <= $unsignedByte && $unsignedByte <= HessianConstants::BINARY_END) {
            return $this->beginBinary($character, $signedByte, $unsignedByte);
        } elseif (HessianConstants::INT64_1_START <= $unsignedByte && $unsignedByte <= HessianConstants::INT64_1_END) {
            return $this->emitValue($unsignedByte - HessianConstants::INT64_1_OFFSET);
        } elseif (HessianConstants::INT64_2_START <= $unsignedByte && $unsignedByte <= HessianConstants::INT64_2_END) {
            return $this->beginInt64Compact2($character, $signedByte, $unsignedByte);
        } elseif (HessianConstants::INT64_3_START <= $unsignedByte && $unsignedByte <= HessianConstants::INT64_3_END) {
            return $this->beginInt64Compact3($character, $signedByte, $unsignedByte);
        // } elseif (HessianConstants::OBJECT_INSTANCE_COMPACT_START <= $unsignedByte && $unsignedByte <= HessianConstants::OBJECT_INSTANCE_COMPACT_END) {
        //     return $this->beginCompactObjectInstance($character, $signedByte, $unsignedByte);
        // } elseif (HessianConstants::VECTOR_TYPED_FIXED_COMPACT_START <= $unsignedByte && $unsignedByte <= HessianConstants::VECTOR_TYPED_FIXED_COMPACT_END) {
        //     return $this->beginCompactTypedFixedLengthVector($character, $signedByte, $unsignedByte);
        } elseif (HessianConstants::VECTOR_FIXED_COMPACT_START <= $unsignedByte && $unsignedByte <= HessianConstants::VECTOR_FIXED_COMPACT_END) {
            return $this->beginCompactFixedLengthVector($character, $signedByte, $unsignedByte);
        }

        throw new Exception\DecodeException('Invalid byte at start of value: 0x' . dechex($unsignedByte) . ' (state: ' . $this->state() . ').');
    }

    public function handleBeginInt32($character, $signedByte, $unsignedByte)
    {
        if (HessianConstants::INT32_4 === $unsignedByte) {
            $this->pushState(ValueDecoderState::INT32());
        } elseif (HessianConstants::INT32_1_START <= $unsignedByte && $unsignedByte <= HessianConstants::INT32_1_END) {
            $this->emitValue($unsignedByte - HessianConstants::INT32_1_OFFSET);
        } elseif (HessianConstants::INT32_2_START <= $unsignedByte && $unsignedByte <= HessianConstants::INT32_2_END) {
            $this->beginInt32Compact2($character, $signedByte, $unsignedByte);
        } elseif (HessianConstants::INT32_3_START <= $unsignedByte && $unsignedByte <= HessianConstants::INT32_3_END) {
            $this->beginInt32Compact3($character, $signedByte, $unsignedByte);
        } else {
            return false;
        }

        return true;
    }

    private function emitValue($value)
    {
        $this->trace('EMIT: ' . $value);

        switch ($this->state()) {
            case ValueDecoderState::VECTOR():
                return $this->emitValueVector($value);
            case ValueDecoderState::VECTOR_SIZE():
                return $this->emitValueVectorSize($value);
            case ValueDecoderState::VECTOR_FIXED():
                return $this->emitValueVectorFixed($value);

            case ValueDecoderState::MAP_KEY():
                return $this->emitValueMapKey($value);
            case ValueDecoderState::MAP_VALUE():
                return $this->emitValueMapValue($value);
        }

        $this->value = $value;
    }

    private function emitValueVector($value)
    {
        $vector = $this->currentContext->collection;

        $vector->pushBack($value);
    }

    private function emitValueVectorSize($value)
    {
        if (0 === $value) {
            $this->popStateAndEmitValue(new Vector);
        } else {
            $this->currentContext->expectedSize = $value;

            $this->setState(ValueDecoderState::VECTOR_FIXED());
        }
    }

    private function emitValueVectorFixed($value)
    {
        $vector = $this->currentContext->collection;

        $vector->pushBack($value);

        if ($vector->size() === $this->currentContext->expectedSize) {
            $this->popStateAndEmitValue($vector);
        }
    }

    private function emitValueMapKey($value)
    {
        $this->currentContext->nextKey = $value;

        $this->setState(ValueDecoderState::MAP_VALUE());
    }

    private function emitValueMapValue($value)
    {
        $this->currentContext->collection->set(
            $this->currentContext->nextKey,
            $value
        );

        $this->setState(ValueDecoderState::MAP_KEY());
    }

    private function beginCompactString($character, $signedByte, $unsignedByte)
    {
        if (HessianConstants::STRING_COMPACT_START === $unsignedByte) {
            $this->emitValue('');
        } else {
            $this->pushState(ValueDecoderState::STRING_DATA());
            $this->currentContext->expectedSize = $unsignedByte - HessianConstants::STRING_COMPACT_START;
        }
    }

    private function beginString($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::STRING_SIZE());
        $this->currentContext->buffer .= pack('c', $unsignedByte - HessianConstants::STRING_START);
    }

    private function beginCompactBinary($character, $signedByte, $unsignedByte)
    {
        if (HessianConstants::BINARY_COMPACT_START === $unsignedByte) {
            $this->emitValue('');
        } else {
            $this->pushState(ValueDecoderState::BINARY_DATA());
            $this->currentContext->expectedSize = $unsignedByte - HessianConstants::BINARY_COMPACT_START;
        }
    }

    private function beginBinary($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::BINARY_SIZE());
        $this->currentContext->buffer .= pack('c', $unsignedByte - HessianConstants::BINARY_START);
    }

    private function beginInt32Compact2($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $unsignedByte - HessianConstants::INT32_2_OFFSET;
        $this->currentContext->buffer = pack('ccc', $value >> 16, $value >> 8, $value);
    }

    private function beginInt32Compact3($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $unsignedByte - HessianConstants::INT32_3_OFFSET;
        $this->currentContext->buffer = pack('cc', $value >> 8, $value);
    }

    private function beginInt64Compact2($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $unsignedByte - HessianConstants::INT64_2_OFFSET;
        $this->currentContext->buffer = pack('ccc', $value >> 16, $value >> 8, $value);
    }

    private function beginInt64Compact3($character, $signedByte, $unsignedByte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $unsignedByte - HessianConstants::INT64_3_OFFSET;
        $this->currentContext->buffer = pack('cc', $value >> 8, $value);
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
    //     $this->pushState(ValueDecoderState::VECTOR());
    //     $this->currentContext->collection = new Vector;
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

    // private function beginCompactObjectInstance($character, $signedByte, $unsignedByte)
    // {

    // }

    // private function beginCompactTypedFixedLengthVector($character, $signedByte, $unsignedByte)
    // {

    // }

    private function beginCompactFixedLengthVector($character, $signedByte, $unsignedByte)
    {
        if (HessianConstants::VECTOR_FIXED_COMPACT_START === $unsignedByte) {
            $this->emitValue(new Vector);
        } else {
            $this->pushState(ValueDecoderState::VECTOR_FIXED(), new Vector);
            $this->currentContext->expectedSize = $unsignedByte - HessianConstants::VECTOR_FIXED_COMPACT_START;
        }
    }

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

        $this->currentContext->collection .= $this->currentContext->buffer;
        $this->currentContext->buffer = '';

        if ($isFinal) {
            $this->popStateAndEmitValue($this->currentContext->collection);
        } else {
            $this->setState(ValueDecoderState::STRING_CHUNK_CONTINUATION());
        }
    }

    public function handleStringChunkContinuation($character, $signedByte, $unsignedByte)
    {
        switch ($unsignedByte) {
            case HessianConstants::STRING_CHUNK:
                return $this->setState(ValueDecoderState::STRING_CHUNK_SIZE());
            case HessianConstants::STRING_CHUNK_FINAL:
                return $this->setState(ValueDecoderState::STRING_CHUNK_FINAL_SIZE());
        }

        throw new Exception\DecodeException('Invalid byte at start string chunk: 0x' . dechex($unsignedByte) . ' (state: ' . $this->state() . ').');
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

        $this->currentContext->collection .= $this->currentContext->buffer;
        $this->currentContext->buffer = '';

        if ($isFinal) {
            $this->popStateAndEmitValue($this->currentContext->collection);
        } else {
            $this->setState(ValueDecoderState::BINARY_CHUNK_CONTINUATION());
        }
    }

    public function handleBinaryChunkContinuation($character, $signedByte, $unsignedByte)
    {
        switch ($unsignedByte) {
            case HessianConstants::BINARY_CHUNK:
                return $this->setState(ValueDecoderState::BINARY_CHUNK_SIZE());
            case HessianConstants::BINARY_CHUNK_FINAL:
                return $this->setState(ValueDecoderState::BINARY_CHUNK_FINAL_SIZE());
        }

        throw new Exception\DecodeException('Invalid byte at start binary chunk: 0x' . dechex($unsignedByte) . ' (state: ' . $this->state() . ').');
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

    public function handleNextCollectionElement($character, $signedByte, $unsignedByte)
    {
        if (HessianConstants::COLLECTION_TERMINATOR === $unsignedByte) {
            $this->popStateAndEmitValue($this->currentContext->collection);
        } else {
            $this->handleBegin($character, $signedByte, $unsignedByte);
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
    private function pushState(ValueDecoderState $state, $collection = '')
    {
        $this->trace('PUSH: ' . $state);

        $context = new stdClass;
        $context->state = $state;
        $context->buffer = '';
        $context->collection = $collection;
        $context->nextKey = null;
        $context->expectedSize = 0;

        $this->stack->push($context);

        $this->currentContext = $context;
    }

    private function setState(ValueDecoderState $state)
    {
        $this->trace('SET: ' . $state);

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

        $this->trace('POP (SET: ' . $this->state() . ')');
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

    public function trace($str)
    {
        // echo $str . PHP_EOL;
    }

    private $typeCheck;
    private $stack;
    private $currentContext;
    private $value;
}
