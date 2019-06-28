<?php
/**
 * Created by Moonpie Studio.
 * User: JohnZhang
 * Date: 2019/6/27
 * Time: 16:43
 */

namespace Moonpie\Macro\ByteMiniProgram\Storage;


use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider
 * 注册抖音小程序中数据缓存相关服务接口数据
 * @see https://microapp.bytedance.com/docs/server/storage/setStorage.html
 * @package Moonpie\Macro\ByteMiniProgram\Storage
 */
class ServiceProvider implements ServiceProviderInterface
{

    public function register(Container $app)
    {
        if(!isset($app['storage'])){
            $app['storage'] = function($target) {
                return new Client($target);
            };
        }
    }
}