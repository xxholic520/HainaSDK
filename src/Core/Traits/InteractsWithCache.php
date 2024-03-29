<?php

namespace Sammy1992\Haina\Core\Traits;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use Sammy1992\Haina\Core\Exceptions\InvalidArgumentException;
use Sammy1992\Haina\Core\ServiceContainer;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\FilesystemCache;

/**
 * Trait InteractsWithCache
 */
trait InteractsWithCache
{
    /**
     * @var SimpleCacheInterface
     */
    protected $cache;

    /**
     * @return SimpleCacheInterface|Psr16Cache|FilesystemCache
     * @throws InvalidArgumentException
     */
    public function getCache()
    {
        if ($this->cache) {
            return $this->cache;
        }

        if (property_exists($this, 'app') && $this->app instanceof ServiceContainer && isset($this->app['cache'])) {
            $this->setCache($this->app['cache']);

            // Fix PHPStan error
            assert($this->cache instanceof \Psr\SimpleCache\CacheInterface);

            return $this->cache;
        }

        return $this->cache = $this->createDefaultCache();
    }

    /**
     * @param $cache
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setCache($cache)
    {
        // 比较cache interface
        if (empty(\array_intersect([SimpleCacheInterface::class, CacheItemPoolInterface::class], \class_implements($cache)))) {
            throw new InvalidArgumentException(
                \sprintf('The cache instance must implements %s or %s interface.',
                    SimpleCacheInterface::class, CacheItemPoolInterface::class
                )
            );
        }

        if ($cache instanceof CacheItemPoolInterface) {
            if (!$this->isSymfony43()) {
                throw new InvalidArgumentException(sprintf('The cache instance must implements %s', SimpleCacheInterface::class));
            }
            $cache = new Psr16Cache($cache);
        }

        $this->cache = $cache;

        return $this;
    }

    /**
     * @return Psr16Cache|FilesystemCache
     */
    protected function createDefaultCache()
    {
        if ($this->isSymfony43()) {
            return new Psr16Cache(new FilesystemAdapter('haina', 1500));
        }

        return new FilesystemCache();
    }

    /**
     * @return bool
     */
    protected function isSymfony43(): bool
    {
        return \class_exists('Symfony\Component\Cache\Psr16Cache');
    }
}
