<?php

namespace Sammy1992\Haina\Property;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $pimple)
    {
        $pimple['property'] = function ($pimple) {
            return new Client($pimple);
        };
    }
}