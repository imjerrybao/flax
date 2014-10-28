<?php
namespace Icecave\Flax;

use Eloquent\Liberator\Liberator;
use Exception;
use Icecave\Collections\Map;
use Icecave\Isolator\Isolator;
use Phake;
use PHPUnit_Framework_TestCase;
use stdClass;

class HessianClientTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->httpClient = Phake::mock('Guzzle\Http\Client');
        $this->logger = Phake::mock('Psr\Log\LoggerInterface');
        $this->streamFactory = Phake::mock('Guzzle\Stream\PhpStreamRequestFactory');
        $this->stream = Phake::mock('Guzzle\Stream\StreamInterface');
        $this->request = Phake::mock('Guzzle\Http\Message\Request');
        $this->encoder = Phake::mock('Icecave\Flax\Message\Encoder');
        $this->decoder = Phake::mock('Icecave\Flax\Message\Decoder');
        $this->isolator = Phake::mock('Icecave\Isolator\Isolator');

        $this->requestBuffer  = "H\x02\x00C...";
        $this->responseBuffer = "H\x02\x00R\x05hello";

        $this->client = new HessianClient(
            $this->httpClient,
            $this->logger,
            $this->streamFactory,
            $this->encoder,
            $this->decoder
        );

        $this->client->setIsolator($this->isolator);

        Phake::when($this->isolator)
            ->microtime(true)
            ->thenReturn(1.75)
            ->thenReturn(2.25);

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
            ->thenReturn([true, 'hello']);
    }

    public function testConstructorDefaults()
    {
        $client = new HessianClient($this->httpClient);
        $liberatedClient = Liberator::liberate($client);

        $this->assertInstanceOf('Psr\Log\NullLogger', $liberatedClient->logger);
        $this->assertInstanceOf('Guzzle\Stream\PhpStreamRequestFactory', $liberatedClient->streamFactory);
        $this->assertInstanceOf('Icecave\Flax\Message\Encoder', $liberatedClient->encoder);
        $this->assertInstanceOf('Icecave\Flax\Message\Decoder', $liberatedClient->decoder);
    }

    public function testSetLogger()
    {
        $logger = Phake::mock('Psr\Log\LoggerInterface');

        $this->client->setLogger($logger);

        $this->assertSame($logger, Liberator::liberate($this->client)->logger);
    }

    public function testCallMagicMethod()
    {
        $result = $this->client->foo(1, 2, 3);

        Phake::inOrder(
            Phake::verify($this->encoder)->encodeVersion(),
            Phake::verify($this->encoder)->encodeCall('foo', [1, 2, 3]),
            Phake::verify($this->httpClient)->post(null, null, $this->requestBuffer),
            Phake::verify($this->decoder)->feed("H\x02\x00R"),
            Phake::verify($this->decoder)->feed("\x05hello"),
            Phake::verify($this->decoder)->finalize(),
            Phake::verify($this->logger)->debug(
                'Invoked "foo(integer, integer, integer)" in 0.5 second(s), with "string" reply.',
                [
                    'arguments' => [1, 2, 3],
                    'reply' => 'hello',
                ]
            )
        );

        $this->assertSame('hello', $result);
    }

    public function testInvoke()
    {
        $result = $this->client->invoke('foo', 1, 2, 3);

        Phake::inOrder(
            Phake::verify($this->encoder)->encodeVersion(),
            Phake::verify($this->encoder)->encodeCall('foo', [1, 2, 3]),
            Phake::verify($this->httpClient)->post(null, null, $this->requestBuffer),
            Phake::verify($this->decoder)->feed("H\x02\x00R"),
            Phake::verify($this->decoder)->feed("\x05hello"),
            Phake::verify($this->decoder)->finalize(),
            Phake::verify($this->logger)->debug(
                'Invoked "foo(integer, integer, integer)" in 0.5 second(s), with "string" reply.',
                [
                    'arguments' => [1, 2, 3],
                    'reply' => 'hello',
                ]
            )
        );

        $this->assertSame('hello', $result);
    }

    public function testInvokeArray()
    {
        $result = $this->client->invokeArray('foo', [1, 2, 3]);

        Phake::inOrder(
            Phake::verify($this->encoder)->encodeVersion(),
            Phake::verify($this->encoder)->encodeCall('foo', [1, 2, 3]),
            Phake::verify($this->httpClient)->post(null, null, $this->requestBuffer),
            Phake::verify($this->decoder)->feed("H\x02\x00R"),
            Phake::verify($this->decoder)->feed("\x05hello"),
            Phake::verify($this->decoder)->finalize(),
            Phake::verify($this->logger)->debug(
                'Invoked "foo(integer, integer, integer)" in 0.5 second(s), with "string" reply.',
                [
                    'arguments' => [1, 2, 3],
                    'reply' => 'hello',
                ]
            )
        );

        $this->assertSame('hello', $result);
    }

    public function testInvokeArrayClassNameLogging()
    {
        $result = $this->client->invokeArray('foo', [new stdClass()]);

        Phake::verify($this->logger)->debug(
            'Invoked "foo(stdClass)" in 0.5 second(s), with "string" reply.',
            [
                'arguments' => [new stdClass()],
                'reply' => 'hello',
            ]
        );

        $this->assertSame('hello', $result);
    }

    /**
     * @dataProvider exceptionTypes
     */
    public function testInvokeFailure($exceptionType)
    {
        $properties = Map::create(
            ['code', $exceptionType],
            ['message', 'The message.']
        );

        Phake::when($this->decoder)
            ->finalize()
            ->thenReturn([false, $properties]);

        $this->setExpectedException('Icecave\Flax\Exception\\' . $exceptionType, 'The message.');

        try {
            $this->client->invoke('foo', 1, 2, 3);
        } catch (Exception $e) {
            Phake::verify($this->logger)->debug(
                'Invoked "foo(integer, integer, integer)" in 0.5 second(s), with "' . $exceptionType . '" fault: The message.',
                [
                    'arguments' => [1, 2, 3],
                    'fault' => $properties
                ]
            );

            throw $e;
        }
    }

    public function testInvokeFailureUnknownExceptionType()
    {
        $properties = Map::create(
            ['code', '<foo>'],
            ['message', 'The message.']
        );

        Phake::when($this->decoder)
            ->finalize()
            ->thenReturn([false, $properties]);

        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Unknown Hessian fault code: <foo>.');
        $this->client->invoke('foo', 1, 2, 3);
    }

    public function testInvokeFailureMissingExceptionCode()
    {
        $properties = Map::create(
            ['message', 'The message.']
        );

        Phake::when($this->decoder)
            ->finalize()
            ->thenReturn([false, $properties]);

        $this->setExpectedException('Icecave\Flax\Exception\DecodeException', 'Encountered Hessian fault with no fault code.');
        $this->client->invoke('foo', 1, 2, 3);
    }

    public function exceptionTypes()
    {
        return [
            ['NoSuchMethodException'],
            ['NoSuchObjectException'],
            ['ProtocolException'],
            ['RequireHeaderException'],
            ['ServiceException'],
        ];
    }
}
