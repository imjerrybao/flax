<?php
namespace Icecave\Flax\Wire;

use Icecave\Chrono\DateTime;
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

        $this->assertEquals($result, $output);
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

            'string - empty' => array(
                "\x00",
                "",
            ),
            'string - hello' => array(
                "\x05hello",
                "hello",
            ),
            'string - unicode' => array(
                "\x01\xc3\x83",
                "\xc3\x83",
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
        );
    }
}
