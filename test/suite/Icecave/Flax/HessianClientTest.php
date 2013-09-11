<?php
namespace Icecave\Flax;

use PHPUnit_Framework_TestCase;

class HessianClientTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new HessianClient;
    }

    public function testPlaceHolder()
    {
        $this->assertTrue(true);
    }
}
