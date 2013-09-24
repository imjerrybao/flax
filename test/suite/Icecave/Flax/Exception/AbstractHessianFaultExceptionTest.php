<?php
namespace Icecave\Flax\Exception;

use Exception;
use Icecave\Collections\Map;
use Phake;
use PHPUnit_Framework_TestCase;

class AbstractHessianFaultExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $previous = new Exception;
        $properties = Map::create(array('message', 'The message.'));
        $exception = Phake::partialMock(__NAMESPACE__ . '\AbstractHessianFaultException', $properties, $previous);

        $this->assertSame('The message.', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame($properties, $exception->properties());
    }
}
