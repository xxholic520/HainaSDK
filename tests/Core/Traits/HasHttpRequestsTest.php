<?php

namespace Sammy1992\Haina\Tests\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery\Mock;
use Sammy1992\Haina\Core\Traits\HasHttpRequests;
use Sammy1992\Haina\Tests\TestCase;

class HasHttpRequestsTest extends TestCase
{
    public function testDefalutOptions()
    {
        $this->assertSame([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ]
        ], HasHttpRequests::getDefaultOptions());

        HasHttpRequests::setDefaultOptions(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], HasHttpRequests::getDefaultOptions());
    }

    public function testHandlerStack()
    {
        $mock = \Mockery::mock(HasHttpRequests::class);
        $fn1  = function () {
        };
        $mock->pushMiddleware($fn1, 'fn1');
        $handlerStack = $mock->getHandlerStack();
        $this->assertInstanceOf(HandlerStack::class, $handlerStack);

        $handlerStack2 = \Mockery::mock(HandlerStack::class);
        $mock->setHandlerStack($handlerStack2);
        $this->assertSame($handlerStack2, $mock->getHandlerStack());
    }

    public function testMiddlewares()
    {
        $mock = \Mockery::mock(HasHttpRequests::class);
        $fn1  = function () {
        };
        $fn2  = function () {
        };
        $fn3  = function () {
        };

        $mock->pushMiddleware($fn1);
        $mock->pushMiddleware($fn2);
        $mock->pushMiddleware($fn3, 'fn3');

        $this->assertSame([$fn1, $fn2, 'fn3' => $fn3], $mock->getMiddlewares());
    }

    public function testHttpClient()
    {
        $mock    = \Mockery::mock(HasHttpRequests::class);
        $client1 = $mock->getHttpClient();
        $this->assertInstanceOf(ClientInterface::class, $client1);

        $client2 = \Mockery::mock(Client::class);
        $mock->setHttpClient($client2);
        $this->assertSame($client2, $mock->getHttpClient());
    }

    public function testRequest()
    {
        $mock             = \Mockery::mock(FakeClassForHasHttpRequestsTrait::class)->makePartial();
        $mockHandlerStack = \Mockery::mock(HandlerStack::class);
        $mockClient       = \Mockery::mock(Client::class);

        $mock->shouldReceive('getHandlerStack')->andReturn($mockHandlerStack);

        $response = new Response(200, [], 'mock-result');

        $mock->setHttpClient($mockClient);

        $mockClient->shouldReceive('request')->once()->with('GET', 'foo/bar', [
            'curl'     => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
            'handler'  => $mockHandlerStack,
            'base_uri' => 'http://mock-url'
        ])->andReturn($response);
        $this->assertSame($response, $mock->request('foo/bar'));

        $mockClient->shouldReceive('request')->once()->with('POST', 'foo/bar', [
            'curl'     => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
            'handler'  => $mockHandlerStack,
            'base_uri' => 'http://mock-url',
            'query'    => ['foo' => 'bar']
        ])->andReturn($response);
        $this->assertSame($response, $mock->request('foo/bar', 'post', ['query' => ['foo' => 'bar']]));

        $mockClient->shouldReceive('request')->once()->with('POST', 'foo/bar2', [
            'curl'     => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
            'handler'  => $mockHandlerStack,
            'base_uri' => 'http://mock-url',
            'headers'  => [
                'Content-Type' => 'application/json'
            ],
            'body'     => '{}'
        ])->andReturn($response);
        $this->assertSame($response, $mock->request('foo/bar2', 'POST', [
            'json' => []
        ]));
    }
}

class FakeClassForHasHttpRequestsTrait
{
    use HasHttpRequests;

    protected $baseUri = 'http://mock-url';
}