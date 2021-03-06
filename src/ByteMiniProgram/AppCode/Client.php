<?php

namespace Moonpie\Macro\ByteMiniProgram\AppCode;

use EasyWeChat\Kernel\Http\StreamResponse;
use Moonpie\Macro\ByteMiniProgram\Kernel\BaseClient;

class Client extends BaseClient
{
    /**
     * Get AppCode.
     *
     * @param string $path
     * @param array  $optional
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     */
    public function get(string $path = '', array $optional = [])
    {
        $params = array_merge([
            'path' => $path,
        ], $optional);
        $token = $this->getAccessToken();
        $params['access_token'] = $token->getToken()[$token->getTokenKey()];

        return $this->getStream('apps/qrcode', $params);
    }

    /**
     * Get stream.
     *
     * @param string $endpoint
     * @param array  $params
     *
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|StreamResponse|string
     */
    protected function getStream(string $endpoint, array $params)
    {
        $response = $this->requestRaw($endpoint, 'POST', ['json' => $params]);

        if (false !== stripos($response->getHeaderLine('Content-Type'), 'image/png')) {
            return StreamResponse::buildFromPsrResponse($response);
        }

        return $this->castResponseToType($response, $this->app['config']->get('response_type'));
    }
}
