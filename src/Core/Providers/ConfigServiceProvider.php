<?php


namespace Sammy1992\Haina\Core\Providers;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Sammy1992\Haina\Core\Support\Config;

/**
 * Class ConfigServiceProvider
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $pimple['config'] = function ($pimple) {
            return new Config($pimple->getConfig());
        };
    }
}