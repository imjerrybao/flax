<?php
namespace Icecave\Flax\Wire;

use DateTime;
use Icecave\Chrono\TimePointInterface;
use Icecave\Collections\Collection;
use Icecave\Collections\Map;
use Icecave\Flax\TypeCheck\TypeCheck;
use InvalidArgumentException;
use stdClass;

class ValueDecoder
{
    public function __construct()
    {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        $this->reset();
    }

    public function reset()
    {
        $this->typeCheck->reset(func_get_args());

        $this->stack = new Stack;
    }

    public function feed($buffer)
    {
        $this->typeCheck->feed(func_get_args());

        $length = strlen($buffer);

        for ($index = 0; $index < $length; ++$index) {
            $this->feedByte(ord($buffer[$index]));
        }
    }

    public function feedByte($byte)
    {
        if (!$this->stack->isEmpty()) {
            switch ($this->stack->next()->state) {
            }
        }

        return $this->doBegin($byte);
    }

    public function finalize()
    {
        $this->typeCheck->finalize(func_get_args());
    }

    public function values()
    {
        $this->typeCheck->values(func_get_args());

    }

    private function doBegin($byte)
    {
        switch ($byte) {
            case Constants::STRING_CHUNK:
                return $this->push(ValueDecoderState::STRING_CHUNK())
            case Constants::STRING_CHUNK_FINAL:
                return $this->push(ValueDecoderState::STRING_CHUNK_FINAL())
            case Constants::BINARY_CHUNK:
                return $this->push(ValueDecoderState::BINARY_CHUNK())
            case Constants::BINARY_CHUNK_FINAL:
                return $this->push(ValueDecoderState::BINARY_CHUNK_FINAL())
            case Constants::INT32_4:
                return $this->push(ValueDecoderState::INT32_4())
            case Constants::INT64_4:
                return $this->push(ValueDecoderState::INT64_4())
            case Constants::INT64_8:
                return $this->push(ValueDecoderState::INT64_8())
            case Constants::DOUBLE_ZERO:
                return $this->emitValue(0.0);
            case Constants::DOUBLE_ONE:
                return $this->emitValue(1.0);
            case Constants::DOUBLE_1:
                return $this->push(ValueDecoderState::DOUBLE_1())
            case Constants::DOUBLE_2:
                return $this->push(ValueDecoderState::DOUBLE_2())
            case Constants::DOUBLE_4:
                return $this->push(ValueDecoderState::DOUBLE_4())
            case Constants::DOUBLE_8:
                return $this->push(ValueDecoderState::DOUBLE_8())
            case Constants::BOOLEAN_TRUE:
                return $this->emitValue(true);
            case Constants::BOOLEAN_FALSE:
                return $this->emitValue(false);
            case Constants::NULL_VALUE:
                return $this->emitValue(null);
            case Constants::TIMESTAMP_MILLISECONDS:
                return $this->push(ValueDecoderState::TIMESTAMP_MILLISECONDS());
            case Constants::TIMESTAMP_MINUTES:
                return $this->push(ValueDecoderState::TIMESTAMP_MINUTES());
            case Constants::CLASS_DEFINITION:
                return $this->push(ValueDecoderState::CLASS_DEFINITION());
            case Constants::OBJECT_INSTANCE:
                return $this->push(ValueDecoderState::OBJECT_INSTANCE());
            case Constants::REFERENCE:
                return $this->push(ValueDecoderState::REFERENCE());
            case Constants::VECTOR_TYPED:
                return $this->push(ValueDecoderState::VECTOR_TYPED());
            case Constants::VECTOR_TYPED_FIXED:
                return $this->push(ValueDecoderState::VECTOR_TYPED_FIXED());
            case Constants::VECTOR:
                return $this->push(ValueDecoderState::VECTOR());
            case Constants::VECTOR_FIXED:
                return $this->push(ValueDecoderState::VECTOR_FIXED());
            case Constants::MAP_TYPED:
                return $this->push(ValueDecoderState::MAP_TYPED());
            case Constants::MAP:
                return $this->push(ValueDecoderState::MAP());
        }

        if (Constants::STRING_COMPACT_START <= $byte && $byte <= Constants::STRING_COMPACT_END) {
            return $this->doBeginCompactString($byte);
        } elseif (Constants::STRING_START <= $byte && $byte <= Constants::STRING_END) {
            return $this->doBeginString($byte);
        } elseif (Constants::BINARY_COMPACT_START <= $byte && $byte <= Constants::BINARY_COMPACT_END) {
            return $this->doBeginCompactBinary($byte);
        } elseif (Constants::BINARY_START <= $byte && $byte <= Constants::BINARY_END) {
            return $this->doBeginBinary($byte);
        } elseif (Constants::INT32_1_START <= $byte && $byte <= Constants::INT32_1_END) {
            return $this->doBeginInt32Compact1($byte);
        } elseif (Constants::INT32_2_START <= $byte && $byte <= Constants::INT32_2_END) {
            return $this->doBeginInt32Compact2($byte);
        } elseif (Constants::INT32_3_START <= $byte && $byte <= Constants::INT32_3_END) {
            return $this->doBeginInt32Compact3($byte);
        } elseif (Constants::INT64_1_START <= $byte && $byte <= Constants::INT64_1_END) {
            return $this->doBeginInt64Compact1($byte);
        } elseif (Constants::INT64_2_START <= $byte && $byte <= Constants::INT64_2_END) {
            return $this->doBeginInt64Compact2($byte);
        } elseif (Constants::INT64_3_START <= $byte && $byte <= Constants::INT64_3_END) {
            return $this->doBeginInt64Compact3($byte);
        } elseif (Constants::OBJECT_INSTANCE_COMPACT_START <= $byte && $byte <= Constants::OBJECT_INSTANCE_COMPACT_END) {
            return $this->doBeginCompactObjectInstance($byte);
        } elseif (Constants::VECTOR_TYPED_FIXED_COMPACT_START <= $byte && $byte <= Constants::VECTOR_TYPED_FIXED_COMPACT_END) {
            return $this->doBeginCompactTypedFixedLengthVector($byte);
        } elseif (Constants::VECTOR_FIXED_COMPACT_START <= $byte && $byte <= Constants::VECTOR_FIXED_COMPACT_END) {
            return $this->doBeginCompactFixedLengthVector($byte);
        }

        throw new Exception\DecodeException('Invalid byte at start of value: 0x' . dechex($byte) . '.');
    }

    private function doBeginChunkedString($isFinal = false)
    {

    }

    private function doBeginChunkedBinary($isFinal = false)
    {

    }

    private function doBeginInt32()
    {
        $this->push(ValueDecoderState::INT32())
    }

    private function doInt32($byte)
    {
        $this->buffer .= $byte;

        if (4 === strlen($this->buffer)) {
            $this->emitValue(
                unpack('N', $this->buffer)
            );
        }
    }

    private function doBeginInt64Compact4()
    {
        $this->push(ValueDecoderState::INT64_2())
    }

    private function

    private function doBeginInt64()
    {

    }

    private function doBeginDoubleCompact1()
    {

    }

    private function doBeginDoubleCompact2()
    {

    }

    private function doBeginDoubleCompact4()
    {

    }

    private function doBeginDouble()
    {

    }

    private function doBeginValue($value)
    {

    }

    private function doBeginTimestampMilliseconds()
    {

    }

    private function doBeginTimestampMinutes()
    {

    }

    private function doBeginClassDefinition()
    {

    }

    private function doBeginObjectInstance()
    {

    }

    private function doBeginReference()
    {

    }

    private function doBeginTypedVector()
    {

    }

    private function doBeginTypedFixedLengthFector()
    {

    }

    private function doBeginVector()
    {

    }

    private function doBeginFixedLengthVector()
    {

    }

    private function doBeginTypedMap()
    {

    }

    private function doBeginMap()
    {

    }

    private function doBeginCompactString($byte)
    {

    }

    private function doBeginString($byte)
    {

    }

    private function doBeginCompactBinary($byte)
    {

    }

    private function doBeginBinary($byte)
    {

    }

    private function doBeginInt32Compact1($byte)
    {

    }

    private function doBeginInt32Compact2($byte)
    {

    }

    private function doBeginInt32Compact3($byte)
    {

    }

    private function doBeginInt64Compact1($byte)
    {

    }

    private function doBeginInt64Compact2($byte)
    {

    }

    private function doBeginInt64Compact3($byte)
    {

    }

    private function doBeginCompactObjectInstance($byte)
    {

    }

    private function doBeginCompactTypedFixedLengthVector($byte)
    {

    }

    private function doBeginCompactFixedLengthVector($byte)
    {

    }

    /**
     * @param mixed       $value
     * @param ParserState $state
     */
    private function push($value, ValueDecoderState $state)
    {
        $entry = new stdClass;
        // $entry->value = $value;
        // $entry->key = null;
        $entry->state = $state;
        $this->stack->push($entry);

        // if (is_array($value)) {
        //     $this->emit('array-open');
        // } elseif (is_object($value)) {
        //     $this->emit('object-open');
        // }
    }

    /**
     * @return stdClass
     */
    private function pop()
    {
        $value = $this->stack->pop()->value;

        // if (is_array($value)) {
        //     $this->emit('array-close');
        // } elseif (is_object($value)) {
        //     $this->emit('object-close');
        // }

        return $value;
    }

    private $typeCheck;
    private $state;
    private $expectedLength;
    private $buffer;
}
