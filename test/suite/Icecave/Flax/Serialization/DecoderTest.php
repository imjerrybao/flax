<?php
namespace Icecave\Flax\Serialization;

use Icecave\Collections\Map;
use Icecave\Collections\Vector;
use Icecave\Parity\ComparableInterface;
use PHPUnit_Framework_TestCase;

class DecoderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->decoder = new Decoder;
    }

    /**
     * @dataProvider Icecave\Flax\Serialization\TestVectors::decoderTestVectors
     */
    public function testDecode($input, $output, $skipTest = false)
    {
        if ($skipTest) {
            $this->markTestSkipped();
        }

        $this->decoder->feed($input);
        $result = $this->decoder->finalize();

        if ($output instanceof ComparableInterface && $result instanceof ComparableInterface) {
            if (0 === $output->compare($result)) {
                $this->assertTrue(true);

                return;
            }
        }

        $this->assertEquals($output, $result);
    }

    public function testDecodeWithReference()
    {
        $this->decoder->feed("WHZQ\x91Z"); // a vector containing the same map twice
        $result = $this->decoder->finalize();

        $this->assertInstanceOf('Icecave\Collections\Vector', $result);
        $this->assertSame(2, $result->size());

        $this->assertInstanceOf('Icecave\Collections\Map', $result[0]);
        $this->assertSame($result[0], $result[1]);
    }

    public function testDecodeFailureNotReset()
    {
        $this->decoder->feed("\x91");

        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Decoder has not been reset.');
        $this->decoder->feed("\x91");
    }

    public function testFinalizedFailure()
    {
        $this->decoder->feed("C");

        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Unexpected end of stream (state: CLASS_DEFINITION_NAME).');
        $this->decoder->finalize();
    }

    public function testTryFinalize()
    {
        $this->decoder->feed("\x91");

        $value = null;
        $this->assertTrue($this->decoder->tryFinalize($value));
        $this->assertSame(1, $value);
    }

    public function testTryFinalizeFailure()
    {
        $this->decoder->feed("C");

        $value = null;
        $this->assertFalse($this->decoder->tryFinalize($value));
        $this->assertNull($value);
    }

    public function testTryFinalizeFailureAfterClassDefinition()
    {
        $this->decoder->feed("C\x08stdClass\x90");

        $value = null;
        $this->assertFalse($this->decoder->tryFinalize($value));
        $this->assertNull($value);
    }

    public function testFeedFailureReservedByte()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Invalid byte at start of value: 0x45 (state: BEGIN).');
        $this->decoder->feed("\x45");
    }

    public function testFeedFailureWithInvalidCollectionType()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Invalid byte at start of collection type: 0x54 (state: COLLECTION_TYPE).');
        $this->decoder->feed("UT");
    }

    public function testFeedFailureWithInvalidStringContinuation()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Invalid byte at start of string chunk: 0x20 (state: STRING_CHUNK_CONTINUATION).');
        $this->decoder->feed("\x52\x00\x05hello\x20");
    }

    public function testFeedFailureWithInvalidBinaryContinuation()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Invalid byte at start of binary chunk: 0x1 (state: BINARY_CHUNK_CONTINUATION).');
        $this->decoder->feed("\x41\x00\x05hello\x01");
    }

    public function testFeedFailureWithInvalidVectorSize()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Invalid byte at start of int: 0x54 (state: VECTOR_SIZE).');
        $this->decoder->feed("XT");
    }

    public function testFeedFailureWithInvalidReference()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Invalid byte at start of int: 0x54 (state: REFERENCE).');
        $this->decoder->feed("QT");
    }

    public function testFeedFailureWithInvalidClassDefinitionName()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Invalid byte at start of string: 0x54 (state: CLASS_DEFINITION_NAME).');
        $this->decoder->feed("CT");
    }

    public function testFeedFailureWithInvalidClassDefinitionSize()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Invalid byte at start of int: 0x54 (state: CLASS_DEFINITION_SIZE).');
        $this->decoder->feed("C\x08stdClassT");
    }

    public function testFeedFailureWithInvalidClassDefinitionField()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Invalid byte at start of string: 0x54 (state: CLASS_DEFINITION_FIELD).');
        $this->decoder->feed("C\x08stdClass\x91T");
    }

    public function testFeedFailureWithInvalidObjectInstanceType()
    {
        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Invalid byte at start of int: 0x54 (state: OBJECT_INSTANCE_TYPE).');
        $this->decoder->feed("OT");
    }
}
