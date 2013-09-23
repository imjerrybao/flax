<?php
namespace Icecave\Flax\Message;

use Eloquent\Enumeration\Enumeration;

class DecoderState extends Enumeration
{
    const VERSION = 0;
    const RESPONSE_TYPE = 1;
    const RESPONSE_VALUE = 2;
    const COMPLETE = 3;
}
