<?php


namespace Sammy1992\Haina\Tests\Core;


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
}