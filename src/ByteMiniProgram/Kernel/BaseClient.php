<?php

namespace Moonpie\Macro\ByteMiniProgram\Kernel;

use EasyWeChat\Kernel\BaseClient as OriginClient;
use EasyWeChat\Kernel\Contracts\AccessTokenInterface;
use Moonpie\Macro\ByteMiniProgram\Application;

/**
 * Class Client.
 */
class BaseClient extends OriginClient
{
    public function __construct(Application $app, AccessTokenInterface $accessToken = null)
    {
        parent::__construct($app, $accessToken);
    }

    protected $baseUri = 'https://developer.toutiao.com/api/';
}
