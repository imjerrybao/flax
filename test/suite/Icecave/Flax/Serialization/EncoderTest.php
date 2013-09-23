<?php
namespace Icecave\Flax\Serialization;

use Exception;
use PHPUnit_Framework_TestCase;
use stdClass;

class ValueEncoderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->encoder = new Encoder;
    }

    /**
     * @dataProvider Icecave\Flax\Serialization\TestVectors::encoderTestVectors
     */
    public function testEncode($output, $input, $skipTest = false)
    {
        if ($skipTest) {
            $this->markTestSkipped();
        }

        $result = $this->encoder->encode($input);

        $this->assertSameBinary($output, $result);
    }

    public function testEncodeStringMediumLength()
    {
        $value = $this->generateData(1023);

        $this->testEncode(
            "\x33\xff" . $value,
            $value
        );
    }

    public function testEncodeStringSmallestChunk()
    {
        $chunk = $this->generateData(1024);

        $this->testEncode(
            "S\x04\x00" . $chunk,
            $chunk
        );
    }

    public function testEncodeStringWholeChunk()
    {
        $chunk = $this->generateData(0xffff);

        $this->testEncode(
            "S\xff\xff" . $chunk,
            $chunk
        );
    }

    public function testEncodeStringPartialChunk()
    {
        $chunk = $this->generateData(0x7fff);

        $this->testEncode(
            "S\x7f\xff" . $chunk,
            $chunk
        );
    }

    public function testEncodeStringMultipleChunks()
    {
        $chunkA = $this->generateData(0xffff);
        $chunkB = $this->generateData(0x012C);

        $this->testEncode(
            "R\xff\xff" . $chunkA . "S\x01\x2c" . $chunkB,
            $chunkA . $chunkB
        );
    }

    /**
     * @dataProvider binaryTestVectors
     */
    public function testEncodeBinary($output, $input)
    {
        $result = $this->encoder->encodeBinary($input);

        $this->assertSameBinary($output, $result);
    }

    public function binaryTestVectors()
    {
        return array(
            'binary - compact - empty' => array(
                "\x20",
                "",
            ),
            'binary - compact - hello' => array(
                "\x25hello",
                "hello",
            ),
        );
    }

    public function testEncodeBinaryMediumLength()
    {
        $value = $this->generateData(1023);

        $this->testEncodeBinary(
            "\x37\xff" . $value,
            $value
        );
    }

    public function testEncodeBinarySmallestChunk()
    {
        $chunk = $this->generateData(1024);

        $this->testEncodeBinary(
            "B\x04\x00" . $chunk,
            $chunk
        );
    }

    public function testEncodeBinaryWholeChunk()
    {
        $chunk = $this->generateData(0xffff);

        $this->testEncodeBinary(
            "B\xff\xff" . $chunk,
            $chunk
        );
    }

    public function testEncodeBinaryPartialChunk()
    {
        $chunk = $this->generateData(0x7fff);

        $this->testEncodeBinary(
            "B\x7f\xff" . $chunk,
            $chunk
        );
    }

    public function testEncodeBinaryMultipleChunks()
    {
        $chunkA = $this->generateData(0xffff);
        $chunkB = $this->generateData(0x012C);

        $this->testEncodeBinary(
            "A\xff\xff" . $chunkA . "B\x01\x2c" . $chunkB,
            $chunkA . $chunkB
        );
    }

    /**
     * @dataProvider timestampTestVectors
     */
    public function testEncodeTimestamp($output, $input)
    {
        $result = $this->encoder->encodeTimestamp($input);

        $this->assertSameBinary($result, $output);
    }

    public function timestampTestVectors()
    {
        return array(
            'timestamp - milliseconds' => array(
                "\x4a\x00\x00\x00\xd0\x4b\x92\x84\xb8",
                894621091 * 1000
            ),
            'timestamp - minutes' => array(
                "\x4b\x00\xe3\x83\x8f",
                894621060 * 1000
            ),
        );
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

    public function testEncodeArrayIncreasesReferenceId()
    {
        $value = new stdClass;

        $this->encoder->encode(array());

        $this->assertSameBinary("C\x08stdClass\x90\x60", $this->encoder->encode($value));
        $this->assertSameBinary("Q\x91", $this->encoder->encode($value));
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
        $this->setExpectedException('InvalidArgumentException', 'Can not encode object of type "Exception".');
        $this->encoder->encode(new Exception);
    }

    public function testReset()
    {
        $value = new stdClass;

        $this->encoder->encode($value);

        $this->encoder->reset();
        $this->assertSameBinary("C\x08stdClass\x90\x60", $this->encoder->encode($value));
        $this->assertSameBinary("Q\x90", $this->encoder->encode($value));
    }

    public function testEncodeUnsupportedType()
    {
        $resource = fopen(__FILE__, 'r');
        $this->setExpectedException('InvalidArgumentException', 'Can not encode value of type "resource".');
        $this->encoder->encode($resource);
    }

    private function generateData($length)
    {
        $text  = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod ';
        $text .= 'tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, ';
        $text .= 'quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo ';
        $text .= 'consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse ';
        $text .= 'cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non ';
        $text .= 'proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

        $str = str_repeat(
            $text,
            ceil($length / strlen($text))
        );

        return substr($str, 0, $length - 1) . '!';
    }

    public function assertSameBinary($expectedResult, $buffer)
    {
        if ($buffer !== $expectedResult) {
            $this->assertEquals(
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

        while ($length = strlen($buffer)) {
            $char = $buffer[0];
            $ordinal = ord($char);

            $result .= sprintf(
                '0x%02x %s' . PHP_EOL,
                $ordinal,
                $ordinal >= 0x20 && $ordinal <= 0x7e ? $char : ''
            );

            $buffer = substr($buffer, 1);
        }

        return $result;
    }
}
