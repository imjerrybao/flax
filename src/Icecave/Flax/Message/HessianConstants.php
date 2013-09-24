<?php
namespace Icecave\Flax\Message;

class HessianConstants
{
    const VERSION_START  = 0x48;
    const VERSION        = "\x02\x00";

    const MESSAGE_TYPE_CALL  = 0x43;
    const MESSAGE_TYPE_FAULT = 0x46;
    const MESSAGE_TYPE_REPLY = 0x52;

    const COLLECTION_TERMINATOR = 0x5a;
}
