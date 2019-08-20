<?php

namespace Sammy1992\Haina\Tests\Core\Support;


use PHPUnit\Framework\TestCase;
use Sammy1992\Haina\Core\Support\Collection;

class CollectionTest extends TestCase
{
    public function testCollection()
    {
        $collection = new Collection([
            'foo' => 'bar',
            'baz' => [
                'x' => 'y',
                'z' => 'haha'
            ]
        ]);

        $this->assertSame('bar', $collection['foo']);
        $this->assertSame('bar', $collection->get('foo'));

        $collection['foo'] = 'baz';
        $this->assertSame('baz', $collection['foo']);
        $this->assertSame('y', $collection['baz.x']);
        $collection['baz.z'] = 123;
        $this->assertSame(123, $collection['baz.z']);
        $this->assertSame(2, count($collection));
        $this->assertSame(['foo', 'baz'], $collection->getKeys());
        $this->assertSame($collection['foo'], $collection->first());
        $this->assertSame($collection['baz'], $collection->last());
        $this->assertTrue($collection->containsKey('foo'));
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->exists(function ($item, $key) {
            return $key === 'foo';
        }));
        $this->assertInstanceOf(Collection::class, $collection->filter(function ($item, $key) {
            return $key === 'foo';
        }));
        $this->assertInstanceOf(Collection::class, $collection->map(function ($item) {
            if (is_string($item)) $item = 'new bar';
            return $item;
        }));

        $collection->removeElement($collection['baz']);
        $this->assertSame(1, $collection->count());

//        unset($collection['foo']);
//        $collection->remove('foo');
//        $this->assertArrayNotHasKey('foo', $collection);

        $collection->clear();
        $this->assertTrue($collection->isEmpty());
    }

    public function testSetValue()
    {
        $collection = new Collection(['foo' => 'bar']);

        $collection['newFoo'] = 123;
//        $this->assertSame(123, $collection['newFoo.newBar']);
    }
}
