<?php
/**
 * Created by Moonpie Studio.
 * User: JohnZhang
 * Date: 2019/6/5
 * Time: 19:14
 */

namespace Moonpie\Macro\ByteMiniPayment\Alipay;


use Moonpie\Macro\ByteMiniPayment\Application;

class Client
{
    /**
     * @var Application 关联的抖音支付程序
     */
    protected $app;
    protected $aopClient;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getAopClient()
    {
        if (is_null($this->aopClient)) {
            $this->aopClient = new \AopClient();
            $this->aopClient->format = $this->app->config->get('alipay.format', 'json');
            $this->aopClient->appId = $this->app->config->alipay['app_id'];
            $this->aopClient->rsaPrivateKeyFilePath = $this->app->config->alipay['rsa_private_file'];
            $this->aopClient->signType = $this->app->config->get('alipay.sign_type', 'RSA');
            $this->aopClient->alipayPublicKey = $this->app->config->alipay['ali_public_file'];
            if ($this->app->inSandbox()) {
                $this->aopClient->gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';
            }
        }
        return $this->aopClient;
    }
}