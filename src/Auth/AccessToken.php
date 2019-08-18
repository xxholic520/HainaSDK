<?php


namespace Sammy1992\Haina\Auth;


class AccessToken extends \Sammy1992\Haina\Core\AccessToken
{
    protected $endPoint = 'access/getToken';

    protected function getCredentials(): array
    {
        return [
            'bucket_id'     => $this->app['config']['bucket_id'],
            'bucket_secret' => $this->app['config']['bucket_secret']
        ];
    }
}