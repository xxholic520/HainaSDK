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
        $agentId = $this->app['config']['agentId'];
        return $this->request("access/getUser?agent_id=$agentId", 'POST', ['json' => [
            'user_code' => $code
        ]]);
    }
}