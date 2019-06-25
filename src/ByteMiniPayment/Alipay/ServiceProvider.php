<?php
/**
 * Created by Moonpie Studio.
 * User: JohnZhang
 * Date: 2019/6/5
 * Time: 19:11
 */

namespace Moonpie\Macro\ByteMiniPayment\Alipay;


use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['alipay'] = function (Container $app) {
            return new Client($app);
        };
    }

}