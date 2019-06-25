<?php
/**
 * Created by Moonpie Studio.
 * User: JohnZhang
 * Date: 2019/6/3
 * Time: 15:15
 */

namespace Moonpie\Macro;

use EasyWeChat\Factory as BasicFactory;
use EasyWeChat\Kernel\Support\Str;

class Factory extends BasicFactory
{
    /**
     * @param string $name
     * @param array  $config
     *
     * @return \EasyWeChat\Kernel\ServiceContainer
     */
    public static function make($name, array $config)
    {
        $namespace = Str::studly($name);
        if(stripos($name, 'byte') === 0) {
            $application = "\\Moonpie\\Macro\\{$namespace}\\Application";
        }else {
            $application = "\\EasyWeChat\\{$namespace}\\Application";
        }

        return new $application($config);
    }
    /**
     * Dynamically pass methods to the application.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::make($name, ...$arguments);
    }
}