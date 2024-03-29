<?php

namespace Sammy1992\Haina\Company;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $pimple)
    {
        $pimple['company'] = function ($pimple) {
            return new Client($pimple);
        };
    }
}