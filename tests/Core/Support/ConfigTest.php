<?php

namespace Sammy1992\Haina\Tests\Core\Support;

use PHPUnit\Framework\TestCase;
use Sammy1992\Haina\Core\Config;

class ConfigTest extends TestCase
{
    public function testConfig()
    {
        $config = new Config([
            'foo' => 'bar',
            'baz' => [
                'a' => 1,
                'b' => 9999,
                'c' => [
                    's' => 'haha'
                ]
            ]
        ]);

        // 是否存在“foo”
        $this->assertTrue(isset($config['foo']));

        // 是否等于值
        $this->assertSame('bar', $config['foo']);
        $this->assertSame('bar', $config->get('foo'));
        $this->assertNull($config->get(null));

        $this->assertSame(1, $config['baz.a']);
        $this->assertSame(9999, $config['baz.b']);
        $this->assertSame('haha', $config['baz.c.s']);

        $this->assertSame(1, $config->get('baz.a'));
        $this->assertSame(9999, $config->get('baz.d', 9999));

        $config['foo'] = 'new bar';
        $this->assertSame('new bar', $config['foo']);
        $this->assertSame('new bar', $config->get('foo'));

        unset($config['foo']);
        $this->assertTrue(!isset($config['foo']));

        $config['foo.xyz'] = 1;
        $this->assertSame(1, $config['foo.xyz']);
    }
}