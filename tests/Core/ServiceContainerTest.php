<?php


namespace Sammy1992\Haina\Tests\Core;


use Sammy1992\Haina\Core\ServiceContainer;
use Sammy1992\Haina\Support\Config;
use Sammy1992\Haina\Tests\TestCase;

class ServiceContainerTest extends TestCase
{
    public function testBase()
    {
        $container = new ServiceContainer();

        $this->assertNotEmpty($container->getProviders());
        $this->assertInstanceOf(Config::class, $container['config']);

        $container['foo'] = 'bar';
        $this->assertSame('bar', $container['foo']);
    }
}