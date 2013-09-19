<?php
namespace Icecave\Flax;

use Buzz\Browser;
use Buzz\Client\Curl;
use Icecave\Flax\TypeCheck\TypeCheck;

class HessianClientFactory
{
    /**
     * @param string      $url
     * @param string|null $username
     * @param string      $password
     */
    public function connect($url, $username = null, $password = '')
    {
        TypeCheck::get(__CLASS__)->connect(func_get_args());

        if ($username) {
            $httpClient = new Curl;
            $httpClient->setOption(CURLOPT_USERPWD, $username . ':' . $password);
        }

        $httpBrowser = new Browser($httpClient);

        return new HessianClient($url, $httpBrowser);
    }
}
