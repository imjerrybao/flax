<?php
namespace Icecave\Flax\Serialization;

use DateTime as NativeDateTime;
use Icecave\Chrono\DateTime;
use Icecave\Collections\Map;
use PHPUnit_Framework_TestCase;
use stdClass;

class ValueEncoderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->encoder = new Encoder;
    }

    /**
     * @dataProvider getTestValues
     */
    public function testEncode($value, $expectedResult)
    {
        $buffer = $this->encoder->encode($value);

        $this->assertSameBinary($expectedResult, $buffer);
    }

    public function testEncodeMediumLengthString()
    {
        $value = $this->generateData(1023);

        $this->testEncode($value, "\x33\xff" . $value);
    }

    public function testEncodeStringSmallestChunk()
    {
        $chunk = $this->generateData(1024);

        $this->testEncode($chunk, "S\x04\x00" . $chunk);
    }

    public function testEncodeStringWholeChunk()
    {
        $chunk = $this->generateData(0xffff);

        $this->testEncode($chunk, "S\xff\xff" . $chunk);
    }

    public function testEncodeStringPartialChunk()
    {
        $chunk = $this->generateData(0x7fff);

        $this->testEncode($chunk, "S\x7f\xff" . $chunk);
    }

    public function testEncodeStringMultipleChunks()
    {
        $chunkA = $this->generateData(0xffff);
        $chunkB = $this->generateData(0x012C);

        $this->testEncode($chunkA . $chunkB, "R\xff\xff" . $chunkA . "S\x01\x2c" . $chunkB);
    }

    /**
     * @dataProvider getBinaryTestValues
     */
    public function testEncodeBinary($value, $expectedResult)
    {
        $buffer = $this->encoder->encodeBinary($value);

        $this->assertSameBinary($expectedResult, $buffer);
    }

    public function testEncodeMediumLength()
    {
        $value = $this->generateData(1023);

        $this->testEncodeBinary($value, "\x37\xff" . $value);
    }

    public function testEncodeBinarySmallestChunk()
    {
        $chunk = $this->generateData(1024);

        $this->testEncodeBinary($chunk, "B\x04\x00" . $chunk);
    }

    public function testEncodeBinaryWholeChunk()
    {
        $chunk = $this->generateData(0xffff);

        $this->testEncodeBinary($chunk, "B\xff\xff" . $chunk);
    }

    public function testEncodeBinaryPartialChunk()
    {
        $chunk = $this->generateData(0x7fff);

        $this->testEncodeBinary($chunk, "B\x7f\xff" . $chunk);
    }

    public function testEncodeBinaryMultipleChunks()
    {
        $chunkA = $this->generateData(0xffff);
        $chunkB = $this->generateData(0x012C);

        $this->testEncodeBinary($chunkA . $chunkB, "A\xff\xff" . $chunkA . "B\x01\x2c" . $chunkB);
    }

    /**
     * @dataProvider getTimestampTestValues
     */
    public function testEncodeTimestamp($value, $expectedResult)
    {
        $buffer = $this->encoder->encodeTimestamp($value);

        $this->assertSameBinary($expectedResult, $buffer);
    }

    public function testEncodeMultipleObjects()
    {
        $value1 = new stdClass;
        $value1->foo = 1;
        $value1->bar = 2;

        $value2 = new stdClass;
        $value2->baz = 3;
        $value2->faz = 4;

        $this->assertSameBinary("C\x08stdClass\x92\x03bar\x03foo\x60\x92\x91", $this->encoder->encode($value1));
        $this->assertSameBinary("C\x08stdClass\x92\x03baz\x03faz\x61\x93\x94", $this->encoder->encode($value2));
    }

    public function testEncodeMultipleObjectsSingleDefinition()
    {
        $value1 = new stdClass;
        $value1->foo = 1;
        $value1->bar = 2;

        $value2 = new stdClass;
        $value2->foo = 3;
        $value2->bar = 4;

        $this->assertSameBinary("C\x08stdClass\x92\x03bar\x03foo\x60\x92\x91", $this->encoder->encode($value1));
        $this->assertSameBinary("\x60\x94\x93", $this->encoder->encode($value2));
    }

    public function testEncodeMultipleObjectsReference()
    {
        $value1 = new stdClass;
        $value1->foo = 1;
        $value1->bar = 2;

        $value2 = new stdClass;
        $value2->foo = 3;
        $value2->bar = 4;

        $this->assertSameBinary("C\x08stdClass\x92\x03bar\x03foo\x60\x92\x91", $this->encoder->encode($value1));
        $this->assertSameBinary("\x60\x94\x93", $this->encoder->encode($value2));
        $this->assertSameBinary("Q\x90", $this->encoder->encode($value1));
        $this->assertSameBinary("Q\x91", $this->encoder->encode($value2));
    }

    public function testReset()
    {
        $this->encoder->encode(new stdClass);

        $this->encoder->reset();
        $this->assertSameBinary("C\x08stdClass\x90\x60", $this->encoder->encode(new stdClass));

    }

    public function testEncodeUnsupportedType()
    {
        $resource = fopen(__FILE__, 'r');
        $this->setExpectedException('InvalidArgumentException', 'Can not encode value of type "resource".');
        $this->encoder->encode($resource);
    }

    public function testEncodeObjectWithLongFormClassDefinitionId()
    {
        $buffer = '';

        // Force 16 class defs to be made to be made ...
        for ($i = 0; $i < 16; ++$i) {
            $value = new stdClass;
            $value->{'prop' . $i} = $i;

            $buffer .= $this->encoder->encode($value);
        }

        // This class def should be the 16th (0xa0) ...
        $this->assertSameBinary("C\x08stdClass\x90O\xa0", $this->encoder->encode(new stdClass));
    }

    public function testEncodeUnsupportedObject()
    {
        $this->setExpectedException('InvalidArgumentException', 'Can not encode object of type "Icecave\Collections\Map".');
        $this->encoder->encode(new Map);
    }

    public function getTestValues()
    {
        $data = array(
            'null'                           => array(null, 'N'),

            'boolean - true'                 => array(true,  'T'),
            'boolean - false'                => array(false, 'F'),

            'string'                         => array("",         "\x00"),
            'string - hello'                 => array("hello",    "\x05hello"),
            'string - unicode'               => array("\xc3\x83", "\x01\xc3\x83"),

            'double - 1 octet zero'          => array(       0.0, "\x5b"),
            'double - 1 octet one'           => array(       1.0, "\x5c"),
            'double - 2 octet min'           => array(    -128.0, "\x5d\x80"),
            'double - 2 octet max'           => array(     127.0, "\x5d\x7f"),
            'double - 2 octet midway 1'      => array(     -10.0, "\x5d\xf6"),
            'double - 2 octet midway 2'      => array(      12.0, "\x5d\x0c"),
            'double - 3 octet min'           => array(  -32768.0, "\x5e\x80\x00"),
            'double - 3 octet max'           => array(   32767.0, "\x5e\x7f\xff"),
            'double - 3 octet midway'        => array(   -1000.0, "\x5e\xfc\x18"),
            'double - 4 octet float'         => array(     12.25, "\x5f\x41\x44\x00\x00"),
            'double - 4 octet float (whole)' => array(  45000.00, "\x5f\x47\x2f\xc8\x00"),
            'double - 8 octet'               => array( 12.251234, "D\x40\x28\x80\xa1\xbe\x2b\x49\x5a"),

            'integer - 1 octet zero'         => array(         0, "\x90"),
            'integer - 1 octet min'          => array(       -16, "\x80"),
            'integer - 1 octet max'          => array(        47, "\xbf"),
            'integer - 1 octet midway'       => array(        -8, "\x88"),
            'integer - 2 octet min'          => array(     -2048, "\xc0\x00"),
            'integer - 2 octet max'          => array(      2047, "\xcf\xff"),
            'integer - 2 octet midway'       => array(      -256, "\xc7\x00"),
            'integer - 3 octet min'          => array(   -262144, "\xd0\x00\x00"),
            'integer - 3 octet max'          => array(    262143, "\xd7\xff\xff"),
            'integer - 3 octet midway'       => array(     -4000, "\xd3\xf0\x60"),
            'integer - 4 octet'              => array(0x10203040, "I\x10\x20\x30\x40"),

            'array - empty'                  => array(array(),                       "\x57Z"),
            'array - small'                  => array(array(1, 2, 3),                "\x7b\x91\x92\x93"),
            'array - longer'                 => array(array(1, 2, 3, 4, 5, 6, 7, 8), "\x58\x98\x91\x92\x93\x94\x95\x96\x97\x98"),
            'array - map'                    => array(array(10 => 1, 15 => 2),       "H\x9a\x91\x9f\x92Z"),

            'object - empty'                 => array(new stdClass,                                   "C\x08stdClass\x90\x60"),
            'object - simple'                => array((object) array('foo' => 1, 'bar' => 2),         "C\x08stdClass\x92\x03bar\x03foo\x60\x92\x91"),
            'object - datetime'              => array(new NativeDateTime('09:51:31 May 8, 1998 UTC'), "\x4a\x00\x00\x00\xd0\x4b\x92\x84\xb8"),
            'object - datetime minutes'      => array(new NativeDateTime('09:51:00 May 8, 1998 UTC'), "\x4b\x00\xe3\x83\x8f"),
            'object - chrono'                => array(new DateTime(1998, 5, 8, 9, 51, 31),            "\x4a\x00\x00\x00\xd0\x4b\x92\x84\xb8"),
            'object - chrono minutes'        => array(new DateTime(1998, 5, 8, 9, 51,  0),            "\x4b\x00\xe3\x83\x8f"),
        );

        if (PHP_INT_SIZE > 4) {
            $data['integer - 8 octet'] = array(0x1020304050607080, "L\x10\x20\x30\x40\x50\x60\x70\x80");
        }

        return $data;
    }

    public function getBinaryTestValues()
    {
        return array(
            'binary - empty'            => array("",             "\x20"),
            'binary - 3 octet'          => array("\x01\x02\x03", "\x23\x01\x02\x03"),
        );
    }

    public function getTimestampTestValues()
    {
        date_default_timezone_set('UTC');

        return array(
            'timestamp - milliseconds' => array(strtotime('09:51:31 May 8, 1998 UTC') * 1000, "\x4a\x00\x00\x00\xd0\x4b\x92\x84\xb8"),
            'timestamp - minutes'      => array(strtotime('09:51:00 May 8, 1998 UTC') * 1000, "\x4b\x00\xe3\x83\x8f"),
        );
    }

    private function generateData($length)
    {
        $text = preg_replace(
            '/\s+/',
            ' ',
            'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
            tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
            quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
            consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
            cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
            proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
        );

        $str = '';

        while (strlen($str) < $length) {
            $str .= $text;
        }

        return substr($str, 0, $length - 1) . '!';
    }

    public function assertSameBinary($expectedResult, $buffer)
    {
        if ($buffer !== $expectedResult) {
            $this->assertSame(
                $this->formatBinaryData($expectedResult),
                $this->formatBinaryData($buffer)
            );
        } else {
            $this->assertTrue(true);
        }
    }

    private function formatBinaryData($buffer)
    {
        $result = '';
        $length = strlen($buffer);

        for ($index = 0; $index < $length; ++$index) {
            $ordinal = ord($buffer[$index]);
            $result .= sprintf(
                '%8d 0x%02x %s' . PHP_EOL,
                $index,
                $ordinal,
                $ordinal >= 0x20 && $ordinal <= 0x7e ? $buffer[$index] : ''
            );
        }

        return $result;
    }
}
