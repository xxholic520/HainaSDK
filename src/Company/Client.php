<?php


namespace Sammy1992\Haina\Company;

use Sammy1992\Haina\Core\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取员工列表
     *
     * @param string $property_id 物业ID
     * @param int $begin 分页数据开始下标，从1开始
     * @param int $limit 从下标起获取数据个数，最大不超过50
     * @param string|null $department_id 所属部门id
     * @param string|null $keyword 手机号或者姓名模糊查询
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getEmployeeList(string $property_id, int $begin = 1, int $limit = 20, ?string $department_id = null, ?string $keyword = null)
    {
        $data = [
            'property_id' => $property_id,
            'begin_index' => $begin,
            'size'        => $limit
        ];
        if (!is_null($department_id)) $data['department_id'] = $department_id;
        if (!is_null($keyword)) $data['keyword'] = $keyword;

        return $this->httpPostJson('resources/qy/getEmployeeList', $data, [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }
}