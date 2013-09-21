<?php
namespace Icecave\Flax\Wire;

use Icecave\Chrono\DateTime;
use Icecave\Collections\Map;
use Icecave\Collections\Vector;
use Icecave\Parity\ComparableInterface;
use PHPUnit_Framework_TestCase;

class ValueDecoderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->decoder = new ValueDecoder;
    }

    /**
     * @dataProvider decodeTestVectors
     */
    public function testDecode($input, $output)
    {
        $this->decoder->reset();
        $this->decoder->feed($input);
        $result = $this->decoder->finalize();

        if ($output instanceof ComparableInterface && $result instanceof ComparableInterface) {
            if (0 === $output->compare($result)) {
                $this->assertTrue(true);

                return;
            }
        }

        $this->assertEquals($result, $output);
    }

    public function testDecodeWithReference()
    {
        $this->decoder->reset();
        $this->decoder->feed("WHZQ\x91Z"); // a vector containing the same map twice
        $result = $this->decoder->finalize();

        $this->assertInstanceOf('Icecave\Collections\Vector', $result);
        $this->assertSame(2, $result->size());

        $this->assertInstanceOf('Icecave\Collections\Map', $result[0]);
        $this->assertSame($result[0], $result[1]);
    }

    public function decodeTestVectors()
    {
        return array(
            'null' => array(
                'N',
                null,
            ),

            'boolean - true' => array(
                'T',
                true,
            ),
            'boolean - false' => array(
                'F',
                false
            ),

            'string - compact - empty' => array(
                "\x00",
                "",
            ),
            'string - compact - hello' => array(
                "\x05hello",
                "hello",
            ),
            'string - compact - unicode' => array(
                "\x01\xc3\x83",
                "\xc3\x83",
            ),
            'string - empty' => array(
                "\x30\x00",
                "",
            ),
            'string - hello' => array(
                "\x30\x05hello",
                "hello",
            ),
            'string - unicode' => array(
                "\x30\x01\xc3\x83",
                "\xc3\x83",
            ),
            'string - chunked - single' => array(
                "\x53\x00\x05hello",
                "hello",
            ),
            'string - chunked - multiple' => array(
                "\x52\x00\x06he\xcc\x88llo\x52\x00\x07, world\x53\x00\x01!",
                "he\xcc\x88llo, world!",
            ),

            'binary - compact - empty' => array(
                "\x20",
                "",
            ),
            'binary - compact - hello' => array(
                "\x25hello",
                "hello",
            ),
            'binary - empty' => array(
                "\x34\x00",
                "",
            ),
            'binary - hello' => array(
                "\x34\x05hello",
                "hello",
            ),
            'binary - chunked - single' => array(
                "\x42\x00\x05hello",
                "hello",
            ),
            'binary - chunked - multiple' => array(
                "\x41\x00\x05hello\x41\x00\x07, world\x42\x00\x01!",
                "hello, world!",
            ),

            'double - 1 octet zero' => array(
                "\x5b",
                0.0,
            ),
            'double - 1 octet one' => array(
                "\x5c",
                1.0,
            ),
            'double - 2 octet min' => array(
                "\x5d\x80",
                -128.0,
            ),
            'double - 2 octet max' => array(
                "\x5d\x7f",
                127.0,
            ),
            'double - 2 octet midway 1' => array(
                "\x5d\xf6",
                -10.0,
            ),
            'double - 2 octet midway 2' => array(
                "\x5d\x0c",
                12.0,
            ),
            'double - 3 octet min' => array(
                "\x5e\x80\x00",
                -32768.0,
            ),
            'double - 3 octet max' => array(
                "\x5e\x7f\xff",
                32767.0,
            ),
            'double - 3 octet midway' => array(
                "\x5e\xfc\x18",
                -1000.0,
            ),
            'double - 4 octet float' => array(
                "\x5f\x41\x44\x00\x00",
                12.25,
            ),
            'double - 4 octet float (whole)' => array(
                "\x5f\x47\x2f\xc8\x00",
                45000.00,
            ),
            'double - 8 octet' => array(
                "D\x40\x28\x80\xa1\xbe\x2b\x49\x5a",
                12.251234,
            ),

            'integer - 1 octet zero' => array(
                "\x90",
                0,
            ),
            'integer - 1 octet min' => array(
                "\x80",
                -16,
            ),
            'integer - 1 octet max' => array(
                "\xbf",
                47,
            ),
            'integer - 1 octet midway' => array(
                "\x88",
                -8,
            ),
            'integer - 2 octet min' => array(
                "\xc0\x00",
                -2048,
            ),
            'integer - 2 octet max' => array(
                "\xcf\xff",
                2047,
            ),
            'integer - 2 octet midway' => array(
                "\xc7\x00",
                -256,
            ),
            'integer - 3 octet min' => array(
                "\xd0\x00\x00",
                -262144,
            ),
            'integer - 3 octet max' => array(
                "\xd7\xff\xff",
                262143,
            ),
            'integer - 3 octet midway' => array(
                "\xd3\xf0\x60",
                -4000,
            ),
            'integer - 4 octet' => array(
                "I\x10\x20\x30\x40",
                0x10203040,
            ),

            'long - 1 octet zero' => array(
                "\xe0",
                0,
            ),
            'long - 1 octet min' => array(
                "\xd8",
                -8,
            ),
            'long - 1 octet max' => array(
                "\xef",
                15,
            ),
            'long - 1 octet midway' => array(
                "\xdc",
                -4,
            ),
            'long - 2 octet min' => array(
                "\xf0\x00",
                -2048,
            ),
            'long - 2 octet max' => array(
                "\xff\xff",
                2047,
            ),
            'long - 2 octet midway' => array(
                "\xf7\x00",
                -256,
            ),
            'long - 3 octet min' => array(
                "\x38\x00\x00",
                -262144,
            ),
            'long - 3 octet max' => array(
                "\x3f\xff\xff",
                262143,
            ),
            'long - 3 octet midway' => array(
                "\x3b\xf0\x60",
                -4000,
            ),
            'long - 4 octet' => array(
                "\x59\x10\x20\x30\x40",
                0x10203040,
            ),
            'long - 8 octet' => array(
                "L\x10\x20\x30\x40\x50\x60\x70\x80",
                0x1020304050607080,
            ),

            'timestamp - milliseconds' => array(
                "\x4a\x00\x00\x00\xd0\x4b\x92\x84\xb8",
                new DateTime(1998, 5, 8, 9, 51, 31)
            ),
            'timestamp - minutes' => array(
                "\x4b\x00\xe3\x83\x8f",
                new DateTime(1998, 5, 8, 9, 51,  0),
            ),

            'vector - fixed size - compact - empty' => array(
                "\x78",
                Vector::create(),
            ),
            'vector - fixed size - compact' => array(
                "\x7b\x91\x92\x93",
                Vector::create(1, 2, 3),
            ),
            'vector - fixed size - compact - nested' => array(
                "\x7a\x7b\x91\x92\x93\x7b\x94\x95\x96",
                Vector::create(Vector::create(1, 2, 3), Vector::create(4, 5, 6)),
            ),
            'vector - fixed size - empty' => array(
                "\x58\x90",
                Vector::create(),
            ),
            'vector - fixed size' => array(
                "\x58\x93\x91\x92\x93",
                Vector::create(1, 2, 3),
            ),
            'vector - fixed size - nested' => array(
                "\x58\x92\x58\x93\x91\x92\x93\x58\x93\x94\x95\x96",
                Vector::create(Vector::create(1, 2, 3), Vector::create(4, 5, 6)),
            ),
            'vector - empty' => array(
                "\x57\x5a",
                Vector::create(),
            ),
            'vector' => array(
                "\x57\x91\x92\x93\x5a",
                Vector::create(1, 2, 3),
            ),
            'vector - nested' => array(
                "\x57\x57\x91\x92\x93\x5a\x57\x94\x95\x96\x5a\x5a",
                Vector::create(Vector::create(1, 2, 3), Vector::create(4, 5, 6)),
            ),

            'vector - typed + fixed size - compact - empty' => array(
                "\x70\x03???",
                Vector::create(),
            ),
            'vector - typed + fixed size - compact' => array(
                "\x73\x03???\x91\x92\x93",
                Vector::create(1, 2, 3),
            ),
            'vector - typed + fixed size - compact - nested' => array(
                "\x72\x03???\x73\x03???\x91\x92\x93\x73\x03???\x94\x95\x96",
                Vector::create(Vector::create(1, 2, 3), Vector::create(4, 5, 6)),
            ),
            'vector - typed + fixed size - empty' => array(
                "\x56\x03???\x90",
                Vector::create(),
            ),
            'vector - typed + fixed size' => array(
                "\x56\x03???\x93\x91\x92\x93",
                Vector::create(1, 2, 3),
            ),
            'vector - typed + fixed size - nested' => array(
                "\x56\x03???\x92\x56\x03???\x93\x91\x92\x93\x56\x03???\x93\x94\x95\x96",
                Vector::create(Vector::create(1, 2, 3), Vector::create(4, 5, 6)),
            ),
            'vector - typed - empty' => array(
                "\x55\x03???\x5a",
                Vector::create(),
            ),
            'vector - typed' => array(
                "\x55\x03???\x91\x92\x93\x5a",
                Vector::create(1, 2, 3),
            ),
            'vector - typed - nested' => array(
                "\x55\x03???\x55\x03???\x91\x92\x93\x5a\x55\x03???\x94\x95\x96\x5a\x5a",
                Vector::create(Vector::create(1, 2, 3), Vector::create(4, 5, 6)),
            ),

            'map - empty' => array(
                "\x48\x5a",
                Map::create(),
            ),
            'map' => array(
                "\x48\x9a\x91\x9f\x92\x5a",
                Map::create(array(10, 1), array(15, 2)),
            ),
            'map - nested' => array(
                "\x48\x91\x48\x92\x93\x5a\x5a",
                Map::create(
                    array(
                        1,
                        Map::create(array(2, 3))
                    )
                ),
            ),
            'map - typed - empty' => array(
                "\x4d\x03???\x5a",
                Map::create(),
            ),
            'map - typed' => array(
                "\x4d\x03???\x9a\x91\x9f\x92\x5a",
                Map::create(array(10, 1), array(15, 2)),
            ),
            'map - typed - nested' => array(
                "\x4d\x03???\x91\x4d\x03???\x92\x91\x5a\x5a",
                Map::create(
                    array(
                        1,
                        Map::create(array(2, 3))
                    )
                ),
            ),

        );
    }
}
