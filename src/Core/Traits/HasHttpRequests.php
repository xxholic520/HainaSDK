<?php


namespace Sammy1992\Haina\Core\Traits;


use function GuzzleHttp\choose_handler;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait HasHttpRequest
 */
trait HasHttpRequests
{
    protected $httpClient;

    protected static $defaults = [
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ]
    ];

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var HandlerStack
     */
    protected $handlerStack;

    /**
     * @return mixed
     */
    public function getHandlerStack(): HandlerStack
    {
        if (is_null($this->handlerStack)) {
            $this->handlerStack = HandlerStack::create($this->getHandler());
            foreach ($this->middlewares as $middleware) {
                $this->handlerStack->push($middleware);
            }
        }
        return $this->handlerStack;
    }

    /**
     * @param mixed $handlerStack
     */
    public function setHandlerStack(HandlerStack $handlerStack): void
    {
        $this->handlerStack = $handlerStack;
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param array $defaults
     */
    public static function setDefaultOptions($defaults = [])
    {
        self::$defaults = $defaults;
    }

    /**
     * @return array
     */
    public static function getDefaultOptions(): array
    {
        return self::$defaults;
    }

    /**
     * @param ClientInterface $client
     * @return $this
     */
    public function setHttpClient(ClientInterface $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * @return Client|ClientInterface
     */
    public function getHttpClient()
    {
        if (!($this->httpClient instanceof ClientInterface)) {
            if (property_exists($this, 'app') && $this->app['http_client']) {
                $this->httpClient = $this->app['http_client'];
            } else {
                $this->httpClient = new Client(['handler' => HandlerStack::create($this->getHandler())]);
            }
        }

        return $this->httpClient;
    }

    /**
     * @param callable $middleware
     * @param null $name
     */
    public function pushMiddleware(callable $middleware, $name = null)
    {
        if (!is_null($name)) {
            $this->middlewares[$name] = $middleware;
        } else {
            array_push($this->middlewares, $middleware);
        }
    }

    /**
     * @return callable
     */
    public function getHandler()
    {
        return choose_handler();
    }

    /**
     * @param $url
     * @param string $method
     * @param array $options
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($url, $method = 'GET', $options = []): ResponseInterface
    {
        $method = strtoupper($method);

        $options = array_merge(self::$defaults, $options, ['handler' => $this->getHandlerStack()]);

        if (isset($options['json']) && is_array($options['json'])) {
            $options['headers'] = array_merge($options['headers'] ?? [], ['Content-Type' => 'application/json']);

            if (empty($options['json'])) {
                $options['body'] = \GuzzleHttp\json_encode($options['json'], JSON_FORCE_OBJECT);
            } else {
                $options['body'] = \GuzzleHttp\json_encode($options['json'], JSON_UNESCAPED_UNICODE);
            }

            unset($options['json']);
        }

        if (property_exists($this, 'baseUri') && $this->baseUri) {
            $options['base_uri'] = $this->baseUri;
        }

        $response = $this->getHttpClient()->request($method, $url, $options);
        $response->getBody()->rewind();

        return $response;
    }
}