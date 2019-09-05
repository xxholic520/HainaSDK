<?php

namespace Sammy1992\Haina\TemplateMessage;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $pimple)
    {
        $pimple['template_message'] = function ($pimple) {
            return new Client($pimple);
        };
    }
}