<?php

/*
 * This file is part of the moonpie/macro.
 * (c) moonpie<875010341@qq.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Moonpie\Macro\ByteMiniPayment;

use EasyWeChat\Kernel\ServiceContainer;

/**
 * Class Application.
 *
 * @property \Moonpie\Macro\ByteMiniPayment\Jssdk\Client             $jssdk
 * @property \Moonpie\Macro\Alipay\Application                       $alipay
 * @property \EasyWeChat\Payment\Application                         $wepay
 *
 * @method mixed pay(array $attributes)
 * @method mixed authCodeToOpenid(string $authCode)
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        Jssdk\ServiceProvider::class,
        Alipay\ServiceProvider::class,
        Wechat\ServiceProvider::class,
    ];

    /**
     * @var array
     */
    protected $defaultConfig = [
        'http' => [
            'base_uri' => 'https://tp-pay.snssdk.com',
        ],
    ];



    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this['base'], $name], $arguments);
    }
}
