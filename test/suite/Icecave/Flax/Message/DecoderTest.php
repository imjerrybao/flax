<?php
namespace Icecave\Flax\Message;

use Icecave\Collections\Map;
use PHPUnit_Framework_TestCase;

class DecoderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->decoder = new Decoder();
    }

    /**
     * @dataProvider decodeTestVectors
     */
    public function testDecode($input, $output)
    {
        $this->decoder->feed($input);
        $result = $this->decoder->finalize();

        $this->assertEquals($result, $output);
    }

    public function decodeTestVectors()
    {
        return array(
            array(
                "H\x02\x00R\x91",
                array(true, 1)
            ),
            array(
                "H\x02\x00FH\x04code\x04testZ",
                array(false, Map::create(array('code', 'test')))
            ),
        );
    }

    public function testDecodeFailureNotReset()
    {
        $this->decoder->feed("H\x02\x00R\x91");

        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Decoder has not been reset.');
        $this->decoder->feed("H");
    }

    public function testFinalizedFailure()
    {
        $this->decoder->feed("H");

        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Unexpected end of stream (state: VERSION).');
        $this->decoder->finalize();
    }

    public function testTryFinalize()
    {
        $this->decoder->feed("H\x02\x00R\x91");

        $value = null;
        $this->assertTrue($this->decoder->tryFinalize($value));
        $this->assertSame(array(true, 1), $value);
    }

    public function testTryFinalizeFailure()
    {
        $this->decoder->feed("H");

        $value = null;
        $this->assertFalse($this->decoder->tryFinalize($value));
        $this->assertNull($value);
    }

    public function testFeedFailureInvalidHeader()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Invalid byte at start of message: 0x80 (state: BEGIN).');
        $this->decoder->feed("\x80");
    }

    public function testFeedFailureInvalidVersion()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Unsupported Hessian version: 0x0301.');
        $this->decoder->feed("H\x03\01");
    }

    public function testFeedFailureUnsupportedMessageType()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Unsupported message type: 0x43.');
        $this->decoder->feed("H\x02\x00C");
    }
}
