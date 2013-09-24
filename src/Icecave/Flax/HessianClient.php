<?php
namespace Icecave\Flax;

use Guzzle\Http\ClientInterface;
use Guzzle\Stream\PhpStreamRequestFactory;
use Icecave\Flax\Exception\AbstractHessianFaultException;
use Icecave\Flax\Exception\DecodeException;
use Icecave\Flax\Exception\NoSuchMethodException;
use Icecave\Flax\Exception\NoSuchObjectException;
use Icecave\Flax\Exception\ProtocolException;
use Icecave\Flax\Exception\RequireHeaderException;
use Icecave\Flax\Exception\ServiceException;
use Icecave\Flax\Message\Decoder;
use Icecave\Flax\Message\Encoder;
use Icecave\Flax\TypeCheck\TypeCheck;

class HessianClient implements HessianClientInterface
{
    /**
     * @param ClientInterface              $httpClient
     * @param PhpStreamRequestFactory|null $streamFactory
     * @param Encoder|null                 $encoder
     * @param Decoder|null                 $decoder
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

        return $this->invokeArray($name, $arguments);
    }

    /**
     * Invoke a Hessian operation.
     *
     * @param string $name          The name of the operation to invoke.
     * @param mixed  $arguments,... Arguments to the operation.
     *
     * @return mixed                                   The result of the Hessian call.
     * @throws Exception\AbstractHessianFaultException
     */
    public function invoke($name)
    {
        $this->typeCheck->invoke(func_get_args());

        $arguments = array_slice(func_get_args(), 1);

        return $this->invokeArray($name, $arguments);
    }

    /**
     * Invoke a Hessian operation.
     *
     * @param string       $name      The name of the operation to invoke.
     * @param array<mixed> $arguments Arguments to the operation.
     *
     * @return mixed                                   The result of the Hessian call.
     * @throws Exception\AbstractHessianFaultException
     */
    public function invokeArray($name, array $arguments = array())
    {
        $this->typeCheck->invokeArray(func_get_args());

        $this->encoder->reset();
        $this->decoder->reset();

        $buffer  = $this->encoder->encodeVersion();
        $buffer .= $this->encoder->encodeCall($name, $arguments);
        $request = $this->httpClient->post(null, null, $buffer);
        $stream  = $this->streamFactory->fromRequest($request);

        while (!$stream->feof()) {
            $this->decoder->feed(
                $stream->read(1024)
            );
        }

        list($success, $value) = $this->decoder->finalize();

        if ($success) {
            return $value;
        }

        switch ($value['code']) {
            case 'NoSuchMethodException':
                throw new NoSuchMethodException($value);
            case 'NoSuchObjectException':
                throw new NoSuchObjectException($value);
            case 'ProtocolException':
                throw new ProtocolException($value);
            case 'RequireHeaderException':
                throw new RequireHeaderException($value);
            case 'ServiceException':
                throw new ServiceException($value);
        }

        throw new DecodeException('Unknown exception code: ' . $value['code'] . '.');
    }

    private $typeCheck;
    private $httpClient;
    private $streamFactory;
    private $encoder;
    private $decoder;
}
