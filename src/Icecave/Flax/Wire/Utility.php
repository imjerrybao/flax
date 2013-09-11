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

    public static function convertEndianness($string)
    {
        if (self::isBigEndian()) {
            return $string;
        }

        return strrev($string);
    }

    /**
     * @param integer $value
     *
     * @return string
     */
    public static function packInt64($value)
    {
        TypeCheck::get(__CLASS__)->packInt64(func_get_args());

        return pack(
            'NN',
            (0xffffffff00000000 & $value) >> 32,
            (0x00000000ffffffff & $value)
        );
    }

    /**
     * @param string $bytes
     *
     * @return integer
     */
    public static function unpackInt64($bytes)
    {
        TypeCheck::get(__CLASS__)->unpackInt64(func_get_args());

        list($high, $low) = unpack('N2', $bytes);

        return ($high << 32) | $low;
    }
}
