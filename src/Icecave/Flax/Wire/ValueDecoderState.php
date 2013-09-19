<?php
namespace Icecave\Flax\Wire;

use Eloquent\Enumeration\Enumeration;

class ValueDecoderState extends Enumeration
{
    const BEGIN = 0;

    const STRING_COMPACT_DATA     = 10;
    const STRING_CHUNK_SIZE       = 11;
    const STRING_CHUNK_DATA       = 12;
    const STRING_CHUNK_FINAL_SIZE = 13;
    const STRING_CHUNK_FINAL_DATA = 14;

    const DOUBLE_1 = 20;
    const DOUBLE_2 = 21;
    const DOUBLE_4 = 22;
    const DOUBLE_8 = 23;

    const INT32 = 33;
    const INT64 = 34;

    const TIMESTAMP_MILLISECONDS = 40;
    const TIMESTAMP_MINUTES = 41;
}
