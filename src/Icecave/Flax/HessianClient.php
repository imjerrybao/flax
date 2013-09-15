<?php
namespace Icecave\Flax;

use Buzz\Browser;
use Icecave\Flax\TypeCheck\TypeCheck;

class HessianClient implements HessianClientInterface
{
    /**
     * @param string  $url
     * @param Browser $httpBrowser
     */
    public function __construct($url, Browser $httpBrowser)
    {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        $this->url = $url;
        $this->httpBrowser = $httpBrowser;
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

        throw new \Exception('Not implemented.');
    }

    private $url;
    private $typeCheck;
    private $httpBrowser;
}
