<?php


namespace Sammy1992\Haina\Core;


use Psr\Http\Message\RequestInterface;
use Sammy1992\Haina\Core\Traits\HasHttpRequests;

/**
 * Class BaseClient
 */
class BaseClient
{
    use HasHttpRequests {
        request as performRequest;
    }

    /**
     * @var ServiceContainer
     */
    protected $app;

    /**
     * @var mixed|AccessToken
     */
    protected $accessToken;

    /**
     * @return mixed|AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param AccessToken $accessToken
     * @return $this
     */
    public function setAccessToken(AccessToken $accessToken): BaseClient
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * BaseClient constructor.
     *
     * @param ServiceContainer $app
     * @param AccessToken $accessToken
     */
    public function __construct(ServiceContainer $app, AccessToken $accessToken = null)
    {
        $this->app = $app;

        $this->accessToken = $accessToken ?? $this->app['access_token'];
    }

    /**
     * @param $url
     * @param string $methods
     * @param array $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($url, $methods = 'GET', $options = [])
    {
        if (empty($this->middlewares)) {
            $this->registerHttpMiddlewares();
        }

        $response = $this->performRequest($url, $methods, $options);

        $result = json_decode($response->getBody()->getContents(), true);

        return $result;
    }

    /**
     * register http middleware
     */
    public function registerHttpMiddlewares()
    {
        $this->pushMiddleware($this->accessTokenMiddleware(), 'access_token');
    }

    /**
     * @return \Closure
     */
    protected function accessTokenMiddleware()
    {
        return function ($handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if ($this->accessToken) {
                    $this->accessToken->applyToRequest($request, $options);
                }
                return $handler($request, $options);
            };
        };
    }
}