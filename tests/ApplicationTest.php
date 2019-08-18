<?php


namespace Sammy1992\Haina\Tests;


use Sammy1992\Haina\Application;

class ApplicationTest extends TestCase
{
    public function testCall()
    {
        $app = new Application([
            'bucket_id'     => 'mock-id',
            'bucket_secret' => 'mock-secret'
        ]);

        $this->assertInstanceOf(\Sammy1992\Haina\Auth\AccessToken::class, $app->access_token);
    }
}