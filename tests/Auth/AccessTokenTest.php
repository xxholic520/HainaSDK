<?php


namespace Sammy1992\Haina\Tests\Auth;


use Sammy1992\Haina\Auth\AccessToken;
use Sammy1992\Haina\Core\ServiceContainer;
use Sammy1992\Haina\Tests\TestCase;

class AccessTokenTest extends TestCase
{
    public function testGetCredentials()
    {
        $app = new ServiceContainer([
            'bucket_id'     => 'mock-id',
            'bucket_secret' => 'mock-secret'
        ]);

        $mock = \Mockery::mock(AccessToken::class, [$app])->makePartial()->shouldAllowMockingProtectedMethods();

        $this->assertSame([
            'bucket_id'     => 'mock-id',
            'bucket_secret' => 'mock-secret'
        ], $mock->getCredentials());
    }
}