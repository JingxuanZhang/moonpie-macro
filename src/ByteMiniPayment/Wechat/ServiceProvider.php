<?php
/**
 * Copyright (c) 2018-2019.
 * This file is part of the moonpie production
 *   (c) johnzhang <875010341@qq.com>
 *   This source file is subject to the MIT license that is bundled
 *  with this source code in the file LICENSE.
 */

namespace Moonpie\Macro\ByteMiniPayment\Wechat;


use Moonpie\Macro\Factory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['wepay'] = function (Container $app) {
            if($app->config->has('wechat')) {
                return Factory::payment($app->config->get('wechat'));
            }
            return null;
        };
    }

}