<?php
namespace Icecave\Flax;

use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;

class HessianClientFactoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->factory = new HessianClientFactory;
    }

    public function testCreate()
    {
        $url = 'http://hessian.caucho.com/test/test';

        $client = $this->factory->create($url);

        $this->assertInstanceOf(__NAMESPACE__ . '\HessianClientInterface', $client);

        $httpClient = Liberator::liberate($client)->httpClient;

        $this->assertSame('Flax/0.0.0', Liberator::liberate($httpClient)->userAgent);
        $this->assertSame('x-application/hessian', $httpClient->getDefaultOption('headers/Content-Type'));
    }
}
