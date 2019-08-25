<?php

namespace Sammy1992\Haina\Resources;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $pimple)
    {
        $pimple['resources'] = function ($pimple) {
            return new Client($pimple);
        };
    }
}