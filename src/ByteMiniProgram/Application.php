<?php
/**
 * Created by Moonpie Studio.
 * User: JohnZhang
 * Date: 2019/6/3
 * Time: 15:24
 */

namespace Moonpie\Macro\ByteMiniProgram;


use EasyWeChat\Kernel\ServiceContainer;

/**
 * Class Application.
 *
 * @author mingyoung <mingyoungcheung@gmail.com>
 *
 * @property \Moonpie\Macro\ByteMiniProgram\Auth\AccessToken           $access_token
 * @property \Moonpie\Macro\ByteMiniProgram\Auth\Client                $auth
 * @property \EasyWeChat\MiniProgram\Encryptor                  $encryptor
 * @property \Moonpie\Macro\ByteMiniProgram\Storage\Client $storage
 * @property \Moonpie\Macro\ByteMiniProgram\AppCode\Client $app_code
 * @property \Moonpie\Macro\ByteMiniProgram\TemplateMessage\Client $template_message
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        Auth\ServiceProvider::class,
        Storage\ServiceProvider::class,
        TemplateMessage\ServiceProvider::class,
        AppCode\ServiceProvider::class,
    ];

    /**
     * Handle dynamic calls.
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->base->$method(...$args);
    }
}