<?php
namespace Icecave\Flax;

use Exception;
use Guzzle\Http\ClientInterface;
use Guzzle\Stream\PhpStreamRequestFactory;
use Icecave\Collections\Map;
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
use Icecave\Isolator\Isolator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HessianClient implements HessianClientInterface, LoggerAwareInterface
{
    /**
     * @param ClientInterface              $httpClient    The HTTP client used to make the request.
     * @param LoggerInterface|null         $logger        A PSR-3 logger to log requests against, or null to disable logging.
     * @param PhpStreamRequestFactory|null $streamFactory The stream factory used to create stream-based HTTP requests, or null to use the default.
     * @param Encoder|null                 $encoder       The Hessian message encoder, or null to use the default.
     * @param Decoder|null                 $decoder       The hessian message decoder, or null to use the default.
     * @param Isolator|null                $isolator
     */
    public function __construct(
        ClientInterface $httpClient,
        LoggerInterface $logger = null,
        PhpStreamRequestFactory $streamFactory = null,
        Encoder $encoder = null,
        Decoder $decoder = null,
        Isolator $isolator = null
    ) {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        if (null === $logger) {
            $logger = new NullLogger;
        }

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
        $this->logger = $logger;
        $this->streamFactory = $streamFactory;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->isolator = Isolator::get($isolator);
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->typeCheck->setLogger(func_get_args());

        $this->logger = $logger;
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

        $typeName = function ($value) {
            if (is_object($value)) {
                return get_class($value);
            }

            return gettype($value);
        };

        $methodDescription = sprintf(
            '%s(%s)',
            $name,
            implode(
                ', ',
                array_map($typeName, $arguments)
            )
        );

        try {
            $time = $this->isolator->microtime(true);
            list($reply, $fault) = $this->doRequest($name, $arguments);
            $time = $this->isolator->microtime(true) - $time;
        } catch (Exception $e) {
            $this->logger->error(
                'Error occurred while invoking "' . $methodDescription . '".',
                array(
                    'arguments' => $arguments,
                    'exception' => $e
                )
            );

            throw $e;
        }

        if ($fault) {
            $this->logger->debug(
                'Invoked "' . $methodDescription . '" in ' . $time . ' second(s), with "' . $reply['code'] . '" fault: ' . $fault->getMessage(),
                array(
                    'arguments' => $arguments,
                    'fault' => $reply
                )
            );

            throw $fault;
        }

        $this->logger->debug(
            'Invoked "' . $methodDescription . '" in ' . $time . ' second(s), with "' . $typeName($reply) . '" reply.',
            array(
                'arguments' => $arguments,
                'reply' => $reply
            )
        );

        return $reply;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return tuple<mixed, Exception|null>
     */
    protected function doRequest($name, array $arguments)
    {
        $this->typeCheck->doRequest(func_get_args());

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
            return array($value, null);
        } else {
            return array($value, $this->createFaultException($value));
        }
    }

    /**
     * @param Map $properties
     *
     * @return AbstractHessianFaultException
     */
    protected function createFaultException(Map $properties)
    {
        $this->typeCheck->createFaultException(func_get_args());

        if (!$properties->hasKey('code')) {
            throw new DecodeException('Encountered Hessian fault with no fault code.');
        }

        switch ($properties['code']) {
            case 'NoSuchMethodException':
                return new NoSuchMethodException($properties);
            case 'NoSuchObjectException':
                return new NoSuchObjectException($properties);
            case 'ProtocolException':
                return new ProtocolException($properties);
            case 'RequireHeaderException':
                return new RequireHeaderException($properties);
            case 'ServiceException':
                return new ServiceException($properties);
        }

        throw new DecodeException('Unknown Hessian fault code: ' . $properties['code'] . '.');
    }

    private $typeCheck;
    private $httpClient;
    private $logger;
    private $streamFactory;
    private $encoder;
    private $decoder;
}
