<?php
/*
 *  Copyright (c) 2018-2019.
 *  This file is part of the moonpie production
 *  (c) johnzhang <875010341@qq.com>
 *  This source file is subject to the MIT license that is bundled
 *  with this source code in the file LICENSE.
*/

namespace Moonpie\Macro;

use EasyWeChat\Factory as BasicFactory;
use EasyWeChat\Kernel\Support\Str;

/**
 * Class Factory
 * @method static ByteMiniPayment\Application        byteMiniPayment(array $config)
 * @method static ByteMiniProgram\Application        byteMiniProgram(array $config)
 * @package Moonpie\Macro
 */
class Factory extends BasicFactory
{
    /**
     * @param string $name
     * @param array $config
     *
     * @return \EasyWeChat\Kernel\ServiceContainer
     */
    public static function make($name, array $config)
    {
        $namespace = Str::studly($name);
        $first_class = "\\Moonpie\\Macro\\{$namespace}\\Application";
        if (class_exists($first_class)) {
            $application = $first_class;
        } else {
            $application = "\\EasyWeChat\\{$namespace}\\Application";
        }

        return new $application($config);
    }

    /**
     * Dynamically pass methods to the application.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::make($name, ...$arguments);
    }
}
