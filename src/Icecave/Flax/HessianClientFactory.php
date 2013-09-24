<?php
namespace Icecave\Flax;

use Guzzle\Http\Client;
use Icecave\Flax\TypeCheck\TypeCheck;

class HessianClientFactory
{
    /**
     * @param string $url
     */
    public function create($url)
    {
        TypeCheck::get(__CLASS__)->create(func_get_args());

        $httpClient = new Client($url);
        $httpClient->setUserAgent(
            sprintf('Flax/%s', PackageInfo::VERSION)
        );

        $httpClient->setDefaultOption(
            'headers/Content-Type',
            'x-application/hessian'
        );

        return new HessianClient($httpClient);
    }
}
