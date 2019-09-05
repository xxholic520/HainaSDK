<?php

namespace Sammy1992\Haina\Employee;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $pimple)
    {
        $pimple['employee'] = function ($pimple) {
            return new Client($pimple);
        };
    }
}