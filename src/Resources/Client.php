<?php

namespace Sammy1992\Haina\Resources;


use Sammy1992\Haina\Core\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取组织部门
     *
     * @param string $property_id
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDepartmentTree(string $property_id)
    {
        return $this->httpPostJson('resources/qy/getDepartmentTree', compact('property_id'), [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }
}