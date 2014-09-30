<?php
namespace Icecave\Flax;

use PHPUnit_Framework_TestCase;

class BinaryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->binary = new Binary('foo');
    }

    public function testConstructorDefaults()
    {
        $binary = new Binary();

        $this->assertSame('', $binary->data());
    }

    public function testData()
    {
        $this->assertSame('foo', $this->binary->data());
    }
}
