<?php
namespace Icecave\Flax\Wire;

use PHPUnit_Framework_TestCase;

class ProtocolEncoderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->encoder = new ProtocolEncoder;
    }

    public function testEncodeVersion()
    {
        $this->assertSameBinary("H\x02\x00", $this->encoder->encodeVersion());
    }

    public function testEncodeCall()
    {
        $expectedResult = "C\x03foo\x93\x91\x92\x93";

        $buffer = $this->encoder->encodeCall("foo", array(1, 2, 3));

        $this->assertSameBinary($expectedResult, $buffer);
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
