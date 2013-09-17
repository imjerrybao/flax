<?php
namespace Icecave\Flax\Wire;

use Icecave\Flax\TypeCheck\TypeCheck;

class ProtocolDecoder
{
    // public function __construct()
    // {
    //     $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

    //     // $this->valueDecoder = new ValueDecoder;

    //     $this->reset();
    // }

    // public function reset()
    // {
    //     $this->buffer = '';
    //     $this->state = ProtocolDecoderState::BEGIN();
    // }

    // public function decode($buffer)
    // {
    //     $this->reset();

    //     $this->feed($buffer);

    //     return $this->finalize();
    // }

    // public function feed($buffer)
    // {
    //     $this->typeCheck->feed(func_get_args());

    //     $length = strlen($buffer);

    //     for ($index = 0; $index < $length; ++$buffer) {
    //         $this->feedByte($buffer[$index]);
    //     }
    // }

    // public function finalize()
    // {
    //     if (ProtocolDecoderState::COMPLETE() !== $this->state) {
    //         throw new Exception\DecodeException('Incomplete response.');
    //     }

    //     $result = array($this->responseIsFault, $this->responseValue);

    //     $this->reset();

    //     return $result;
    // }

    // private function feedByte($byte)
    // {
    //     switch ($this->state) {
    //         case ProtocolDecoderState::RESPONSE_TYPE():
    //             return $this->doResponseType($byte);
    //         case ProtocolDecoderState::RESPONSE_VALUE():
    //             return $this->doResponseValue($byte);
    //         case ProtocolDecoderState::COMPLETE():
    //             return;
    //     }

    //     return $this->doBegin($byte);
    // }

    // private function doVersion($byte)
    // {
    //     $this->buffer .= $byte;

    //     if (3 !== strlen($this->buffer)) {
    //         return;
    //     } elseif ($this->buffer !== "H\x02\x00") {
    //         throw new Exception\DecodeException('Unsupported version: "' . $this->buffer . '".');
    //     }

    //     $this->buffer = '';
    //     $this->state = ProtocolDecoderState::RESPONSE_TYPE();
    // }

    // private function doResponseType($byte)
    // {
    //     if ('R' === $byte) {
    //         $this->responseIsFault = false;
    //     } elseif ('F' === $byte) {
    //         $this->responseIsFault = true;
    //     } else {
    //         throw new Exception\DecodeException('Invalid response.');
    //     }

    //     $this->state = ProtocolDecoderState::RESPONSE_VALUE();
    // }

    // private function doResponseValue($byte)
    // {
    //     $this->valueDecoder->feed($byte, $this->responseValue);

    //         return;
    //     }

    //     $this->state = ProtocolDecoderState::COMPLETE();

    //     if ($this->responseIsFault) {
    //         $this->responseValue = $this->convertFaultResponseToException($this->responseValue);
    //     }
    // }

    // private function convertFaultResponseToException($responseValue)
    // {
    //     if (!is_array($responseValue)) {
    //         throw new Exception\DecodeException('Invalid response: expected fault response to be an array.');
    //     } elseif (!array_key_exists('code', $responseValue)) {
    //         throw new Exception\DecodeException('Invalid response: fault response does not contain a code.');
    //     } elseif (!array_key_exists('message', $responseValue)) {
    //         throw new Exception\DecodeException('Invalid response: fault response does not contain a message.');
    //     }

    //     switch ($responseValue['code']) {
    //         case 'ProtocolException':
    //             return new ProtocolException($responseValue['message']);
    //         case 'NoSuchObjectException':
    //             return new NoSuchObjectException($responseValue['message']);
    //         case 'NoSuchMethodException':
    //             return new NoSuchMethodException($responseValue['message']);
    //         case 'RequireHeaderException':
    //             return new RequireHeaderException($responseValue['message']);
    //         case 'ServiceException':
    //             return new ServiceException($responseValue['message']);
    //     }

    //     throw new Exception\DecodeException('Invalid response: fault response specified unsupported code: "' . $this->responseValue['code'] . '".');
    // }

    private $typeCheck;
    private $valueDecoder;
    private $buffer;
    private $state;
    private $responseValue;
    private $isComplete;
}
