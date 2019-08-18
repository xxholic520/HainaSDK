<?php

namespace Sammy1992\Haina\Core;

use Psr\Http\Message\RequestInterface;
use Sammy1992\Haina\Core\Exceptions\HttpException;
use Sammy1992\Haina\Core\Exceptions\InvalidArgumentException;
use Sammy1992\Haina\Core\Exceptions\RuntimeException;
use Sammy1992\Haina\Core\Traits\HasHttpRequests;
use Sammy1992\Haina\Core\Traits\InteractsWithCache;

abstract class AccessToken
{
    use HasHttpRequests, InteractsWithCache;

    /**
     * @var
     */
    protected $queryName;

    /**
     * @var array
     */
    protected $token;

    /**
     * @var string
     */
    protected $tokenKey = 'access_token';

    /**
     * @var string
     */
    protected $cachePrefix = 'haina.cache.access_token';

    /**
     * @var int
     */
    protected $safeTime = 500;

    /**
     * @var string
     */
    protected $endPoint;

    /**
     * @var ServiceContainer
     */
    protected $app;

    /**
     * AccessToken constructor.
     *
     * @param ServiceContainer $app
     */
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }

    /**
     * @return string
     */
    public function getTokenKey(): string
    {
        return $this->tokenKey;
    }

    /**
     * @return int
     */
    public function getSafeTime(): int
    {
        return $this->safeTime;
    }

    /**
     * @return ServiceContainer
     */
    public function getApp(): ServiceContainer
    {
        return $this->app;
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public function getEndPoint(): string
    {
        if (is_null($this->endPoint)) {
            throw new InvalidArgumentException('must set endPoint');
        }

        return $this->endPoint;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cachePrefix . md5(json_encode($this->getCredentials()));
    }

    /**
     * @param bool $refresh
     * @return array
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getToken($refresh = false): array
    {
        $cacheKey = $this->getCacheKey();
        $cache    = $this->getCache();
        if (!$refresh && $cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $token = $this->requestToken($this->getCredentials());

        $this->setToken($token[$this->tokenKey], $token['expires_in']);

        return $token;
    }

    /**
     * @return array
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getRefreshedToken(): array
    {
        return $this->getToken(true);
    }

    /**
     * @param string $token
     * @param int $lifetime
     * @return $this
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setToken(string $token, $lifetime = 7200)
    {
        $this->getCache()->set($this->getCacheKey(), [
            $this->tokenKey => $token,
            'expires_in'    => $lifetime
        ], $lifetime - $this->safeTime);

        if (!$this->getCache()->has($this->getCacheKey())) {
            throw new RuntimeException('Cache fail');
        }

        return $this;
    }

    /**
     * @return $this
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function refresh()
    {
        $this->getToken(true);

        return $this;
    }

    /**
     * @param array $credentials
     * @return array
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestToken(array $credentials)
    {
        $response = $this->request($this->getEndPoint(), 'POST', [
            'json' => $credentials
        ]);
        $result   = json_decode($response->getBody()->getContents(), true);

        if (empty($result[$this->tokenKey])) {
            throw new HttpException('Request fail token.' . json_encode($result, JSON_UNESCAPED_UNICODE), $response, $result);
        }

        return $result;
    }

    /**
     * @return array
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getQuery()
    {
        return [$this->queryName ?? $this->tokenKey => $this->getToken()[$this->tokenKey]];
    }

    /**
     * @param RequestInterface $request
     * @param array $requestOptions
     * @return RequestInterface
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function applyToRequest(RequestInterface $request, $requestOptions = [])
    {
        parse_str($request->getUri()->getQuery(), $query);

        $query = http_build_query(array_merge($this->getQuery(), $query));

        return $request->withUri($request->getUri()->withQuery($query));
    }

    /**
     * @return array
     */
    abstract protected function getCredentials(): array;
}
