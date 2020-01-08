<?php
/**
 * Copyright (c) 2018-2019.
 * This file is part of the moonpie production
 *   (c) johnzhang <875010341@qq.com>
 *   This source file is subject to the MIT license that is bundled
 *  with this source code in the file LICENSE.
 */

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Moonpie\Macro\Alipay\Notify;

use Moonpie\Macro\Alipay\Application;
use Closure;
use EasyWeChat\Kernel\Exceptions\Exception;
use EasyWeChat\Kernel\Support;
use EasyWeChat\Payment\Kernel\Exceptions\InvalidSignException;
use Symfony\Component\HttpFoundation\Response;

abstract class Handler
{
    const SUCCESS = 'success';
    const FAIL = 'fail';

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $message;

    /**
     * @var string|null
     */
    protected $fail;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Check sign.
     * If failed, throws an exception.
     *
     * @var bool
     */
    protected $check = true;

    /**
     * Respond with sign.
     *
     * @var bool
     */
    protected $sign = false;

    /**
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Handle incoming notify.
     *
     * @param \Closure $closure
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    abstract public function handle(Closure $closure);

    /**
     * @param string $message
     */
    public function fail(string $message)
    {
        $this->fail = $message;
    }

    /**
     * @param array $attributes
     * @param bool  $sign
     *
     * @return $this
     */
    public function respondWith(array $attributes, bool $sign = false)
    {
        $this->attributes = $attributes;
        $this->sign = $sign;

        return $this;
    }

    /**
     * Build xml and return the response to WeChat.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse(): Response
    {
        if(!is_null($this->fail)){
            $flag = static::FAIL;
            $this->app->logger->debug($this->fail, $this->message);
        }else {
            $flag = static::SUCCESS;
        }

        return new Response($flag);
    }

    /**
     * Return the notify message from request.
     *
     * @return array
     *
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function getMessage(): array
    {
        if (!empty($this->message)) {
            return $this->message;
        }

        try {
            $message = $this->app->request->request->all();
        } catch (\Throwable $e) {
            throw new Exception('Invalid request Data: '.$e->getMessage(), 400);
        }

        if (!is_array($message) || empty($message)) {
            throw new Exception('Invalid request Data.', 400);
        }

        if ($this->check) {
            $this->validate($message);
        }

        return $this->message = $message;
    }

    /**
     * Decrypt message.
     *
     * @param string $key
     *
     * @return string|null
     *
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function decryptMessage(string $key)
    {
        $message = $this->getMessage();
        if (empty($message[$key])) {
            return null;
        }

        return Support\AES::decrypt(
            base64_decode($message[$key], true), md5($this->app['config']->key), '', OPENSSL_RAW_DATA, 'AES-256-ECB'
        );
    }

    /**
     * Validate the request params.
     *
     * @param array $message
     *
     * @throws \EasyWeChat\Payment\Kernel\Exceptions\InvalidSignException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    protected function validate(array $message)
    {
        $client = $this->app->getAopClient();
        $result = $client->rsaCheckV1($message, $client->alipayPublicKey, $client->signType);
        if (true !== $result) {
            throw new InvalidSignException();
        }
    }

    /**
     * @param mixed $result
     */
    protected function strict($result)
    {
        if (true !== $result && is_null($this->fail)) {
            $this->fail(strval($result));
        }
    }
}
