<?php


namespace Sammy1992\Haina\Tests\Core;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Sammy1992\Haina\Core\AccessToken;
use Sammy1992\Haina\Core\BaseClient;
use Sammy1992\Haina\Core\ServiceContainer;
use Sammy1992\Haina\Tests\TestCase;

class BaseClientTest extends TestCase
{
    public function mock(ServiceContainer $app = null, AccessToken $accessToken = null)
    {
        $app         = $app ?? \Mockery::mock(ServiceContainer::class);
        $accessToken = $accessToken ?? \Mockery::mock(AccessToken::class, [$app]);

        return \Mockery::mock(BaseClient::class, [$app, $accessToken])->makePartial();
    }

    public function testRequest()
    {
        $object = $this->mock()->shouldAllowMockingProtectedMethods();

        $response = new Response(200, [], '{"mock":"response"}');
        $object->shouldReceive('performRequest')->once()->with('foo/bar', 'GET', ['query' => ['foo' => 'bar']])->andReturn($response);
        $this->assertSame(['mock' => 'response'], $object->request('foo/bar', 'GET', ['query' => ['foo' => 'bar']]));
    }

    public function testAccessToken()
    {
        $object = $this->mock();
        $this->assertInstanceOf(AccessToken::class, $object->getAccessToken());

        $accessToken = \Mockery::mock(AccessToken::class);
        $object->setAccessToken($accessToken);
        $this->assertSame($accessToken, $object->getAccessToken());
    }

    public function testHttpGet()
    {
        $object = $this->mock();

        $url   = 'http://mock-foo';
        $query = ['foo' => 'bar'];
        $object->shouldReceive('request')->once()->with($url, 'GET', ['query' => $query])->andReturn('mock-result');
        $this->assertSame('mock-result', $object->httpGet($url, $query));
    }

    public function testHttpPost()
    {
        $object = $this->mock();

        $url  = 'http://mock-foo';
        $data = ['foo' => 'bar'];
        $object->shouldReceive('request')->once()->with($url, 'POST', ['form_params' => $data])->andReturn('mock-result');
        $this->assertSame('mock-result', $object->httpPost($url, $data));
    }

    public function testHttpPostJson()
    {
        $object = $this->mock();

        $url   = 'http://mock-foo';
        $query = ['bucket_id' => 'mock-id'];
        $data  = ['foo' => 'bar'];
        $object->shouldReceive('request')->once()->with($url, 'POST', ['query' => $query, 'json' => $data])->andReturn('mock-result');
        $this->assertSame('mock-result', $object->httpPostJson($url, $data, $query));
    }

    public function testHttpClient()
    {
        $app = new ServiceContainer();
        $this->assertInstanceOf(ClientInterface::class, $app['http_client']);

        $client = new Client(['base_uri' => 'http://mock-foo']);
        $app    = new ServiceContainer([], ['http_client' => $client]);
        $this->assertSame($client, $app['http_client']);

        $mock = $this->mock($app);
        $this->assertSame($client, $mock->getHttpClient());
    }

    public function testRetryMiddleware()
    {
        $app         = new ServiceContainer();
        $accessToken = \Mockery::mock(AccessToken::class, [$app]);

        $object = $this->mock($app, $accessToken);
        $func   = $object->retryMiddleware();

        $mockHandler = new MockHandler([
            new Response(200, [], '{"retcode":20002,"retmsg":"accessToken 过期"}'),
            new Response(200, [], '{"retcode":0}')
        ]);

        $accessToken->expects()->refresh();
        $handler  = $func($mockHandler);
        $client   = new Client(['handler' => $handler]);
        $response = $client->request('GET', 'http://mock-url');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"retcode":0}', (string)$response->getBody());

        $accessToken->expects()->refresh();
        $mockHandler = new MockHandler([
            new Response(200, [], '{"retcode":20002,"retmsg":"accessToken 过期"}'),
            new Response(200, [], '{"retcode":20004,"retmsg":"accessToken 无效"}'),
            new Response(200, [], '{"retcode":0}')
        ]);
        $handler     = $func($mockHandler);
        $client      = new Client(['handler' => $handler]);
        $response    = $client->request('GET', 'http://mock-url');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"retcode":20004,"retmsg":"accessToken 无效"}', (string)$response->getBody());

        $app    = new ServiceContainer([
            'http' => [
                'max_retries' => 0,
            ]
        ]);
        $object = $this->mock($app, $accessToken);
    }
}