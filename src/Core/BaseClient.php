<?php


namespace Sammy1992\Haina\Core;


use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
     * @var string
     */
    protected $baseUri;

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
     * @param array $query
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpGet($url, $query = [])
    {
        return $this->request($url, 'GET', ['query' => $query]);
    }

    /**
     * @param $url
     * @param array $data
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPost($url, $data = [])
    {
        return $this->request($url, 'POST', ['form_params' => $data]);
    }

    /**
     * @param $url
     * @param array $data
     * @param array $query
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPostJson($url, $data = [], $query = [])
    {
        return $this->request($url, 'POST', ['query' => $query, 'json' => $data]);
    }

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
        // retry
        $this->pushMiddleware($this->retryMiddleware(), 'retry');
        // access_token
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
                    $request = $this->accessToken->applyToRequest($request, $options);
                }
                return $handler($request, $options);
            };
        };
    }

    /**
     * Retry middleware
     * @return callable
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null
        ) {
            if ($retries < $this->app->config->get('http.max_retries', 1) && $response) {
                $response = json_decode($response->getBody(), true);

                if (isset($response['retcode']) && in_array(abs($response['retcode']), [20002, 20004], true)) {
                    $this->accessToken->refresh();
                    return true;
                }
            }
            return false;
        }, function () {
            return abs($this->app->config->get('http.retry_delay', 500));
        });
    }
}