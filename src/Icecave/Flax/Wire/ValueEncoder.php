<?php
namespace Icecave\Flax\Wire;

use DateTime;
use Icecave\Chrono\TimePointInterface;
use Icecave\Collections\Collection;
use Icecave\Collections\Map;
use Icecave\Flax\TypeCheck\TypeCheck;
use InvalidArgumentException;
use stdClass;

class ValueEncoder
{
    public function __construct()
    {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        $this->references = new Map;
        $this->classDefinitions = new Map;
    }

    public function reset()
    {
        $this->typeCheck->reset(func_get_args());

        $this->references->clear();
        $this->classDefinitions->clear();
    }

    /**
     * @param mixed $value
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function encode($value)
    {
        $this->typeCheck->encode(func_get_args());

        $type = gettype($value);

        switch ($type) {
            case 'integer':
                return $this->encodeInteger($value);
            case 'boolean':
                return $this->encodeBoolean($value);
            case 'string':
                return $this->encodeString($value);
            case 'double':
                return $this->encodeDouble($value);
            case 'array':
                return $this->encodeArray($value);
            case 'object':
                return $this->encodeObject($value);
            case 'NULL':
                return $this->encodeNull();
        }

        throw new InvalidArgumentException('Can not encode value of type "' . $type . '".');
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function encodeBinary($value)
    {
        $this->typeCheck->encodeBinary(func_get_args());

        $length = strlen($value);

        if ($length <= Constants::BINARY_COMPACT_LIMIT) {
            return pack('c', $length + Constants::BINARY_COMPACT_START) . $value;
        } elseif ($length <= Constants::BINARY_LIMIT) {
            return pack(
                'cc',
                ($length >> 8) + Constants::BINARY_START,
                ($length)
            ) . $value;
        }

        $buffer = '';

        do {
            if ($length > Constants::BINARY_CHUNK_SIZE) {
                $chunkLength = Constants::BINARY_CHUNK_SIZE;
                $buffer .= pack('c', Constants::BINARY_CHUNK);
            } else {
                $chunkLength = $length;
                $buffer .= pack('c', Constants::BINARY_CHUNK_FINAL);
            }

            $buffer .= pack('n', $chunkLength);
            $buffer .= substr($value, 0, $chunkLength);

            $value = substr($value, $chunkLength);
            $length -= $chunkLength;

        } while ($length);

        return $buffer;
    }

    /**
     * @param integer $timestamp Number of milliseconds since unix epoch.
     *
     * @return string
     */
    public function encodeTimestamp($timestamp)
    {
        $this->typeCheck->encodeTimestamp(func_get_args());

        if ($timestamp % Constants::TIMESTAMP_MILLISECONDS_PER_MINUTE) {
            return pack('c', Constants::TIMESTAMP_MILLISECONDS) . Utility::packInt64($timestamp);
        } else {
            return pack(
                'cN',
                Constants::TIMESTAMP_MINUTES,
                $timestamp / Constants::TIMESTAMP_MILLISECONDS_PER_MINUTE
            );
        }
    }

    /**
     * @param integer $value
     *
     * @return string
     */
    private function encodeInteger($value)
    {
        // 1-byte ...
        if (Constants::INT32_1_MIN <= $value && $value <= Constants::INT32_1_MAX) {
            return pack('c', $value + Constants::INT32_1_OFFSET);

        // 2-bytes ...
        } elseif (Constants::INT32_2_MIN <= $value && $value <= Constants::INT32_2_MAX) {
            return pack(
                'cc',
                ($value >> 8) + Constants::INT32_2_OFFSET,
                ($value)
            );

        // 3-bytes ...
        } elseif (Constants::INT32_3_MIN <= $value && $value <= Constants::INT32_3_MAX) {
            return pack(
                'ccc',
                ($value >> 16) + Constants::INT32_3_OFFSET,
                ($value >> 8),
                ($value)
            );

        // 4-bytes ...
        } elseif (!(Constants::INT64_HIGH_MASK & $value)) {
            return pack('cN', Constants::INT32_4, $value);
        }

        return pack('c', Constants::INT64_8) . Utility::packInt64($value);
    }

    /**
     * @param boolean $value
     *
     * @return string
     */
    private function encodeBoolean($value)
    {
        return pack('c', $value ? Constants::BOOLEAN_TRUE : Constants::BOOLEAN_FALSE);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function encodeString($value)
    {
        $length = mb_strlen($value, 'utf8');

        if ($length <= Constants::STRING_COMPACT_LIMIT) {
            return pack('c', $length + Constants::STRING_COMPACT_START) . $value;
        } elseif ($length <= Constants::STRING_LIMIT) {
            return pack(
                'cc',
                ($length >> 8) + Constants::STRING_START,
                ($length)
            ) . $value;
        }

        $buffer = '';

        do {
            if ($length > Constants::STRING_CHUNK_SIZE) {
                $chunkLength = Constants::STRING_CHUNK_SIZE;
                $buffer .= pack('c', Constants::STRING_CHUNK);
            } else {
                $chunkLength = $length;
                $buffer .= pack('c', Constants::STRING_CHUNK_FINAL);
            }

            $buffer .= pack('n', $chunkLength);
            $buffer .= mb_substr($value, 0, $chunkLength, 'utf8');

            // can not use default of 'null' for length in php 5.3
            $value = mb_substr($value, $chunkLength, $length, 'utf8');
            $length -= $chunkLength;

        } while ($length);

        return $buffer;
    }

    /**
     * @param double $value
     *
     * @return string
     */
    private function encodeDouble($value)
    {
        if (0.0 === $value) {
            return pack('c', Constants::DOUBLE_ZERO);
        } elseif (1.0 === $value) {
            return pack('c', Constants::DOUBLE_ONE);
        }

        $fraction = fmod($value, 1);

        if (0.0 == $fraction) {
            if (Constants::DOUBLE_1_MIN <= $value && $value <= Constants::DOUBLE_1_MAX) {
                return pack('cc', Constants::DOUBLE_1, $value);
            } elseif (Constants::DOUBLE_2_MIN <= $value && $value <= Constants::DOUBLE_2_MAX) {
                return pack('cn', Constants::DOUBLE_2, $value);
            }
        }

        $bytes = pack('f', $value);
        $unpacked = current(unpack('f', $bytes));

        if ($value === $unpacked) {
            return pack('c', Constants::DOUBLE_4) . Utility::convertEndianness($bytes);
        }

        return pack('c', Constants::DOUBLE_8) . Utility::convertEndianness(pack('d', $value));
    }

    /**
     * @return string
     */
    private function encodeNull()
    {
        return pack('c', Constants::NULL_VALUE);
    }

    /**
     * @param array $value
     *
     * @return string
     */
    private function encodeArray(array $value)
    {
        if (Collection::isSequential($value)) {
            return $this->encodeVector($value);
        } else {
            return $this->encodeMap($value);
        }
    }

    /**
     * @param array $value
     *
     * @return string
     */
    private function encodeVector(array $value)
    {
        $size = count($value);

        if (0 === $size) {
            $buffer = pack('cc', Constants::VECTOR, Constants::COLLECTION_TERMINATOR);
        } elseif ($size <= Constants::VECTOR_FIXED_COMPACT_LIMIT) {
            $buffer = pack('c', $size + Constants::VECTOR_FIXED_COMPACT_START);
        } else {
            $buffer = pack('c', Constants::VECTOR_FIXED) . $this->encodeInteger($size);
        }

        foreach ($value as $element) {
            $buffer .= $this->encode($element);
        }

        return $buffer;
    }

    /**
     * @param array $value
     *
     * @return string
     */
    private function encodeMap(array $value)
    {
        $size = count($value);
        $buffer = pack('c', Constants::MAP);

        foreach ($value as $key => $value) {
            $buffer .= $this->encode($key);
            $buffer .= $this->encode($value);
        }

        $buffer .= pack('c', Constants::COLLECTION_TERMINATOR);

        return $buffer;
    }

    /**
     * @param object $value
     *
     * @return string
     */
    private function encodeObject($value)
    {
        if ($value instanceof DateTime) {
            return $this->encodeTimestamp($value->getTimestamp() * 1000);
        } elseif ($value instanceof TimePointInterface) {
            return $this->encodeTimestamp($value->unixTime() * 1000);
        } elseif ('stdClass' !== get_class($value)) {
            throw new InvalidArgumentException('Can not encode object of type "' . get_class($value) . '".');
        }

        $ref = null;
        if ($this->findReference($value, $ref)) {
            return $this->encodeReference($ref);
        }

        return $this->encodeStdClass($value);
    }

    /**
     * @param stdClass $value
     *
     * @return string
     */
    private function encodeStdClass(stdClass $value)
    {
        $sortedProperties = (array) $value;
        ksort($sortedProperties);

        $buffer = '';

        $defId = null;
        if (!$this->findClassDefinition($value, $defId)) {
            $buffer .= $this->encodeClassDefinition('stdClass', array_keys($sortedProperties));
        }

        if ($defId <= Constants::OBJECT_INSTANCE_COMPACT_LIMIT) {
            $buffer .= pack('c', $defId + Constants::OBJECT_INSTANCE_COMPACT_START);
        } else {
            $buffer .= pack('c', Constants::OBJECT_INSTANCE) . $this->encodeInteger($defId);
        }

        foreach ($sortedProperties as $value) {
            $buffer .= $this->encode($value);
        }

        return $buffer;
    }

    /**
     * @param stdClass     $value
     * @param integer|null &$ref
     *
     * @return boolean
     */
    private function findReference(stdClass $value, &$ref = null)
    {
        if (!$this->references->tryGet($value, $ref)) {
            $this->references[$value] = $ref = $this->references->size();

            return false;
        }

        return true;
    }

    /**
     * @param integer $ref
     *
     * @return string
     */
    private function encodeReference($ref)
    {
        return pack('c', Constants::REFERENCE) . $this->encodeInteger($ref);
    }

    /**
     * @param stdClass     $value
     * @param integer|null &$defId
     *
     * @return boolean
     */
    private function findClassDefinition(stdClass $value, &$defId = null)
    {
        $key = $this->classDefinitionKey($value);

        if (!$this->classDefinitions->tryGet($key, $defId)) {
            $this->classDefinitions[$key] = $defId = $this->classDefinitions->size();

            return false;
        }

        return true;
    }

    /**
     * @param string        $className
     * @param array<string> $propertyNames
     *
     * @return string
     */
    private function encodeClassDefinition($className, array $propertyNames)
    {
        $buffer  = pack('c', Constants::CLASS_DEFINITION) . $this->encodeString($className);
        $buffer .= $this->encodeInteger(count($propertyNames));

        foreach ($propertyNames as $name) {
            $buffer .= $this->encodeString($name);
        }

        return $buffer;
    }

    /**
     * @param stdClass $value
     *
     * @return string
     */
    private function classDefinitionKey(stdClass $value)
    {
        $properties = get_object_vars($value);
        ksort($properties);

        return implode(',', array_keys($properties));
    }

    private $typeCheck;
    private $references;
    private $classDefinitions;
}
