<?php


namespace Moonpie\Macro\ByteMiniPayment\Transfer;


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