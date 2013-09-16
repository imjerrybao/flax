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

        throw new InvalidArgumentException;
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

        if ($length < 15) {
            return pack('c', $length + 0x20) . $value;
        }

        $buffer = '';

        do {
            if ($length > self::MAX_CHUNK_LENGTH) {
                $chunkLength = self::MAX_CHUNK_LENGTH;
                $buffer .= 'A';
            } else {
                $chunkLength = $length;
                $buffer .= 'B';
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

        $msPerMinute = 60000;

        if ($timestamp % $msPerMinute) {
            return "\x4a" . Utility::packInt64($timestamp);
        } else {
            return "\x4b" . pack('N', $timestamp / $msPerMinute);
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
        if (-16 <= $value && $value <= 47) {
            return pack('c', $value + 0x90);

        // 2-bytes ...
        } elseif (-2048 <= $value && $value <= 2047) {
            return pack(
                'cc',
                ($value >> 8) + 0xc8,
                ($value)
            );

        // 3-bytes ...
        } elseif (-262144 <= $value && $value <= 262143) {
            return pack(
                'ccc',
                ($value >> 16) + 0xd4,
                ($value >> 8),
                ($value)
            );
        // 4-bytes ...
        } elseif (-2147483648 <= $value && $value <= 2147483647) {
            return 'I' . pack('N', $value);
        }

        return 'L' . Utility::packInt64($value);
    }

    /**
     * @param boolean $value
     *
     * @return string
     */
    private function encodeBoolean($value)
    {
        return $value ? 'T' : 'F';
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function encodeString($value)
    {
        $length = mb_strlen($value, 'utf8');

        if ($length < 32) {
            return pack('c', $length) . $value;
        } elseif ($length < 1024) {
            return pack(
                'cc',
                ($length >> 8) + 0x30,
                ($length & 0xff)
            ) . $value;
        }

        $buffer = '';

        do {
            if ($length > self::MAX_CHUNK_LENGTH) {
                $chunkLength = self::MAX_CHUNK_LENGTH;
                $buffer .= 'R';
            } else {
                $chunkLength = $length;
                $buffer .= 'S';
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
            return "\x5b";
        } elseif (1.0 === $value) {
            return "\x5c";
        }

        $fraction = fmod($value, 1);

        if (0.0 == $fraction) {
            if (-128.0 <= $value && $value <= 127.0) {
                return "\x5d" . pack('c', intval($value));
            } elseif (-32768.0 <= $value && $value <= 32767.0) {
                return "\x5e" . pack('n', intval($value));
            }
        }

        $bytes = pack('f', $value);
        $unpacked = current(unpack('f', $bytes));

        if ($value === $unpacked) {
            return "\x5f" . Utility::convertEndianness($bytes);
        }

        return 'D' . Utility::convertEndianness(pack('d', $value));
    }

    /**
     * @return string
     */
    private function encodeNull()
    {
        return 'N';
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
            $buffer = "\x57Z";
        } elseif ($size <= 7) {
            $buffer = pack('c', $size + 0x78);
        } else {
            $buffer = "\x58" . $this->encodeInteger($size);
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
        $buffer = 'H';

        foreach ($value as $key => $value) {
            $buffer .= $this->encode($key);
            $buffer .= $this->encode($value);
        }

        $buffer .= 'Z';

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
            throw new InvalidArgumentException('Can not encode object of type ' . get_class($value) . '.');
        }

        $ref = null;
        if ($this->findReference($value, $ref)) {
            return $this->encodeReference($ref);
        }

        $this->encodeStdClass($value);
    }

    /**
     * @param stdClass $value
     *
     * @return string
     */
    private function encodeStdClass(stdClass $value)
    {
        $sortedProperties = (array)$value;
        ksort($sortedProperties);

        $buffer = '';

        $defId = null;
        if (!$this->findClassDefinition($value, $ref)) {
            $buffer .= $this->encodeClassDefinition('stdClass', array_keys($sortedProperties));
        }

        if ($defId < 16) {
            $buffer .= pack('c', $defId + 0x60);
        } else {
            $buffer .= 'O' . $this->encodeInteger($defId);
        }

        foreach ($sortedProperties as $value) {
            $buffer .= $this->encode($value);
        }

        return $buffer;
    }

    /**
     * @param string $className
     * @param array<string> $propertyNames
     *
     * @return string
     */
    private function encodeClassDefinition($className, array $propertyNames)
    {
        $buffer  = 'C' . $this->encodeString($className);
        $buffer .= $this->encodeInteger(count($propertyNames));

        foreach ($propertyNames as $name) {
            $buffer .= $this->encodeString($name);
        }

        return $buffer;
    }

    /**
     * @param integer $ref
     *
     * @return string
     */
    private function encodeReference($ref)
    {
        return "\x51" . $this->encodeInteger($ref);
    }

   /**
     * @param stdClass $value
     * @param integer|null &$defId
     *
     * @return boolean
     */
    private function findClassDefinition(stdClass $value, &$defId = null)
    {
        $key = $this->classDefinitionKey($value);

        if (!$this->classDefinitions->tryGet($key, $defId)) {
            $this->classDefinitions[$key] = $defId = $this->references->size();

            return false;
        }

        return true;
    }

    /**
     * @param stdClass $value
     *
     * @return string
     */
    private function classDefinitionKey(stdClass $value)
    {
        $className = get_class($value);

        if ('stdClass' !== $className) {
            return $className;
        }

        $properties = get_object_vars($value);
        ksort($properties);

        return 'stdClass:' . implode(',', array_keys($properties));
    }

    const MAX_CHUNK_LENGTH = 0xffff;

    private $typeCheck;
    private $references;
    private $classDefinitions;
}
