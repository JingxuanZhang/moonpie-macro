<?php
/**
 * Copyright (c) 2018-2019.
 * This file is part of the moonpie production
 * (c) johnzhang <875010341@qq.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Moonpie\Macro\ByteMiniProgram\Storage;


use EasyWeChat\Kernel\BaseClient;
use EasyWeChat\Kernel\Contracts\AccessTokenInterface;
use Moonpie\Macro\ByteMiniProgram\Application;

/**
 * Class Client
 * 处理抖音用户缓存相关的接口
 * @package Moonpie\Macro\ByteMiniProgram\Storage
 */
class Client extends BaseClient
{
    public function __construct(Application $app, AccessTokenInterface $accessToken = null)
    {
        parent::__construct($app, $accessToken);
    }

    protected $baseUri = 'https://developer.toutiao.com/api/';

    /**
     * @see https://microapp.bytedance.com/docs/server/storage/setStorage.html
     * @param $openid string 设置的用户openid
     * @param $data array 设置的数据信息
     * @param string $sessionKey 用户登录态会话密钥
     * @param string $method 签名算法
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function setStorage($openid, $data, $sessionKey, $method = 'hmac_sha256')
    {
        $post = [
            'openid' => $openid, 'sig_method' => $method,
            'signature' => $this->getSignature($data, $sessionKey, $method),
            'kv_list' => $data, 'access_token' => $this->app->access_token->getToken(),
        ];
        return $this->request('/apps/set_user_storage', 'POST', ['form_params' => $post, 'body' => ['kv_list' => $data]]);
    }

    /**
     * @see
     * @param $openid string 限制的用户openid
     * @param $key string 删除的索引
     * @param $sessionKey string 使用的会话凭证
     * @param string $method
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function removeStorage($openid, $key, $sessionKey, $method = 'hmac_sha256')
    {
        $post = [
            'openid' => $openid, 'sig_method' => $method,
            'signature' => $this->getSignature(['key' => $key], $sessionKey, $method),
            'key' => $key,
        ];
        return $this->request('/apps/remove_user_storage', 'POST', ['form_params' => $post, 'body' => ['key' => $key]]);
    }
    protected function getSignature($params, $key, $method = 'hmac_sha256')
    {
        if(is_array($params) || $params instanceof \Traversable){
            $data = \GuzzleHttp\json_encode($params);
        }else {
            $data = strval($params);
        }
        return hash_hmac('sha256', $data, $key);
    }
}