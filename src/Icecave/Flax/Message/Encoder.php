<?php
namespace Icecave\Flax\Message;

use Icecave\Flax\Serialization\Encoder as SerializationEncoder;
use Icecave\Flax\TypeCheck\TypeCheck;

class Encoder
{
    public function __construct()
    {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        $this->serializationEncoder = new SerializationEncoder;
    }

    public function reset()
    {
        $this->typeCheck->reset(func_get_args());

        $this->serializationEncoder->reset();
    }

    /**
     * @return string
     */
    public function encodeVersion()
    {
        $this->typeCheck->encodeVersion(func_get_args());

        return pack('c', HessianConstants::VERSION_START) . HessianConstants::VERSION;
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

        $buffer  = pack('c', HessianConstants::MESSAGE_TYPE_CALL);
        $buffer .= $this->serializationEncoder->encode($methodName);
        $buffer .= $this->serializationEncoder->encode(count($arguments));

        foreach ($arguments as $value) {
            $buffer .= $this->serializationEncoder->encode($value);
        }

        return $buffer;
    }

    private $typeCheck;
    private $serializationEncoder;
}
