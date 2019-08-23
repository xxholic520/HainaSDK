<?php


namespace Sammy1992\Haina\Property;


use Sammy1992\Haina\Core\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取小区楼栋结构列表
     * @param string $property_id 物业ID
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCommunityList(string $property_id)
    {
        return $this->httpPostJson('resources/getCommunityList', compact('property_id'), [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }

    /**
     * 获取楼栋地址树
     * @param string $property_id 物业ID
     * @param string $community_id 小区楼栋结构ID
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCommunityAddressTree(string $property_id, string $community_id)
    {
        return $this->httpPostJson('resources/getCommunityAddressTree', compact('property_id', 'community_id'), [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }

    /**
     * 按照层级结构查询楼栋
     * @param string $property_id 物业ID
     * @param string $community_id 小区楼栋结构ID
     * @param string|null $parent_full_id 父节点full_id
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCommunityAddressLayerList(string $property_id, string $community_id, ?string $parent_full_id = null)
    {
        $data = compact('property_id', 'community_id');
        if ($parent_full_id) $data = array_merge($data, compact('parent_full_id'));
        return $this->httpPostJson('resources/getCommunityAddressLayerList', $data, [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }

    /**
     * 批量获取已认证的业主信息
     * @param string $property_id
     * @param string|null $community_id
     * @param array $search
     * @param array $page
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function residentSearch(string $property_id, ?string $community_id = null, array $search = [], array $page = [])
    {
        $data = compact('property_id');
        if ($community_id) $data = array_merge($data, compact('community_id'));
        if (array_key_exists('type', $search)) $data['search_type'] = $search['type'];
        if (array_key_exists('items', $search) && is_array($search['items'])) {
            $data['search_item'] = $search['items'];
        }
        if (!empty($page)) $data['page'] = $page;

        return $this->httpPostJson('resources/resident_search', $data, [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }

    /**
     * 获取物业所属服务号详情
     * @param string $property_id 物业ID
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getInfo(string $property_id)
    {
        return $this->httpPostJson('resources/getProperty', compact('property_id'), [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }
}