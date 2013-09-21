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
        $this->classDefinitions = new Vector;
        $this->objects = new Vector;
        $this->currentContext = null;
        $this->value = null;
    }

    public function feed($buffer)
    {
        // $this->typeCheck->feed(func_get_args());

        $length = strlen($buffer);

        for ($index = 0; $index < $length; ++$index) {
            list(, $byte) = unpack('C', $buffer[$index]);
            $this->feedByte($byte);
        }
    }

    public function feedByte($byte)
    {
        switch ($this->state()) {
            case ValueDecoderState::STRING_SIZE():
                return $this->handleStringSize($byte);
            case ValueDecoderState::STRING_DATA():
                return $this->handleStringData($byte);
            case ValueDecoderState::STRING_CHUNK_SIZE():
                return $this->handleStringChunkSize($byte, false);
            case ValueDecoderState::STRING_CHUNK_DATA():
                return $this->handleStringChunkData($byte, false);
            case ValueDecoderState::STRING_CHUNK_FINAL_SIZE():
                return $this->handleStringChunkSize($byte, true);
            case ValueDecoderState::STRING_CHUNK_FINAL_DATA():
                return $this->handleStringChunkData($byte, true);
            case ValueDecoderState::STRING_CHUNK_CONTINUATION():
                return $this->handleStringChunkContinuation($byte);
            case ValueDecoderState::BINARY_SIZE():
                return $this->handleBinarySize($byte);
            case ValueDecoderState::BINARY_DATA():
                return $this->handleBinaryData($byte);
            case ValueDecoderState::BINARY_CHUNK_SIZE():
                return $this->handleBinaryChunkSize($byte, false);
            case ValueDecoderState::BINARY_CHUNK_DATA():
                return $this->handleBinaryChunkData($byte, false);
            case ValueDecoderState::BINARY_CHUNK_FINAL_SIZE():
                return $this->handleBinaryChunkSize($byte, true);
            case ValueDecoderState::BINARY_CHUNK_FINAL_DATA():
                return $this->handleBinaryChunkData($byte, true);
            case ValueDecoderState::BINARY_CHUNK_CONTINUATION():
                return $this->handleBinaryChunkContinuation($byte);
            case ValueDecoderState::INT32():
                return $this->handleInt32($byte);
            case ValueDecoderState::INT64():
                return $this->handleInt64($byte);
            case ValueDecoderState::DOUBLE_1():
                return $this->handleDouble1($byte);
            case ValueDecoderState::DOUBLE_2():
                return $this->handleDouble2($byte);
            case ValueDecoderState::DOUBLE_4():
                return $this->handleDouble4($byte);
            case ValueDecoderState::DOUBLE_8():
                return $this->handleDouble8($byte);
            case ValueDecoderState::TIMESTAMP_MILLISECONDS():
                return $this->handleTimestampMilliseconds($byte);
            case ValueDecoderState::TIMESTAMP_MINUTES():
                return $this->handleTimestampMinutes($byte);
            case ValueDecoderState::COLLECTION_TYPE():
                return $this->handleCollectionType($byte);
            case ValueDecoderState::VECTOR():
            case ValueDecoderState::MAP_KEY():
                return $this->handleNextCollectionElement($byte);
            case ValueDecoderState::VECTOR_SIZE():
            case ValueDecoderState::REFERENCE():
            case ValueDecoderState::CLASS_DEFINITION_SIZE():
            case ValueDecoderState::OBJECT_INSTANCE_TYPE():
                return $this->handleBeginInt32($byte);
            case ValueDecoderState::CLASS_DEFINITION_NAME():
            case ValueDecoderState::CLASS_DEFINITION_FIELD():
                return $this->handleBeginString($byte);
        }

        return $this->handleBegin($byte);
    }

    public function finalize()
    {
        if (!$this->stack->isEmpty()) {
            throw new Exception\DecodeException('Unexpected end of stream (state: ' . $this->state() . ').');
        }

        $value = $this->value;
        $this->reset();

        return $value;
    }

    private function handleBegin($byte)
    {
        switch ($byte) {
            case HessianConstants::NULL_VALUE:
                return $this->emitValue(null);
            case HessianConstants::BOOLEAN_TRUE:
                return $this->emitValue(true);
            case HessianConstants::BOOLEAN_FALSE:
                return $this->emitValue(false);
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
            case HessianConstants::CLASS_DEFINITION:
                return $this->beginClassDefinition();
            case HessianConstants::OBJECT_INSTANCE:
                return $this->pushState(ValueDecoderState::OBJECT_INSTANCE_TYPE());
            case HessianConstants::REFERENCE:
                return $this->pushState(ValueDecoderState::REFERENCE());
            case HessianConstants::VECTOR_TYPED:
                return $this->beginTypedVector();
            case HessianConstants::VECTOR_TYPED_FIXED:
                return $this->beginTypedFixedLengthVector();
            case HessianConstants::VECTOR:
                return $this->pushState(ValueDecoderState::VECTOR(), new Vector);
            case HessianConstants::VECTOR_FIXED:
                return $this->beginFixedLengthVector();
            case HessianConstants::MAP_TYPED:
                return $this->beginTypedMap();
            case HessianConstants::MAP:
                return $this->pushState(ValueDecoderState::MAP_KEY(), new Map);
        }

        if ($this->handleBeginInt32($byte, false)) {
            return;
        } elseif ($this->handleBeginString($byte, false)) {
            return;
        } elseif (HessianConstants::BINARY_COMPACT_START <= $byte && $byte <= HessianConstants::BINARY_COMPACT_END) {
            return $this->beginCompactBinary($byte);
        } elseif (HessianConstants::BINARY_START <= $byte && $byte <= HessianConstants::BINARY_END) {
            return $this->beginBinary($byte);
        } elseif (HessianConstants::INT64_1_START <= $byte && $byte <= HessianConstants::INT64_1_END) {
            return $this->emitValue($byte - HessianConstants::INT64_1_OFFSET);
        } elseif (HessianConstants::INT64_2_START <= $byte && $byte <= HessianConstants::INT64_2_END) {
            return $this->beginInt64Compact2($byte);
        } elseif (HessianConstants::INT64_3_START <= $byte && $byte <= HessianConstants::INT64_3_END) {
            return $this->beginInt64Compact3($byte);
        } elseif (HessianConstants::OBJECT_INSTANCE_COMPACT_START <= $byte && $byte <= HessianConstants::OBJECT_INSTANCE_COMPACT_END) {
            return $this->beginCompactObjectInstance($byte);
        } elseif (HessianConstants::VECTOR_TYPED_FIXED_COMPACT_START <= $byte && $byte <= HessianConstants::VECTOR_TYPED_FIXED_COMPACT_END) {
            return $this->beginCompactTypedFixedLengthVector($byte);
        } elseif (HessianConstants::VECTOR_FIXED_COMPACT_START <= $byte && $byte <= HessianConstants::VECTOR_FIXED_COMPACT_END) {
            return $this->beginCompactFixedLengthVector($byte);
        }

        throw new Exception\DecodeException('Invalid byte at start of value: 0x' . dechex($byte) . ' (state: ' . $this->state() . ').');
    }

    public function handleBeginString($byte, $matchFailureIsError = true)
    {
        if (HessianConstants::STRING_CHUNK === $byte) {
            $this->pushState(ValueDecoderState::STRING_CHUNK_SIZE());
        } elseif (HessianConstants::STRING_CHUNK_FINAL === $byte) {
            $this->pushState(ValueDecoderState::STRING_CHUNK_FINAL_SIZE());
        } elseif (HessianConstants::STRING_COMPACT_START <= $byte && $byte <= HessianConstants::STRING_COMPACT_END) {
            $this->beginCompactString($byte);
        } elseif (HessianConstants::STRING_START <= $byte && $byte <= HessianConstants::STRING_END) {
            $this->beginString($byte);
        } elseif ($matchFailureIsError) {
            throw new Exception\DecodeException('Invalid byte at start of string: 0x' . dechex($byte) . ' (state: ' . $this->state() . ').');
        } else {
            return false;
        }

        return true;
    }

    public function handleBeginInt32($byte, $matchFailureIsError = true)
    {
        if (HessianConstants::INT32_4 === $byte) {
            $this->pushState(ValueDecoderState::INT32());
        } elseif (HessianConstants::INT32_1_START <= $byte && $byte <= HessianConstants::INT32_1_END) {
            $this->emitValue($byte - HessianConstants::INT32_1_OFFSET);
        } elseif (HessianConstants::INT32_2_START <= $byte && $byte <= HessianConstants::INT32_2_END) {
            $this->beginInt32Compact2($byte);
        } elseif (HessianConstants::INT32_3_START <= $byte && $byte <= HessianConstants::INT32_3_END) {
            $this->beginInt32Compact3($byte);
        } elseif ($matchFailureIsError) {
            throw new Exception\DecodeException('Invalid byte at start of int: 0x' . dechex($byte) . ' (state: ' . $this->state() . ').');
        } else {
            return false;
        }

        return true;
    }

    private function emitValue($value)
    {
        $this->trace('EMIT: ' . ($value instanceof stdClass ? 'stdClass' : $value));

        switch ($this->state()) {
            case ValueDecoderState::COLLECTION_TYPE():
                return $this->emitValueCollectionType($value);
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
            case ValueDecoderState::CLASS_DEFINITION_NAME():
                return $this->emitValueClassDefinitionName($value);
            case ValueDecoderState::CLASS_DEFINITION_SIZE():
                return $this->emitValueClassDefinitionSize($value);
            case ValueDecoderState::CLASS_DEFINITION_FIELD():
                return $this->emitValueClassDefinitionField($value);
            case ValueDecoderState::OBJECT_INSTANCE_TYPE():
                return $this->emitValueObjectInstanceType($value);
            case ValueDecoderState::OBJECT_INSTANCE_FIELD():
                return $this->emitValueObjectInstanceField($value);
            case ValueDecoderState::REFERENCE():
                return $this->emitValueReference($value);
        }

        $this->value = $value;
    }

    private function emitValueCollectionType($value)
    {
        $this->popState(); // discard the type

        if (0 === $this->currentContext->expectedSize) {
            $this->popStateAndEmitResult();
        }
    }

    private function emitValueVector($value)
    {
        $vector = $this->currentContext->result;

        $vector->pushBack($value);
    }

    private function emitValueVectorSize($value)
    {
        $this->popState();

        if (0 === $value) {
            $this->popStateAndEmitValue(new Vector);
        } else {
            $this->currentContext->expectedSize = $value;
        }
    }

    private function emitValueVectorFixed($value)
    {
        $vector = $this->currentContext->result;

        $vector->pushBack($value);

        if ($vector->size() === $this->currentContext->expectedSize) {
            $this->popStateAndEmitResult();
        }
    }

    private function emitValueMapKey($value)
    {
        $this->currentContext->nextKey = $value;

        $this->setState(ValueDecoderState::MAP_VALUE());
    }

    private function emitValueMapValue($value)
    {
        $this->currentContext->result->set(
            $this->currentContext->nextKey,
            $value
        );

        $this->setState(ValueDecoderState::MAP_KEY());
    }

    private function emitValueClassDefinitionName($value)
    {
        $this->currentContext->result->name = $value;

        $this->setState(ValueDecoderState::CLASS_DEFINITION_SIZE());
    }

    private function emitValueClassDefinitionSize($value)
    {
        $this->currentContext->expectedSize = $value;

        if (0 === $value) {
            $this->popState();
        } else {
            $this->setState(ValueDecoderState::CLASS_DEFINITION_FIELD());
        }
    }

    private function emitValueClassDefinitionField($value)
    {
        $classDef = $this->currentContext->result;

        $classDef->fields->pushBack($value);

        if ($classDef->fields->size() === $this->currentContext->expectedSize) {
            $this->popState();
        }
    }

    private function emitValueObjectInstanceType($value)
    {
        $this->popState();
        $this->beginObjectInstance($value);
    }

    private function emitValueObjectInstanceField($value)
    {
        $fields = $this->currentContext->definition->fields;
        $fieldName = $fields[$this->currentContext->nextKey++];
        $this->currentContext->result->{$fieldName} = $value;

        if ($fields->size() === $this->currentContext->nextKey) {
            $this->popStateAndEmitResult();
        }
    }

    private function emitValueReference($value)
    {
        $this->popStateAndEmitValue(
            $this->objects[$value]
        );
    }

    private function beginCompactString($byte)
    {
        if (HessianConstants::STRING_COMPACT_START === $byte) {
            $this->emitValue('');
        } else {
            $this->pushState(ValueDecoderState::STRING_DATA());
            $this->currentContext->expectedSize = $byte - HessianConstants::STRING_COMPACT_START;
        }
    }

    private function beginString($byte)
    {
        $this->pushState(ValueDecoderState::STRING_SIZE());
        $this->currentContext->buffer .= pack('c', $byte - HessianConstants::STRING_START);
    }

    private function beginCompactBinary($byte)
    {
        if (HessianConstants::BINARY_COMPACT_START === $byte) {
            $this->emitValue('');
        } else {
            $this->pushState(ValueDecoderState::BINARY_DATA());
            $this->currentContext->expectedSize = $byte - HessianConstants::BINARY_COMPACT_START;
        }
    }

    private function beginBinary($byte)
    {
        $this->pushState(ValueDecoderState::BINARY_SIZE());
        $this->currentContext->buffer .= pack('c', $byte - HessianConstants::BINARY_START);
    }

    private function beginInt32Compact2($byte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $byte - HessianConstants::INT32_2_OFFSET;
        $this->currentContext->buffer = pack('ccc', $value >> 16, $value >> 8, $value);
    }

    private function beginInt32Compact3($byte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $byte - HessianConstants::INT32_3_OFFSET;
        $this->currentContext->buffer = pack('cc', $value >> 8, $value);
    }

    private function beginInt64Compact2($byte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $byte - HessianConstants::INT64_2_OFFSET;
        $this->currentContext->buffer = pack('ccc', $value >> 16, $value >> 8, $value);
    }

    private function beginInt64Compact3($byte)
    {
        $this->pushState(ValueDecoderState::INT32());
        $value = $byte - HessianConstants::INT64_3_OFFSET;
        $this->currentContext->buffer = pack('cc', $value >> 8, $value);
    }

    private function beginFixedLengthVector()
    {
        $this->pushState(ValueDecoderState::VECTOR_FIXED(), new Vector);
        $this->pushState(ValueDecoderState::VECTOR_SIZE());
    }

    private function beginTypedVector()
    {
        $this->pushState(ValueDecoderState::VECTOR(), new Vector);
        $this->pushState(ValueDecoderState::COLLECTION_TYPE());
    }

    private function beginTypedFixedLengthVector()
    {
        $this->pushState(ValueDecoderState::VECTOR_FIXED(), new Vector);
        $this->pushState(ValueDecoderState::VECTOR_SIZE());
        $this->pushState(ValueDecoderState::COLLECTION_TYPE());
    }

    private function beginCompactTypedFixedLengthVector($byte)
    {
        $this->pushState(ValueDecoderState::VECTOR_FIXED(), new Vector);
        $this->currentContext->expectedSize = $byte - HessianConstants::VECTOR_TYPED_FIXED_COMPACT_START;

        $this->pushState(ValueDecoderState::COLLECTION_TYPE());
    }

    private function beginCompactFixedLengthVector($byte)
    {
        if (HessianConstants::VECTOR_FIXED_COMPACT_START === $byte) {
            $this->emitValue(new Vector);
        } else {
            $this->pushState(ValueDecoderState::VECTOR_FIXED(), new Vector);
            $this->currentContext->expectedSize = $byte - HessianConstants::VECTOR_FIXED_COMPACT_START;
        }
    }

    private function beginTypedMap()
    {
        $this->pushState(ValueDecoderState::MAP_KEY(), new Map);
        $this->pushState(ValueDecoderState::COLLECTION_TYPE());
    }

    private function beginClassDefinition()
    {
        $this->pushState(ValueDecoderState::CLASS_DEFINITION_NAME());

        $def = new stdClass;
        $def->name = null;
        $def->fields = new Vector;

        $this->currentContext->result = $def;

        $this->classDefinitions->pushBack($def);
    }

    private function beginObjectInstance($classDefIndex)
    {
        $classDef = $this->classDefinitions[$classDefIndex];

        if ($classDef->fields->isEmpty()) {
            $this->emitValue(new stdClass);
        } else {
            $this->pushState(ValueDecoderState::OBJECT_INSTANCE_FIELD(), new stdClass);
            $this->currentContext->definition = $classDef;
            $this->currentContext->nextKey = 0;
        }
    }

    private function beginCompactObjectInstance($byte)
    {
        $this->beginObjectInstance($byte - HessianConstants::OBJECT_INSTANCE_COMPACT_START);
    }

    private function handleStringSize($byte)
    {
        $this->currentContext->expectedSize = $this->appendInt16Data($byte, false);

        if (0 === $this->currentContext->expectedSize) {
            $this->popStateAndEmitValue('');
        } else {
            $this->currentContext->buffer = '';
            $this->setState(ValueDecoderState::STRING_DATA());
        }
    }

    private function handleStringData($byte)
    {
        if ($this->appendStringData($byte)) {
            $this->popStateAndEmitBuffer();
        }
    }

    public function handleStringChunkSize($byte, $isFinal)
    {
        $this->currentContext->expectedSize = $this->appendInt16Data($byte, false);

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

    public function handleStringChunkData($byte, $isFinal)
    {
        if (!$this->appendStringData($byte)) {
            return;
        }

        $this->currentContext->result .= $this->currentContext->buffer;
        $this->currentContext->buffer = '';

        if ($isFinal) {
            $this->popStateAndEmitResult();
        } else {
            $this->setState(ValueDecoderState::STRING_CHUNK_CONTINUATION());
        }
    }

    public function handleStringChunkContinuation($byte)
    {
        switch ($byte) {
            case HessianConstants::STRING_CHUNK:
                return $this->setState(ValueDecoderState::STRING_CHUNK_SIZE());
            case HessianConstants::STRING_CHUNK_FINAL:
                return $this->setState(ValueDecoderState::STRING_CHUNK_FINAL_SIZE());
        }

        throw new Exception\DecodeException('Invalid byte at start of string chunk: 0x' . dechex($byte) . ' (state: ' . $this->state() . ').');
    }

    private function handleBinarySize($byte)
    {
        $this->currentContext->expectedSize = $this->appendInt16Data($byte, false);

        if (0 === $this->currentContext->expectedSize) {
            $this->popStateAndEmitValue('');
        } else {
            $this->currentContext->buffer = '';
            $this->setState(ValueDecoderState::BINARY_DATA());
        }
    }

    private function handleBinaryData($byte)
    {
        if ($this->appendBinaryData($byte)) {
            $this->popStateAndEmitBuffer();
        }
    }

    public function handleBinaryChunkSize($byte, $isFinal)
    {
        $this->currentContext->expectedSize = $this->appendInt16Data($byte, false);

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

    public function handleBinaryChunkData($byte, $isFinal)
    {
        if (!$this->appendBinaryData($byte)) {
            return;
        }

        $this->currentContext->result .= $this->currentContext->buffer;
        $this->currentContext->buffer = '';

        if ($isFinal) {
            $this->popStateAndEmitResult();
        } else {
            $this->setState(ValueDecoderState::BINARY_CHUNK_CONTINUATION());
        }
    }

    public function handleBinaryChunkContinuation($byte)
    {
        switch ($byte) {
            case HessianConstants::BINARY_CHUNK:
                return $this->setState(ValueDecoderState::BINARY_CHUNK_SIZE());
            case HessianConstants::BINARY_CHUNK_FINAL:
                return $this->setState(ValueDecoderState::BINARY_CHUNK_FINAL_SIZE());
        }

        throw new Exception\DecodeException('Invalid byte at start binary of chunk: 0x' . dechex($byte) . ' (state: ' . $this->state() . ').');
    }

    private function handleDouble1($byte)
    {
        $this->popStateAndEmitValue(
            floatval(Utility::byteToSigned($byte))
        );
    }

    private function handleDouble2($byte)
    {
        $value = $this->appendInt16Data($byte);

        if (null !== $value) {
            $this->popStateAndEmitValue(floatval($value));
        }
    }

    private function handleDouble4($byte)
    {
        $this->currentContext->buffer .= pack('C', $byte);

        if (4 === strlen($this->currentContext->buffer)) {
            list(, $value) = unpack(
                'f',
                Utility::convertEndianness($this->currentContext->buffer)
            );
            $this->popStateAndEmitValue($value);
        }
    }

    private function handleDouble8($byte)
    {
        $this->currentContext->buffer .= pack('C', $byte);

        if (8 === strlen($this->currentContext->buffer)) {
            list(, $value) = unpack(
                'd',
                Utility::convertEndianness($this->currentContext->buffer)
            );
            $this->popStateAndEmitValue($value);
        }
    }

    public function handleInt32($byte)
    {
        $value = $this->appendInt32Data($byte);

        if (null !== $value) {
            $this->popStateAndEmitValue($value);
        }
    }

    public function handleInt64($byte)
    {
        $value = $this->appendInt64Data($byte);

        if (null !== $value) {
            $this->popStateAndEmitValue($value);
        }
    }

    public function handleTimestampMilliseconds($byte)
    {
        $value = $this->appendInt64Data($byte);

        if (null !== $value) {
            $this->popStateAndEmitValue(DateTime::fromUnixTime($value / 1000));
        }
    }

    public function handleTimestampMinutes($byte)
    {
        $value = $this->appendInt32Data($byte);

        if (null !== $value) {
            $this->popStateAndEmitValue(DateTime::fromUnixTime($value * 60));
        }
    }

    public function handleNextCollectionElement($byte)
    {
        if (HessianConstants::COLLECTION_TERMINATOR === $byte) {
            $this->popStateAndEmitResult();
        } else {
            $this->handleBegin($byte);
        }
    }

    public function handleCollectionType($byte)
    {
        if ($this->handleBeginString($byte)) {
            return;
        } elseif ($this->handleBeginInt32($byte)) {
            return;
        }

        throw new Exception\DecodeException('Invalid byte at start of collection type: 0x' . dechex($byte) . ' (state: ' . $this->state() . ').');
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
    private function pushState(ValueDecoderState $state, $result = '')
    {
        $this->trace('PUSH: ' . $state);

        $context = new stdClass;
        $context->state = $state;
        $context->buffer = '';
        $context->result = $result;
        $context->nextKey = null;
        $context->expectedSize = null;
        $context->definition = null;

        $this->stack->push($context);

        if (is_object($result)) {
            $this->objects->pushBack($result);
        }

        $this->currentContext = $context;
    }

    private function setState(ValueDecoderState $state)
    {
        $this->trace('SET: ' . $state);

        $this->currentContext->state = $state;
    }

    private function popState()
    {
        $previousState = $this->state();

        $this->stack->pop();

        if ($this->stack->isEmpty()) {
            $this->currentContext = null;
        } else {
            $this->currentContext = $this->stack->next();
        }

        $this->trace('POP ' . $previousState . ' (SET: ' . $this->state() . ')');
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

    private function popStateAndEmitResult()
    {
        $this->popStateAndEmitValue($this->currentContext->result);
    }

    private function appendStringData($byte)
    {
        $this->currentContext->buffer .= pack('C', $byte);

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

    private function appendBinaryData($byte)
    {
        $this->currentContext->buffer .= pack('C', $byte);

        if (strlen($this->currentContext->buffer) < $this->currentContext->expectedSize) {
            return false;
        }

        return true;
    }

    private function appendInt16Data($byte, $signed = true)
    {
        $this->currentContext->buffer .= pack('C', $byte);

        if (2 === strlen($this->currentContext->buffer)) {
            list(, $value) = unpack(
                $signed ? 's' : 'S',
                Utility::convertEndianness($this->currentContext->buffer)
            );

            return $value;
        }

        return null;
    }

    private function appendInt32Data($byte)
    {
        $this->currentContext->buffer .= pack('C', $byte);

        if (4 === strlen($this->currentContext->buffer)) {
            list(, $value) = unpack(
                'l',
                Utility::convertEndianness($this->currentContext->buffer)
            );

            return $value;
        }

        return null;
    }

    public function appendInt64Data($byte)
    {
        $this->currentContext->buffer .= pack('C', $byte);

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
    private $classDefinitions;
    private $objects;
    private $stack;
    private $currentContext;
    private $value;
}
