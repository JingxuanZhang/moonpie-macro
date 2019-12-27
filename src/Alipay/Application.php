<?php
/**
 * Copyright (c) 2018-2019.
 * This file is part of the moonpie production
 *   (c) johnzhang <875010341@qq.com>
 *   This source file is subject to the MIT license that is bundled
 *  with this source code in the file LICENSE.
 */

namespace Moonpie\Macro\Alipay;


use EasyWeChat\Kernel\ServiceContainer;

/**
 * Class Application
 * @package Moonpie\Macro\ByteMiniPayment\Alipay
 * @property Order\Client $order
 * @property Transfer\Client $transfer
 * @property Refund\Client $refund
 */
class Application extends ServiceContainer
{
    protected $aopClient;
    protected $providers = [
        Order\ServiceProvider::class,
        Refund\ServiceProvider::class,
        Transfer\ServiceProvider::class,
    ];
    public function getAopClient()
    {
        if (is_null($this->aopClient)) {
            $this->aopClient = new \AopClient();
            $this->aopClient->format = $this->config->get('format', 'json');
            $this->aopClient->appId = $this->config->app_id;
            $this->aopClient->rsaPrivateKeyFilePath = $this->config['rsa_private_file'];
            $this->aopClient->signType = $this->config->get('sign_type', 'RSA');
            $this->aopClient->alipayPublicKey = $this->config['ali_public_file'];
            if ($this->app->inSandbox()) {
                $this->aopClient->gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';
            }
        }
        return $this->aopClient;
    }
}