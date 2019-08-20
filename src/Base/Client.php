<?php


namespace Sammy1992\Haina\Base;


use Sammy1992\Haina\Core\BaseClient;

class Client extends BaseClient
{
    /**
     * @param $code
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser($code)
    {
        return $this->httpPostJson('access/getUser', [
            'user_code' => $code
        ], [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }
}