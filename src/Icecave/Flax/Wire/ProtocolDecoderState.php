<?php
namespace Icecave\Flax\Wire;

use Eloquent\Enumeration\Enumeration;

class ProtocolDecoderState extends Enumeration
{
    const VERSION = 0;
    const RESPONSE_TYPE = 1;
    const RESPONSE_VALUE = 2;
    const COMPLETE = 3;
}
