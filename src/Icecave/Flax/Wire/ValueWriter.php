<?php
namespace Icecave\Flax\Wire;

use Icecave\Flax\TypeCheck\TypeCheck;
use InvalidArgumentException;

class ValueWriter
{
    /**
     * @param string &$buffer
     * @param mixed $value
     */
    public function write(&$buffer, $value)
    {
        TypeCheck::get(__CLASS__)->write(func_get_args());

        $buffer .= $this->encode($value);
    }

    /**
     * @param mixed $value
     */
    public function encode($value)
    {
        TypeCheck::get(__CLASS__)->encode(func_get_args());

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
     * @param integer $value
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
     */
    private function encodeBoolean($value)
    {
        return $value ? 'T' : 'F';
    }

    /**
     * @param string $value
     */
    private function encodeString($value)
    {
        $length = mb_strlen($value, 'utf8');

        if ($length < 32) {
            return pack('c', $length) . $value;
        } elseif ($length < 1024) {
            return pack(
                'CC',
                ($length >> 8) + 0x30,
                ($length & 0xff)
            ) . $value;
        }

        throw new \Exception;
    }

    /**
     * @param double $value
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

        if ($value == $unpacked) {
            return "\x5f" . Utility::convertEndianness($bytes);
        }

        return 'D' . Utility::convertEndianness(pack('d', $value));
    }

    /**
     * @param array $value
     */
    private function encodeArray(array $value)
    {
        throw new \Exception;
    }

    /**
     * @param object $value
     */
    private function encodeObject($value)
    {
        throw new \Exception;
    }

    private function encodeNull()
    {
        return 'N';
    }
}
