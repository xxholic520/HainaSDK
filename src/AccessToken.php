<?php

namespace Sammy1992\Haina;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\FilesystemCache;

class AccessToken
{
  protected $endPoint = 'https://api.haina.com/access/getToken';

  protected $cache;

  public function getToken($refresh = false)
  {
    // TODO
  }

  public function getCache()
  {
    if (!is_null($this->cache)) {
      return $this->cache;
    }

    return $this->cache = $this->setDefaultCache();
  }

  public function setDefaultCache()
  {
    if (\class_exists('Symfony\Component\Cache\Psr16Cache')) {
      return new Psr16Cache(new FilesystemAdapter('haina', 1500));
    }
    return new FilesystemCache();
  }
}
