<?php
/**
 * Copyright (c) 2018-2019.
 * This file is part of the moonpie production
 *   (c) johnzhang <875010341@qq.com>
 *   This source file is subject to the MIT license that is bundled
 *  with this source code in the file LICENSE.
 */

namespace Moonpie\Macro\Alipay\Transfer;


use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    public function register(Container $app)
    {
        $app['transfer'] = function($app) {
            return new Client($app);
        };
    }
}