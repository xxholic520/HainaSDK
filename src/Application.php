<?php


namespace Sammy1992\Haina;

use Sammy1992\Haina\Core\ServiceContainer;

/**
 * Class Application
 *
 * @property Auth\AccessToken       $access_token
 * @property Company\Client         $company
 * @property Property\Client        $property
 * @property Resources\Client       $resources
 * @property Employee\Client        $employee
 * @property TemplateMessage\Client $template_message
 */
class Application extends ServiceContainer
{
    protected $providers = [
        Auth\ServiceProvider::class,
        Company\ServiceProvider::class,
        Property\ServiceProvider::class,
        Resources\ServiceProvider::class,
        Employee\ServiceProvider::class,
        TemplateMessage\ServiceProvider::class,

        Base\ServiceProvider::class
    ];

    protected $defaultConfig = [
        'http' => [
            'timeout'  => 15.0,
            'base_uri' => 'https://api.haina.com'
        ]
    ];

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this['base'], $name], $arguments);
    }
}