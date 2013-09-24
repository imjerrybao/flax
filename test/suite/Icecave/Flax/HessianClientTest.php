<?php
namespace Icecave\Flax;

use Eloquent\Liberator\Liberator;
use Icecave\Collections\Map;
use Phake;
use PHPUnit_Framework_TestCase;

class HessianClientTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->httpClient = Phake::mock('Guzzle\Http\Client');
        $this->streamFactory = Phake::mock('Guzzle\Stream\PhpStreamRequestFactory');
        $this->stream = Phake::mock('Guzzle\Stream\StreamInterface');
        $this->request = Phake::mock('Guzzle\Http\Message\Request');
        $this->encoder = Phake::mock('Icecave\Flax\Message\Encoder');
        $this->decoder = Phake::mock('Icecave\Flax\Message\Decoder');

        $this->requestBuffer  = "H\x02\x00C...";
        $this->responseBuffer = "H\x02\x00R\x05hello";

        $this->client = new HessianClient(
            $this->httpClient,
            $this->streamFactory,
            $this->encoder,
            $this->decoder
        );

        Phake::when($this->httpClient)
            ->post(Phake::anyParameters())
            ->thenReturn($this->request);

        Phake::when($this->streamFactory)
            ->fromRequest(Phake::anyParameters())
            ->thenReturn($this->stream);

        Phake::when($this->stream)
            ->feof()
            ->thenReturn(false)
            ->thenReturn(false)
            ->thenReturn(true);

        Phake::when($this->stream)
            ->read(Phake::anyParameters())
            ->thenReturn("H\x02\x00R")
            ->thenReturn("\x05hello");

        Phake::when($this->encoder)
            ->encodeVersion()
            ->thenReturn("H\x02\x00");

        Phake::when($this->encoder)
            ->encodeCall(Phake::anyParameters())
            ->thenReturn('C...');

        Phake::when($this->decoder)
            ->finalize()
            ->thenReturn(array(true, 'hello'));
    }

    public function testConstructorDefaults()
    {
        $client = new HessianClient($this->httpClient);
        $liberatedClient = Liberator::liberate($client);

        $this->assertInstanceOf('Guzzle\Stream\PhpStreamRequestFactory', $liberatedClient->streamFactory);
        $this->assertInstanceOf('Icecave\Flax\Message\Encoder', $liberatedClient->encoder);
        $this->assertInstanceOf('Icecave\Flax\Message\Decoder', $liberatedClient->decoder);
    }

    public function testCallMagicMethod()
    {
        $result = $this->client->foo(1, 2, 3);

        Phake::inOrder(
            Phake::verify($this->encoder)->encodeVersion(),
            Phake::verify($this->encoder)->encodeCall('foo', array(1, 2, 3)),
            Phake::verify($this->httpClient)->post(null, null, $this->requestBuffer),
            Phake::verify($this->decoder)->feed("H\x02\x00R"),
            Phake::verify($this->decoder)->feed("\x05hello"),
            Phake::verify($this->decoder)->finalize()
        );

        $this->assertSame('hello', $result);
    }

    public function testInvoke()
    {
        $result = $this->client->invoke('foo', 1, 2, 3);

        Phake::inOrder(
            Phake::verify($this->encoder)->encodeVersion(),
            Phake::verify($this->encoder)->encodeCall('foo', array(1, 2, 3)),
            Phake::verify($this->httpClient)->post(null, null, $this->requestBuffer),
            Phake::verify($this->decoder)->feed("H\x02\x00R"),
            Phake::verify($this->decoder)->feed("\x05hello"),
            Phake::verify($this->decoder)->finalize()
        );

        $this->assertSame('hello', $result);
    }

    public function testInvokeArray()
    {
        $result = $this->client->invokeArray('foo', array(1, 2, 3));

        Phake::inOrder(
            Phake::verify($this->encoder)->encodeVersion(),
            Phake::verify($this->encoder)->encodeCall('foo', array(1, 2, 3)),
            Phake::verify($this->httpClient)->post(null, null, $this->requestBuffer),
            Phake::verify($this->decoder)->feed("H\x02\x00R"),
            Phake::verify($this->decoder)->feed("\x05hello"),
            Phake::verify($this->decoder)->finalize()
        );

        $this->assertSame('hello', $result);
    }

    /**
     * @dataProvider exceptionTypes
     */
    public function testInvokeFailure($exceptionType)
    {
        $properties = Map::create(
            array('code', $exceptionType),
            array('message', 'The message.')
        );

        Phake::when($this->decoder)
            ->finalize()
            ->thenReturn(array(false, $properties));

        $this->setExpectedException('Icecave\Flax\Exception\\' . $exceptionType, 'The message.');
        $this->client->invoke('foo', 1, 2, 3);
    }

    public function testInvokeFailureUnknownExceptionType()
    {
        $properties = Map::create(
            array('code', 'unknown exception type'),
            array('message', 'The message.')
        );

        Phake::when($this->decoder)
            ->finalize()
            ->thenReturn(array(false, $properties));

        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Unknown exception code: unknown exception type.');
        $this->client->invoke('foo', 1, 2, 3);
    }

    public function exceptionTypes()
    {
        return array(
            array('NoSuchMethodException'),
            array('NoSuchObjectException'),
            array('ProtocolException'),
            array('RequireHeaderException'),
            array('ServiceException'),
        );
    }
}
