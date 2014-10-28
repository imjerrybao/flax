<?php
namespace Icecave\Flax;

use PHPUnit_Framework_TestCase;
use stdClass;

class ObjectTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->internal = new stdClass();
        $this->object = new Object('foo', $this->internal);
    }

    public function testClassName()
    {
        $this->assertSame('foo', $this->object->className());
    }

    public function testObject()
    {
        $this->assertSame($this->internal, $this->object->object());
    }
}
