<?php


namespace Sammy1992\Haina\Tests\Core;


use GuzzleHttp\Psr7\Response;
use Psr\SimpleCache\CacheInterface;
use Sammy1992\Haina\Core\AccessToken;
use Sammy1992\Haina\Core\Exceptions\HttpException;
use Sammy1992\Haina\Core\Exceptions\InvalidArgumentException;
use Sammy1992\Haina\Core\Exceptions\RuntimeException;
use Sammy1992\Haina\Core\ServiceContainer;
use Sammy1992\Haina\Tests\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Simple\FilesystemCache;

class AccessTokenTest extends TestCase
{
    public function testGetToken()
    {
        $mock      = \Mockery::mock(AccessToken::class, [new ServiceContainer()])->makePartial()->shouldAllowMockingProtectedMethods();
        $mockCache = \Mockery::mock(CacheInterface::class);

        $credentials = [
            'foo' => 'foo',
            'bar' => 'bar'
        ];

        $result = [
            'access_token' => 'mock-accesstoken',
            'expires_in'   => 7200
        ];

        $mock->shouldReceive('getCacheKey')->andReturn('mock-cache-key');
        $mock->shouldReceive('getCache')->andReturn($mockCache);
        $mock->shouldReceive('getCredentials')->andReturn($credentials);

        // 存在缓存
        $mockCache->shouldReceive('has')->once()->with('mock-cache-key')->andReturn(true);
        $mockCache->shouldReceive('get')->once()->with('mock-cache-key')->andReturn($result);
        $this->assertSame($result, $mock->getToken());

        // 刷新缓存
        $mockCache->shouldReceive('has')->once()->with('mock-cache-key')->andReturn(false);
        $mockCache->shouldReceive('get')->once()->with('mock-cache-key')->never();
        $mock->shouldReceive('requestToken')->once()->with($credentials)->andReturn($result);
        $mock->shouldReceive('setToken')->once()->with($result['access_token'], $result['expires_in'])->andReturn($mock);
        $this->assertSame($result, $mock->getToken());
    }

    public function testSetToken()
    {
        $mockApp   = \Mockery::mock(ServiceContainer::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $mock      = \Mockery::mock(AccessToken::class, [$mockApp])->makePartial()->shouldAllowMockingProtectedMethods();
        $mockCache = \Mockery::mock(CacheInterface::class);

        $mock->shouldReceive('getCacheKey')->andReturn('mock-cache-key');
        $mock->shouldReceive('getCache')->andReturn($mockCache);

        $mockCache->shouldReceive('has')->once()->with('mock-cache-key')->andReturn(true);
        $mockCache->shouldReceive('set')->once()->with('mock-cache-key', [
            'access_token' => 'mock-token',
            'expires_in'   => 7200
        ], 7200 - 500)->andReturn(true);
        $result = $mock->setToken('mock-token');
        $this->assertSame($mock, $result);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cache fail');
        $mockCache->shouldReceive('has')->once()->with('mock-cache-key')->andReturn(false);
        $mockCache->shouldReceive('set')->once()->with('mock-cache-key', [
            'access_token' => 'mock-token',
            'expires_in'   => 7200
        ], 7200 - 500)->andReturn(false);
        $mock->setToken('mock-token');
    }

    public function testRefresh()
    {
        $mock = \Mockery::mock(AccessToken::class, [new ServiceContainer()])->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getToken')->once()->with(true);
        $token = $mock->refresh();
        $this->assertSame($mock, $token);
    }

    public function testGetEndPoint()
    {
        $app    = \Mockery::mock(ServiceContainer::class)->makePartial();
        $object = new FakeClassAccessToken($app);
        $this->assertSame('foo/bar', $object->getEndPoint());

        $object = \Mockery::mock(AccessToken::class, [$app])->makePartial();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must set endPoint');
        $object->getEndPoint();
    }

    public function testTokenKey()
    {
        $app    = \Mockery::mock(ServiceContainer::class)->makePartial();
        $object = \Mockery::mock(AccessToken::class, [$app])->makePartial();
        $this->assertSame('access_token', $object->getTokenKey());

        $object = \Mockery::mock(FakeClassAccessToken::class)->makePartial();
        $this->assertSame('foo', $object->getTokenKey());
    }

    public function testGetQuery()
    {
        $app    = \Mockery::mock(ServiceContainer::class)->makePartial();
        $object = \Mockery::mock(AccessToken::class, [$app])->makePartial();

        $object->shouldReceive('getToken')->once()->andReturn(['access_token' => 'mock-accesstoken', 'expires_in' => 7200]);
        $object->shouldReceive('getQuery')->once()->passthru();
        $this->assertSame(['access_token' => 'mock-accesstoken'], $object->getQuery());

        $object = \Mockery::mock(FakeClassAccessToken::class, [$app])->makePartial();
        $object->shouldReceive('getToken')->once()->andReturn(['foo' => 'mock-token']);
        $object->shouldReceive('getQuery')->once()->passthru();
        $this->assertSame(['foo' => 'mock-token'], $object->getQuery());
    }

    public function testRequestToken()
    {
        $app    = \Mockery::mock(ServiceContainer::class)->makePartial();
        $object = \Mockery::mock(AccessToken::class, [$app])->makePartial();

        $credentials = [
            'foo' => 'foo',
            'bar' => 'bar'
        ];

        $object->shouldReceive('getEndPoint')->andReturn('foo/bar');
        $object->shouldReceive('request')->once()->with('foo/bar', 'POST', [
            'json' => $credentials
        ])->andReturn(new Response(200, [], '{"access_token":"mock-token"}'));
        $object->shouldReceive('requestToken')->once()->with($credentials)->passthru();
        $this->assertSame(['access_token' => 'mock-token'], $object->requestToken($credentials));

        $object->shouldReceive('request')->once()->with('foo/bar', 'POST', [
            'json' => $credentials
        ])->andReturn(new Response(200, [], '{"err_msg":"err_mock_msg"}'));
        $object->shouldReceive('requestToken')->once()->with($credentials)->passthru();
        try {
            $object->requestToken($credentials);
        } catch (\Exception $exception) {
            $this->assertInstanceOf(HttpException::class, $exception);
            $this->assertSame('Request fail token.', $exception->getMessage());
            $this->assertSame('err_mock_msg', $exception->formattedResponse['err_msg']);
        }
    }

    public function testCache()
    {
        $app    = \Mockery::mock(ServiceContainer::class)->makePartial();
        $object = \Mockery::mock(AccessToken::class, [$app])->makePartial();
        $this->assertInstanceOf(CacheInterface::class, $object->getCache());

        if (\class_exists('Symfony\Component\Cache\Psr16Cache')) {
            $cache = new ArrayAdapter();
        } else {
            $cache = new FilesystemCache();
        }

        $app['cache'] = function () use ($cache) {
            return $cache;
        };

        $object = \Mockery::mock(AccessToken::class, [$app])->makePartial();
        $this->assertInstanceOf(CacheInterface::class, $object->getCache());
    }
}

class FakeClassAccessToken extends AccessToken
{
    protected $tokenKey = 'foo';

    protected $endPoint = 'foo/bar';

    protected function getCredentials(): array
    {
        return [
            'bucket_id'     => 123,
            'bucket_secret' => 'foo'
        ];
    }
}