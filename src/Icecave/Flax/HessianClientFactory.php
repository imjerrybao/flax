<?php
namespace Icecave\Flax;

use Buzz\Client\Curl;
use Guzzle\Http\Client;
use Icecave\Flax\TypeCheck\TypeCheck;

class HessianClientFactory
{
    /**
     * @param string $url
     */
    public function connect($url)
    {
        TypeCheck::get(__CLASS__)->connect(func_get_args());

        $httpClient = new Client($url);
        $httpClient->setUserAgent(
            sprintf('Flax/%s', PackageInfo::VERSION)
        );

        return new HessianClient($httpClient);
    }
}
