<?php

namespace Moonpie\Macro\ByteMiniProgram\TemplateMessage;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Support\Arr;
use Moonpie\Macro\ByteMiniProgram\Kernel\BaseClient;
use ReflectionClass;

/**
 * Class Client.
 *
 * @author overtrue <i@overtrue.me>
 */
class Client extends BaseClient
{

    const API_SEND = 'apps/game/template/send';

    /**
     * Attributes.
     * access_token    String    是    服务端API调用标识，获取方法
     * touser    String    是    要发送给用户的open id, open id的获取请参考登录
     * templateId    String    是    在开发者平台配置消息模版后获得的模版id
     * page    String    否    点击消息卡片之后打开的小程序页面地址，空则无跳转
     * form_id    String    是    可以通过<form />组件获得form_id, 获取方法
     * data    dict<String, SubData>    是    模板中填充着的数据，key必须是keyword为前缀
     * @var array
     */
    protected $message = [
        'touser' => '',
        'templateId' => '',
        'page' => '',
        'form_id' => '',
        'data' => [],
    ];

    /**
     * Required attributes.
     *
     * @var array
     */
    protected $required = ['touser', 'templateId', 'form_id'];


    /**
     * 发送模板消息
     *
     * @param array $data
     *
     * @return \Psr\Http\Message\ResponseInterface|\EasyWeChat\Kernel\Support\Collection|array|object|string
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function send($data)
    {
        $params = $this->formatMessage($data);

        $this->restoreMessage();

        return $this->httpPostJson(static::API_SEND, $params);
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    protected function formatMessage(array $data = [])
    {
        $params = array_merge($this->message, $data);

        foreach ($params as $key => $value) {
            if (in_array($key, $this->required, true) && empty($value) && empty($this->message[$key])) {
                throw new InvalidArgumentException(sprintf('Attribute "%s" can not be empty!', $key));
            }

            $params[$key] = empty($value) ? $this->message[$key] : $value;
        }

        $params['data'] = $this->formatData($params['data'] ?? []);

        $token = $this->getAccessToken();
        $params['access_token'] = Arr::get($token->getToken(), $token->getTokenKey());
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

    /**
     * Restore message.
     */
    protected function restoreMessage()
    {
        $this->message = (new ReflectionClass(static::class))->getDefaultProperties()['message'];
    }
}
