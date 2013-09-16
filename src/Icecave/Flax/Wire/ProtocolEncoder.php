<?php
namespace Icecave\Flax\Wire;

use Icecave\Flax\TypeCheck\TypeCheck;

class ProtocolEncoder
{
    public function __construct()
    {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        $this->valueEncoder = new ValueEncoder;
    }

    /**
     * @return string
     */
    public function encodeVersion()
    {
        $this->typeCheck->encodeVersion(func_get_args());

        return "H\x02\x00";
    }

    /**
     * @param string $methodName
     * @param array  $arguments
     *
     * @return string
     */
    public function encodeCall($methodName, array $arguments)
    {
        $this->typeCheck->encodeCall(func_get_args());

        $this->valueEncoder->reset();

        $buffer  = 'C';
        $buffer .= $this->valueEncoder->encode($methodName);
        $buffer .= $this->valueEncoder->encode(count($arguments));

        foreach ($arguments as $value) {
            $buffer .= $this->valueEncoder->encode($value);
        }

        return $buffer;
    }

    private $typeCheck;
    private $valueEncoder;
}
