<?php
namespace Icecave\Flax;

use Buzz\Browser;
use Icecave\Flax\TypeCheck\TypeCheck;
use Icecave\Flax\Message\Encoder;
use Icecave\Flax\Message\Decoder;

class HessianClient implements HessianClientInterface
{
    /**
     * @param string       $url
     * @param Browser      $httpBrowser
     * @param Encoder|null $encoder
     * @param Decoder|null $decoder
     */
    public function __construct(
        $url,
        Browser $httpBrowser,
        Encoder $encoder = null,
        Decoder $decoder = null
    ) {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        if (null === $encoder) {
            $encoder = new Encoder;
        }

        if (null === $decoder) {
            $decoder = new Decoder;
        }

        $this->url = $url;
        $this->httpBrowser = $httpBrowser;
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

        $this->invoke($name, $arguments);
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

        $response = $this->httpBrowser->post(
            $this->url,
            array(
                'Content-Type' => 'x-application/hessian',
            ),
            $buffer
        );

        var_dump($response);
    }

    private $url;
    private $typeCheck;
    private $httpBrowser;
    private $encoder;
    private $decoder;
}
