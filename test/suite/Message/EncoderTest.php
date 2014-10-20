<?php
namespace Icecave\Flax\Message;

use PHPUnit_Framework_TestCase;

class EncoderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->encoder = new Encoder();
    }

    public function testEncodeVersion()
    {
        $this->assertSame("H\x02\x00", $this->encoder->encodeVersion());
    }

    public function testEncodeCall()
    {
        $expectedResult = "C\x03foo\x93\x91\x92\x93";

        $buffer = $this->encoder->encodeCall("foo", [1, 2, 3]);

        $this->assertSame($expectedResult, $buffer);
    }

    public function testReset()
    {
        $this->encoder->encodeCall("foo", [1, 2, 3]);

        $this->encoder->reset();

        $expectedResult = "C\x03foo\x93\x91\x92\x93";

        $buffer = $this->encoder->encodeCall("foo", [1, 2, 3]);

        $this->assertSame($expectedResult, $buffer);
    }
}
