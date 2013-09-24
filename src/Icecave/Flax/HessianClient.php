<?php
namespace Icecave\Flax;

use Guzzle\Http\ClientInterface;
use Guzzle\Stream\PhpStreamRequestFactory;
use Icecave\Flax\Exception\RPCFaultException;
use Icecave\Flax\Message\Decoder;
use Icecave\Flax\Message\Encoder;
use Icecave\Flax\TypeCheck\TypeCheck;

class HessianClient implements HessianClientInterface
{
    /**
     * @param ClientInterface $httpClient
     * @param PhpStreamRequestFactory|null $streamFactory
     * @param Encoder|null $encoder
     * @param Decoder|null $decoder
     */
    public function __construct(
        ClientInterface $httpClient,
        PhpStreamRequestFactory $streamFactory = null,
        Encoder $encoder = null,
        Decoder $decoder = null
    ) {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        if (null === $streamFactory) {
            $streamFactory = new PhpStreamRequestFactory;
        }

        if (null === $encoder) {
            $encoder = new Encoder;
        }

        if (null === $decoder) {
            $decoder = new Decoder;
        }

        $this->httpClient = $httpClient;
        $this->streamFactory = $streamFactory;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
    }

    /**
     * Invoke a Hessian operation.
     *
     * @param string       $name      The name of the operation to invoke.
     * @param array<mixed> $arguments Arguments to the operation.
     */
    public function __call($name, array $arguments)
    {
        $this->typeCheck->validateCall(func_get_args());

        return $this->invoke($name, $arguments);
    }

    /**
     * Invoke a Hessian operation.
     *
     * @param string       $name      The name of the operation to invoke.
     * @param array<mixed> $arguments Arguments to the operation.
     */
    public function invoke($name, array $arguments = array())
    {
        $this->typeCheck->invoke(func_get_args());

        $this->encoder->reset();
        $this->decoder->reset();

        $buffer  = $this->encoder->encodeVersion();
        $buffer .= $this->encoder->encodeCall($name, $arguments);

        $request = $this->httpClient->post(
            null,
            array('Content-Type' => 'x-application/hessian'),
            $buffer
        );

        $stream  = $this->streamFactory->fromRequest($request);

        $TEST_BUFFER = '';
        while (!$stream->feof()) {
            $TEST_BUFFER .= $stream->read(1024);
        }
        var_dump($TEST_BUFFER);
        $this->decoder->feed($TEST_BUFFER);

        // while (!$stream->feof()) {
        //     $this->decoder->feed(
        //         $stream->read(1024)
        //     );
        // }

        list($success, $value) = $this->decoder->finalize();

        if ($success) {
            return $value;
        }

        throw new RPCFaultException($value);
    }

    private $typeCheck;
    private $httpClient;
    private $streamFactory;
    private $encoder;
    private $decoder;
}
