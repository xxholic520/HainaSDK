<?php


namespace Sammy1992\Haina\Core\Providers;


use GuzzleHttp\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class HttpClientServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        !isset($pimple['http_client']) && $pimple['http_client'] = function ($pimple) {
            return new Client($pimple['config']['http'] ?? []);
        };
    }
}