<?php
/**
 * Copyright (c) 2018-2019.
 * This file is part of the moonpie production
 *   (c) johnzhang <875010341@qq.com>
 *   This source file is subject to the MIT license that is bundled
 *  with this source code in the file LICENSE.
 */

namespace Moonpie\Macro\Alipay\Kernel;


use Moonpie\Macro\Alipay\Application;
use EasyWeChat\Kernel\Traits\HasHttpRequests;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use EasyWeChat\Kernel\Support;
use Psr\Http\Message\ResponseInterface;

class BaseClient
{
    use HasHttpRequests { request as performRequest; }

    /**
     * @var Application
     */
    protected $app;

    /**
     * BaseClient constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->setHttpClient($this->app['http_client']);
    }

    /**
     * Extra request params.
     *
     * @return array
     */
    protected function prepends()
    {
        return [];
    }

    /**
     * Make a API request.
     *
     * @param string $endpoint
     * @param array  $params
     * @param string $method
     * @param array  $options
     * @param bool   $returnResponse
     *
     * @return \Psr\Http\Message\ResponseInterface|\EasyWeChat\Kernel\Support\Collection|array|object|string
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    protected function request(string $endpoint, array $params = [], $method = 'post', array $options = [], $returnResponse = false)
    {
        $base = [
            'app_id' => $this->app['config']['app_id'],
        ];

        $params = array_filter(array_merge($base, $this->prepends(), $params));

        $secretKey = $this->app->getKey($endpoint);
        if ('HMAC-SHA256' === ($params['sign_type'] ?? 'MD5')) {
            $encryptMethod = function ($str) use ($secretKey) {
                return hash_hmac('sha256', $str, $secretKey);
            };
        } else {
            $encryptMethod = 'md5';
        }
        $base_keys = ['app_id', 'method', 'format', 'charset', 'sign_type', 'sign', 'timestamp',
            'version', 'biz_content'];
        $filter = Support\Arr::except($params, $base_keys);
        $params['biz_content'] = \GuzzleHttp\json_encode($filter);
        $params['sign'] = $this->generate_sign($params, $secretKey, $encryptMethod);
        $base = Support\Arr::only($params, $base_keys);
        $options = array_merge([
            'form_params' => $filter,
            'query' => $base,
        ], $options);

        $this->pushMiddleware($this->logMiddleware(), 'log');

        $response = $this->performRequest($endpoint, $method, $options);

        return $returnResponse ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
    }
    public function generate_sign(array $attributes, $key, $encryptMethod = 'md5')
    {
        //dump($attributes);exit;
        ksort($attributes);
        $build = urldecode(http_build_query($attributes));

        //$attributes['key'] = $key;

        $return = call_user_func_array($encryptMethod, [$build . $key]);
        return $return;
    }

    /**
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        $formatter = new MessageFormatter($this->app['config']['http.log_template'] ?? MessageFormatter::DEBUG);

        return Middleware::log($this->app['logger'], $formatter);
    }

    /**
     * Make a request and return raw response.
     *
     * @param string $endpoint
     * @param array  $params
     * @param string $method
     * @param array  $options
     *
     * @return ResponseInterface
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    protected function requestRaw($endpoint, array $params = [], $method = 'post', array $options = [])
    {
        return $this->request($endpoint, $params, $method, $options, true);
    }

    /**
     * Request with SSL.
     *
     * @param string $endpoint
     * @param array  $params
     * @param string $method
     * @param array  $options
     *
     * @return \Psr\Http\Message\ResponseInterface|\EasyWeChat\Kernel\Support\Collection|array|object|string
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    protected function safeRequest($endpoint, array $params, $method = 'post', array $options = [])
    {
        $options = array_merge([
            'cert' => $this->app['config']->get('cert_path'),
            'ssl_key' => $this->app['config']->get('key_path'),
        ], $options);

        return $this->request($endpoint, $params, $method, $options);
    }

    /**
     * Wrapping an API endpoint.
     *
     * @param string $endpoint
     *
     * @return string
     */
    protected function wrap(string $endpoint): string
    {
        //return $this->app->inSandbox() ? "sandboxnew/{$endpoint}" : $endpoint;
        return $endpoint;
    }

    /**
     * @param $request
     * @param $response
     * @return mixed
     */
    protected function prepareAlipayResponse($request, $response)
    {
        if(!$response) return false;
        $response_key = str_replace('.', '_', $request->getApiMethodName()) . '_response';
        return $response->{$response_key};
    }
}