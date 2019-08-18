<?php


namespace Sammy1992\Haina\Auth;


use Pimple\Container;
use Pimple\ServiceProviderInterface;

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
        $pimple['access_token'] = function ($pimple) {
            return new AccessToken($pimple);
        };
    }
}