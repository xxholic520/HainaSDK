<?php


namespace Sammy1992\Haina\Tests\Core;


use GuzzleHttp\ClientInterface;
use Sammy1992\Haina\Core\ServiceContainer;
use Sammy1992\Haina\Core\Support\Config;
use Sammy1992\Haina\Tests\TestCase;

class ServiceContainerTest extends TestCase
{
    public function testBase()
    {
        $container = new ServiceContainer();

        $this->assertNotEmpty($container->getProviders());
        $this->assertInstanceOf(Config::class, $container['config']);
        $this->assertInstanceOf(ClientInterface::class, $container['http_client']);

        $container['foo'] = 'bar';
        $this->assertSame('bar', $container['foo']);
    }
}