<?php
namespace Icecave\Flax\Wire;

use Eloquent\Enumeration\Enumeration;

class ValueDecoderState extends Enumeration
{
    const BEGIN = 0;

    const STRING_SIZE               = 10;
    const STRING_DATA               = 11;
    const STRING_CHUNK_SIZE         = 12;
    const STRING_CHUNK_DATA         = 13;
    const STRING_CHUNK_FINAL_SIZE   = 14;
    const STRING_CHUNK_FINAL_DATA   = 15;
    const STRING_CHUNK_CONTINUATION = 16;

    const BINARY_SIZE               = 20;
    const BINARY_DATA               = 21;
    const BINARY_CHUNK_SIZE         = 22;
    const BINARY_CHUNK_DATA         = 23;
    const BINARY_CHUNK_FINAL_SIZE   = 24;
    const BINARY_CHUNK_FINAL_DATA   = 25;
    const BINARY_CHUNK_CONTINUATION = 26;

    const DOUBLE_1 = 30;
    const DOUBLE_2 = 31;
    const DOUBLE_4 = 32;
    const DOUBLE_8 = 33;

    const INT32 = 43;
    const INT64 = 44;

    const TIMESTAMP_MILLISECONDS = 50;
    const TIMESTAMP_MINUTES      = 51;

    const COLLECTION_TYPE = 60;

    const VECTOR       = 70;
    const VECTOR_SIZE  = 71;
    const VECTOR_FIXED = 72;

    const MAP_KEY      = 80;
    const MAP_VALUE    = 81;

    const CLASS_DEFINITION_NAME  = 90;
    const CLASS_DEFINITION_SIZE  = 91;
    const CLASS_DEFINITION_FIELD = 92;

    const OBJECT_INSTANCE_TYPE   = 100;
    const OBJECT_INSTANCE_FIELD  = 101;

    const REFERENCE = 110;
}
