<?php


namespace Sammy1992\Haina\TemplateMessage;


use Sammy1992\Haina\Core\BaseClient;
use Sammy1992\Haina\Core\Exceptions\InvalidArgumentException;

class Client extends BaseClient
{
    /**
     * @var array
     */
    protected $message = [
        'resident_id'   => '',
        'template_no'   => '',
        'url'           => '',
        'template_data' => []
    ];

    /**
     * @var array
     */
    protected $required = ['touser', 'property_id', 'template_no'];

    /**
     * send message
     *
     * @param       $property_id
     * @param array $data
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send($property_id, array $data = [])
    {
        $data = $this->formatData($data);

        return $this->httpPostJson('ability/templateSend', array_merge(compact('property_id'), $data), [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }

    /**
     * 企业号推送模板消息
     *
     * @param       $property_id
     * @param array $data
     *
     * @return mixed
     */
    public function sendToQy($property_id, array $data = [])
    {
        return $this->httpPostJson('ability/qy/qyMessageSend', array_merge(compact('property_id'), $data), [
            'agent_id' => $this->app['config']['agent_id']
        ]);
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \Sammy1992\Haina\Core\Exceptions\InvalidArgumentException
     */
    protected function formatMessage(array $data)
    {
        $params = array_merge($this->message, $data);

        foreach ($params as $key => $value) {
            if (in_array($key, $this->required, true) && empty($value) && empty($this->message[$key])) {
                throw new InvalidArgumentException(sprintf('Attribute "%s" can not be empty!', $key));
            }

            $params[$key] = empty($value) ? $this->message[$key] : $value;
        }

        $params['template_data'] = $this->formatData($params['data']);

        return $params;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function formatData(array $data)
    {
        $formatted = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value['value'])) {
                    $formatted[$key] = $value;

                    continue;
                }

                if (count($value) >= 2) {
                    $value = [
                        'value' => $value[0],
                        'color' => $value[1],
                    ];
                }
            } else {
                $value = [
                    'value' => strval($value),
                ];
            }

            $formatted[$key] = $value;
        }

        return $formatted;
    }
}