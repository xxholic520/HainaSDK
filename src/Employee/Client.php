<?php

namespace Sammy1992\Haina\Employee;


use Sammy1992\Haina\Core\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取员工列表
     *
     * @param string      $property_id
     * @param string|null $department_id
     * @param int         $begin
     * @param int         $size
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getEmployeeList(string $property_id, ?string $department_id = null, int $begin = 1, int $size = 20)
    {
        $data = compact('property_id');
        if ($department_id) $data = array_merge($data, compact('department_id'));
        return $this->httpPostJson('resources/qy/getEmployeeList', array_merge($data, [
            'begin_index' => $begin,
            'size'        => $size
        ]), [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }

    /**
     * 获取员工详情
     *
     * @param string $property_id 物业ID
     * @param string $staff_id    员工唯一帐号标识
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getEmployeeDetail(string $property_id, string $staff_id)
    {
        $data = compact('property_id', 'staff_id');
        return $this->httpPostJson('resources/qy/getEmployeeDetail', $data, [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }
}