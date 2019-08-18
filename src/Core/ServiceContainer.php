<?php


namespace Sammy1992\Haina\Core;


use Pimple\Container;
use Sammy1992\Haina\Core\Providers\ConfigServiceProvider;
use Sammy1992\Haina\Core\Providers\HttpClientServiceProvider;

class ServiceContainer extends Container
{
    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $defaultConfig = [];

    /**
     * @var array
     */
    protected $userConfig = [];

    /**
     * ServiceContainer constructor.
     *
     * @param array $config
     * @param array $values
     */
    public function __construct(array $config = [], array $values = [])
    {
        $this->registerProviders($this->getProviders());

        parent::__construct($values);

        $this->userConfig = $config;
    }

    /**
     * @param $id
     * @param $value
     */
    public function rebind($id, $value)
    {
        $this->offsetUnset($id);
        $this->offsetSet($id, $value);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * @param $id
     * @param $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * @return array
     */
    public function getProviders(): array
    {
        return array_merge([
            ConfigServiceProvider::class,
            HttpClientServiceProvider::class
        ], $this->providers);
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $base = [
            'http' => [
                'timeout' => 30.0
            ]
        ];
        return array_replace_recursive($base, $this->defaultConfig, $this->userConfig);
    }

    /**
     * @param array $providers
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            parent::register(new $provider());
        }
    }
}