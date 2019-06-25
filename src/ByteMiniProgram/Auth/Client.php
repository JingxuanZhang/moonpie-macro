<?php
/**
 * moonpie
 */
namespace Moonpie\Macro\ByteMiniProgram\Auth;

use EasyWeChat\Kernel\BaseClient;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;

/**
 * Class Auth.
 *
 * @author mingyoung <mingyoungcheung@gmail.com>
 */
class Client extends BaseClient
{
    protected $baseUri = 'https://developer.toutiao.com/api/';

    /**
     * Get session info by code.
     *
     * @param string $code
     * @param string $anonymousCode 匿名code
     *
     * @return \Psr\Http\Message\ResponseInterface|\EasyWeChat\Kernel\Support\Collection|array|object|string
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function session($code = null, $anonymousCode = null)
    {
        if (empty($code) && empty($anonymousCode)) {
            throw new InvalidConfigException('need code and anonymousCode once at least');
        }
        $params = [
            'appid' => $this->app['config']['app_id'],
            'secret' => $this->app['config']['secret'],
            'js_code' => $code,
        ];
        if (!empty($code)) {
            $params['code'] = $code;
        }
        if (!empty($anonymousCode)) {
            $params['anonymous_code'] = $anonymousCode;
        }

        return $this->httpGet('apps/jscode2session', $params);
    }
}
