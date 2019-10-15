<?php
/*
 *  Copyright (c) 2018-2019.
 *  This file is part of the moonpie production
 *  (c) johnzhang <875010341@qq.com>
 *  This source file is subject to the MIT license that is bundled
 *  with this source code in the file LICENSE.
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
 * @property \Moonpie\Macro\ByteMiniProgram\ContentSecurity\Client $content_security
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
        ContentSecurity\ServiceProvider::class,
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
