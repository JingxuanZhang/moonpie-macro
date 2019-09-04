<?php

namespace Moonpie\Macro\ByteMiniProgram\TemplateMessage;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider.
 *
 * @author overtrue <i@overtrue.me>
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['template_message'] = function ($app) {
            return new Client($app);
        };
    }
}
