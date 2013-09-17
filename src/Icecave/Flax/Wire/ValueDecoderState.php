<?php
namespace Icecave\Flax\Wire;

use Eloquent\Enumeration\Enumeration;

class ValueDecoderState extends Enumeration
{
    const BEGIN = 0;

    const RESPONSE_TYPE = 1;
    const RESPONSE_VALUE = 2;
    const COMPLETE = 3;
}
