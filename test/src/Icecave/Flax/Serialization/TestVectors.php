<?php
namespace Icecave\Flax\Serialization;

use DateTime as NativeDateTime;
use Icecave\Chrono\DateTime;
use Icecave\Collections\Map;
use Icecave\Collections\Vector;
use Icecave\Flax\Binary;
use Icecave\Flax\Object;
use stdClass;

abstract class TestVectors
{
    public static function canonicalTestVectors()
    {
        return [

            ////////////
            // scalar //
            ////////////

            'null' => [
                'N',
                null,
            ],
            'boolean - true' => [
                'T',
                true,
            ],
            'boolean - false' => [
                'F',
                false
            ],

            ////////////
            // string //
            ////////////

            'string - compact - empty' => [
                "\x00",
                "",
            ],
            'string - compact - hello' => [
                "\x05hello",
                "hello",
            ],
            'string - compact - unicode' => [
                "\x01\xc3\x83",
                "\xc3\x83",
            ],

            ////////////
            // binary //
            ////////////

            'binary - compact - empty' => [
                "\x20",
                new Binary(),
            ],
            'binary - compact - hello' => [
                "\x25hello",
                new Binary("hello"),
            ],

            ////////////////////
            // 32-bit integer //
            ////////////////////

            'integer - 1 octet zero' => [
                "\x90",
                0,
            ],
            'integer - 1 octet min' => [
                "\x80",
                -16,
            ],
            'integer - 1 octet max' => [
                "\xbf",
                47,
            ],
            'integer - 1 octet midway' => [
                "\x88",
                -8,
            ],
            'integer - 2 octet min' => [
                "\xc0\x00",
                -2048,
            ],
            'integer - 2 octet max' => [
                "\xcf\xff",
                2047,
            ],
            'integer - 2 octet midway 1' => [
                "\xc7\x00",
                -256,
            ],
            'integer - 2 octet midway 2' => [
                "\xc8\x30",
                0x30,
            ],
            'integer - 3 octet min' => [
                "\xd0\x00\x00",
                -262144,
            ],
            'integer - 3 octet max' => [
                "\xd7\xff\xff",
                262143,
            ],
            'integer - 3 octet midway' => [
                "\xd3\xf0\x60",
                -4000,
            ],
            'integer - 4 octet 1' => [
                "I\x10\x20\x30\x40",
                0x10203040,
            ],
            'integer - 4 octet 2' => [
                "I\xff\xfb\xff\xff",
                -0x40001,
            ],

            ////////////////////
            // 64-bit integer //
            ////////////////////

            'long - 8 octet' => [
                "L\x10\x20\x30\x40\x50\x60\x70\x80",
                0x1020304050607080,
                PHP_INT_SIZE < 8
            ],

            ////////////
            // double //
            ////////////

            'double - 1 octet zero' => [
                "\x5b",
                0.0,
            ],
            'double - 1 octet one' => [
                "\x5c",
                1.0,
            ],
            'double - 2 octet min' => [
                "\x5d\x80",
                -128.0,
            ],
            'double - 2 octet max' => [
                "\x5d\x7f",
                127.0,
            ],
            'double - 2 octet midway 1' => [
                "\x5d\xf6",
                -10.0,
            ],
            'double - 2 octet midway 2' => [
                "\x5d\x0c",
                12.0,
            ],
            'double - 3 octet min' => [
                "\x5e\x80\x00",
                -32768.0,
            ],
            'double - 3 octet max' => [
                "\x5e\x7f\xff",
                32767.0,
            ],
            'double - 3 octet midway' => [
                "\x5e\xfc\x18",
                -1000.0,
            ],
            'double - 4 octet float' => [
                "\x5f\x00\x00\x2f\xda",
                12.25,
            ],
            'double - 4 octet float (whole)' => [
                "\x5f\x02\xae\xa5\x40",
                45000.00,
            ],
            'double - 4 octet float (0.001)' => [
                "\x5f\x00\x00\x00\x01",
                0.001,
            ],
            'double - 4 octet float (-0.001)' => [
                "\x5f\xff\xff\xff\xff",
                -0.001,
            ],
            'double - 4 octet float (65.536)' => [
                "\x5f\x00\x01\x00\x00",
                65.536,
            ],
            'double - 8 octet' => [
                "D\x40\x28\x80\xa1\xbe\x2b\x49\x5a",
                12.251234,
            ],
            'double - 8 octet (pi)' => [
                "D\x40\x09\x21\xf9\xf0\x1b\x86\x6e",
                3.14159,
            ],

            ///////////////
            // timestamp //
            ///////////////

            'timestamp - milliseconds' => [
                "\x4a\x00\x00\x00\xd0\x4b\x92\x84\xb8",
                new DateTime(1998, 5, 8, 9, 51, 31)
            ],
            'timestamp - minutes' => [
                "\x4b\x00\xe3\x83\x8f",
                new DateTime(1998, 5, 8, 9, 51,  0),
            ],

            ////////////
            // vector //
            ////////////

            'vector - fixed size - compact - empty' => [
                "\x78",
                Vector::create(),
            ],
            'vector - fixed size - compact' => [
                "\x7b\x91\x92\x93",
                Vector::create(1, 2, 3),
            ],
            'vector - fixed size - compact - nested' => [
                "\x7a\x7b\x91\x92\x93\x7b\x94\x95\x96",
                Vector::create(Vector::create(1, 2, 3), Vector::create(4, 5, 6)),
            ],
            'vector - fixed size' => [
                "\x58\x98\x91\x92\x93\x94\x95\x96\x97\x98",
                Vector::create(1, 2, 3, 4, 5, 6, 7, 8),
            ],
            'vector - fixed size - nested' => [
                "\x58\x98"
                . "\x58\x98\x91\x92\x93\x94\x95\x96\x97\x98"
                . "\x58\x98\x91\x92\x93\x94\x95\x96\x97\x98"
                . "\x58\x98\x91\x92\x93\x94\x95\x96\x97\x98"
                . "\x58\x98\x91\x92\x93\x94\x95\x96\x97\x98"
                . "\x58\x98\x91\x92\x93\x94\x95\x96\x97\x98"
                . "\x58\x98\x91\x92\x93\x94\x95\x96\x97\x98"
                . "\x58\x98\x91\x92\x93\x94\x95\x96\x97\x98"
                . "\x58\x98\x91\x92\x93\x94\x95\x96\x97\x98",
                Vector::create(
                    Vector::create(1, 2, 3, 4, 5, 6, 7, 8),
                    Vector::create(1, 2, 3, 4, 5, 6, 7, 8),
                    Vector::create(1, 2, 3, 4, 5, 6, 7, 8),
                    Vector::create(1, 2, 3, 4, 5, 6, 7, 8),
                    Vector::create(1, 2, 3, 4, 5, 6, 7, 8),
                    Vector::create(1, 2, 3, 4, 5, 6, 7, 8),
                    Vector::create(1, 2, 3, 4, 5, 6, 7, 8),
                    Vector::create(1, 2, 3, 4, 5, 6, 7, 8)
                ),
            ],

            /////////
            // map //
            /////////

            'map - empty' => [
                "\x48\x5a",
                Map::create(),
            ],
            'map' => [
                "\x48\x9a\x91\x9f\x92\x5a",
                Map::create([10, 1], [15, 2]),
            ],
            'map - nested' => [
                "\x48\x91\x48\x92\x93\x5a\x5a",
                Map::create(
                    [
                        1,
                        Map::create([2, 3])
                    ]
                ),
            ],

            ////////////
            // object //
            ////////////

            'object - compact - empty' => [
                "\x43\x08stdClass\x90\x60",
                new stdClass(),
            ],
            'object - compact' => [
                "\x43\x08stdClass\x92\x03bar\x03foo\x60\x92\x91",
                (object) ['bar' => 2, 'foo' => 1],
            ],
            'object - compact - nested' => [
                "\x43\x08stdClass\x91\x05child\x60\x43\x08stdClass\x90\x61",
                (object) ['child' => new stdClass()],
            ],
        ];
    }

    public static function encoderTestVectors()
    {
        $testVectors = [

            ///////////////
            // timestamp //
            ///////////////

            'timestamp - native datetime - milliseconds' => [
                "\x4a\x00\x00\x00\xd0\x4b\x92\x84\xb8",
                new NativeDateTime('1998-05-08T09:51:31Z')
            ],
            'timestamp - native datetime - minutes' => [
                "\x4b\x00\xe3\x83\x8f",
                new NativeDateTime('1998-05-08T09:51:00Z'),
            ],

            ///////////
            // array //
            ///////////

            'array - compact - empty' => [
                "\x78",
                [],
            ],
            'array - compact' => [
                "\x7b\x91\x92\x93",
                [1, 2, 3]
            ],
            'array' => [
                "\x58\x98\x91\x92\x93\x94\x95\x96\x97\x98",
                [1, 2, 3, 4, 5, 6, 7, 8],
            ],
            'array - map' => [
                "\x48\x9a\x91\x9f\x92\x5a",
                [10 => 1, 15 => 2],
            ],

            ////////////
            // object //
            ////////////

            'object - custom name' => [
                "\x43\x07foo.bar\x92\x03bar\x03foo\x60\x92\x91",
                new Object('foo.bar', (object) ['bar' => 2, 'foo' => 1]),
            ],
        ];

        return array_merge(
            self::canonicalTestVectors(),
            $testVectors
        );
    }

    public static function decoderTestVectors()
    {
        $testVectors = [

            ////////////
            // string //
            ////////////

            'string - empty' => [
                "\x30\x00",
                "",
            ],
            'string - hello' => [
                "\x30\x05hello",
                "hello",
            ],
            'string - unicode' => [
                "\x30\x01\xc3\x83",
                "\xc3\x83",
            ],
            'string - chunked - empty' => [
                "\x53\x00\x00",
                "",
            ],
            'string - chunked - single' => [
                "\x53\x00\x05hello",
                "hello",
            ],
            'string - chunked - multiple' => [
                "\x52\x00\x06he\xcc\x88llo\x52\x00\x07, world\x53\x00\x01!",
                "he\xcc\x88llo, world!",
            ],
            'string - chunked - ending with compact string' => [
                "\x52\x00\x06he\xcc\x88llo\x52\x00\x07, world\x01!",
                "he\xcc\x88llo, world!",
            ],
            'string - chunked - ending with regular string' => [
                "\x52\x00\x06he\xcc\x88llo\x52\x00\x07, world\x30\x01!",
                "he\xcc\x88llo, world!",
            ],

            ////////////
            // binary //
            ////////////

            'binary - empty' => [
                "\x34\x00",
                new Binary(),
            ],
            'binary - hello' => [
                "\x34\x05hello",
                new Binary("hello"),
            ],
            'binary - chunked - empty' => [
                "\x42\x00\x00",
                new Binary(),
            ],
            'binary - chunked - single' => [
                "\x42\x00\x05hello",
                new Binary("hello"),
            ],
            'binary - chunked - multiple' => [
                "\x41\x00\x05hello\x41\x00\x07, world\x42\x00\x01!",
                new Binary("hello, world!"),
            ],
            'binary - chunked - ending with compact binary' => [
                "\x41\x00\x05hello\x41\x00\x07, world\x21!",
                new Binary("hello, world!"),
            ],
            'binary - chunked - ending with regular binary' => [
                "\x41\x00\x05hello\x41\x00\x07, world\x34\x01!",
                new Binary("hello, world!"),
            ],

            ////////////////////
            // 64-bit integer //
            ////////////////////

            'long - 1 octet zero' => [
                "\xe0",
                0,
            ],
            'long - 1 octet min' => [
                "\xd8",
                -8,
            ],
            'long - 1 octet max' => [
                "\xef",
                15,
            ],
            'long - 1 octet midway' => [
                "\xdc",
                -4,
            ],
            'long - 2 octet min' => [
                "\xf0\x00",
                -2048,
            ],
            'long - 2 octet max' => [
                "\xff\xff",
                2047,
            ],
            'long - 2 octet midway' => [
                "\xf7\x00",
                -256,
            ],
            'long - 3 octet min' => [
                "\x38\x00\x00",
                -262144,
            ],
            'long - 3 octet max' => [
                "\x3f\xff\xff",
                262143,
            ],
            'long - 3 octet midway' => [
                "\x3b\xf0\x60",
                -4000,
            ],
            'long - 4 octet' => [
                "\x59\x10\x20\x30\x40",
                0x10203040,
            ],

            ////////////
            // vector //
            ////////////

            'vector - fixed size - empty' => [
                "\x58\x90",
                Vector::create(),
            ],
            'vector - empty' => [
                "\x57\x5a",
                Vector::create(),
            ],
            'vector' => [
                "\x57\x91\x92\x93\x5a",
                Vector::create(1, 2, 3),
            ],
            'vector - nested' => [
                "\x57\x57\x91\x92\x93\x5a\x57\x94\x95\x96\x5a\x5a",
                Vector::create(Vector::create(1, 2, 3), Vector::create(4, 5, 6)),
            ],
            'vector - typed + fixed size - compact - empty' => [
                "\x70\x03???",
                Vector::create(),
            ],
            'vector - typed + fixed size - compact' => [
                "\x73\x03???\x91\x92\x93",
                Vector::create(1, 2, 3),
            ],
            'vector - typed + fixed size - compact - nested' => [
                "\x72\x03???\x73\x03???\x91\x92\x93\x73\x03???\x94\x95\x96",
                Vector::create(Vector::create(1, 2, 3), Vector::create(4, 5, 6)),
            ],
            'vector - typed + fixed size - empty' => [
                "\x56\x03???\x90",
                Vector::create(),
            ],
            'vector - typed + fixed size' => [
                "\x56\x03???\x93\x91\x92\x93",
                Vector::create(1, 2, 3),
            ],
            'vector - typed + fixed size - nested' => [
                "\x56\x03???\x92\x56\x03???\x93\x91\x92\x93\x56\x03???\x93\x94\x95\x96",
                Vector::create(Vector::create(1, 2, 3), Vector::create(4, 5, 6)),
            ],
            'vector - typed - empty' => [
                "\x55\x03???\x5a",
                Vector::create(),
            ],
            'vector - typed' => [
                "\x55\x03???\x91\x92\x93\x5a",
                Vector::create(1, 2, 3),
            ],
            'vector - typed - integer type' => [
                "\x55\x90\x91\x92\x93\x5a",
                Vector::create(1, 2, 3),
            ],
            'vector - typed - nested' => [
                "\x55\x03???\x55\x03???\x91\x92\x93\x5a\x55\x03???\x94\x95\x96\x5a\x5a",
                Vector::create(Vector::create(1, 2, 3), Vector::create(4, 5, 6)),
            ],

            /////////
            // map //
            /////////

            'map - typed - empty' => [
                "\x4d\x03???\x5a",
                Map::create(),
            ],
            'map - typed' => [
                "\x4d\x03???\x9a\x91\x9f\x92\x5a",
                Map::create([10, 1], [15, 2]),
            ],
            'map - typed - integer type' => [
                "\x4d\x91\x9a\x91\x9f\x92\x5a",
                Map::create([10, 1], [15, 2]),
            ],
            'map - typed - nested' => [
                "\x4d\x03???\x91\x4d\x03???\x92\x91\x5a\x5a",
                Map::create(
                    [
                        1,
                        Map::create([2, 3])
                    ]
                ),
            ],

            ////////////
            // object //
            ////////////

            'object - empty' => [
                "\x43\x08stdClass\x90\x4f\x90",
                new stdClass(),
            ],
            'object' => [
                "\x43\x08stdClass\x92\x03bar\x03foo\x4f\x90\x92\x91",
                (object) ['bar' => 2, 'foo' => 1],
            ],
            'object - nested' => [
                "\x43\x08stdClass\x91\x05child\x4f\x90\x43\x08stdClass\x90\x4f\x91",
                (object) ['child' => new stdClass()],
            ],
        ];

        return array_merge(
            self::canonicalTestVectors(),
            $testVectors
        );
    }
}
