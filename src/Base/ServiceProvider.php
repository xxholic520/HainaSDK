<?php


namespace Sammy1992\Haina\Base;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Sammy1992\Haina\Base\Client;

/**
 * Class ServiceProvider
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $pimple['base'] = function ($pimple) {
            return new Client($pimple);
        };
    }
}