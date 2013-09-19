<?php
namespace Icecave\Flax\Wire;

use Icecave\Flax\TypeCheck\TypeCheck;

abstract class Utility
{
    /**
     * @return boolean
     */
    public static function isBigEndian()
    {
        TypeCheck::get(__CLASS__)->isBigEndian(func_get_args());

        static $isBigEndian = null;

        if (null === $isBigEndian) {
            $isBigEndian = pack('S', 0x1020) === pack('n', 0x1020);
        }

        return $isBigEndian;
    }

    /**
     * @param string $buffer
     *
     * @return string
     */
    public static function convertEndianness($buffer)
    {
        TypeCheck::get(__CLASS__)->convertEndianness(func_get_args());

        return self::isBigEndian()
            ? $buffer
            : strrev($buffer);
    }

    /**
     * @param integer $value
     *
     * @return string
     */
    public static function packInt64($value)
    {
        TypeCheck::get(__CLASS__)->packInt64(func_get_args());

        $hi = (0xffffffff00000000 & $value) >> 32;
        $lo = (0x00000000ffffffff & $value);

        return pack('NN', $hi, $lo);
    }

    /**
     * @param string $bytes
     *
     * @return integer
     */
    public static function unpackInt64($bytes)
    {
        TypeCheck::get(__CLASS__)->unpackInt64(func_get_args());

        list(, $hi, $lo) = unpack('N2', $bytes);

        return ($hi << 32) | $lo;
    }

    public static function byteToUnsigned($byte)
    {
        list(, $value) = unpack('C', pack('c', $byte));

        return $value;
    }

    public static function byteToSigned($byte)
    {
        list(, $value) = unpack('c', pack('C', $byte));

        return $value;
    }
}
