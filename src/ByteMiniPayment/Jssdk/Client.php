<?php

/*
 * This file is part of the moonpie/macro.
 *
 * (c) moonpie<875010341@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * app_id    string    required    N/A    是    支付分配给业务方的 id
 * method    string    required    N/A    否    固定值 "tp.trade.confirm"
 * sign    string    required    N/A    否    商户签名
 * sign_type    string    required    N/A    是    签名算法，暂支持 MD5
 * timestamp    string    required    N/A    是    发送请求的时间戳
 * trade_no    string    required    N/A    是    支付订单号
 * merchant_id    string    required    N/A    是    商户 id
 * uid    string    required    N/A    是    用户的唯一标识 id，开发者请传 openid，获取方法
 * total_amount    number    required    0    是    订单金额，单位为分
 * pay_channel    string    required    N/A    否    支付渠道，目前只支持支付宝，值为 "ALIPAY_NO_SIGN"
 * pay_type    string    required    N/A    否    支付方式，目前只支持支付宝，值为 "ALIPAY_APP"
 * risk_info    string    required    N/A    否    风控信息，标准 json 格式字符串(JSON.stringify({ip: "...."}))，目前需要传入用户的真实 IP
 * params    string    required    N/A    是    传递给支付方的支付信息，标准 json 格式字符串(JSON.stringify({url: "...."}))，不同的支付方参数格式不一样
 * return_url    string    optional    N/A    否    (支付宝)支付完成返回的地址
 * show_url    string    optional    N/A    否    (支付宝)支付失败返回的地址
 */

namespace Moonpie\Macro\ByteMiniPayment\Jssdk;

use EasyWeChat\Kernel\BaseClient;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use Moonpie\Macro\ByteMiniPayment\Application;
use EasyWeChat\Kernel\Support;

/**
 * 这里模拟使用抖音的前端调用支付功能
 */
class Client extends BaseClient
{
    const PAY_SERVICE_PLATFORM = 1;
    const PAY_SERVICE_ALIPAY = 4;
    const PAY_SERVICE_WEPAY = 3;
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 支付宝支付,适用于tt.requestPayment需要获取的数据信息
     * @param string $prepayId
     * @param string 用户openid
     * @param int 支付金额，单位分
     * @param array $request 同步支付宝的相关业务参数
     * @param null $returnUrl 成功后的返回地址
     * @param null $showUrl 失败后返回的地址
     * @return array
     */
    public function alipayRequestConfig(string $prepayId, string $openid, int $amount, array $request, $notifyUrl = null, $returnUrl = null, $showUrl = null): array
    {
        $params = [
            'app_id' => $this->app['config']->app_id,
            'method' => 'tp.trade.confirm',
            'sign_type' => 'MD5',
            'timestamp' => time(),
            'trade_no' => $prepayId,
            'merchant_id' => $this->app['config']->mch_id,
            'uid' => $openid,
            'total_amount' => $amount,
            'pay_channel' => 'ALIPAY_NO_SIGN',
            'pay_type' => 'ALIPAY_APP',
            'risk_info' => json_encode(['ip' => Support\get_client_ip()]),
        ];
        if ($returnUrl) {
            $params['return_url'] = $returnUrl;
        }
        if ($showUrl) {
            $params['show_url'] = $showUrl;
        }
        $base_request = [
            'timestamp' => $params['timestamp'],
            'notify_url' => $notifyUrl ?? $this->app['config']->get('alipay.notify_url'),
        ];
        $request['total_amount'] = bcdiv($amount, 100, 2);
        $params['params'] = \GuzzleHttp\json_encode([
            //'url' => http_build_query($this->prepareAliParams($base_request, $request)),
            'url' => $this->prepareAliParams($base_request, $request),
        ]);

        $params['sign'] = $this->generate_sign_old($params, $this->app['config']->key, 'md5');
        $params['timestamp'] = strval($params['timestamp']);

        return $params;
    }

    protected function generate_sign_old(array $params, $key, $encryptMethod)
    {
        //首先过滤需要签名的字段
        $keys = ['app_id', 'sign_type', 'timestamp', 'trade_no', 'merchant_id', 'uid',
            'total_amount', 'params'];
        $sign_params = Support\Arr::only($params, $keys);
        ksort($sign_params);
        $build = urldecode(http_build_query($sign_params));
        return call_user_func_array($encryptMethod, [$build . $key]);
    }

    /**
     * @link https://docs.open.alipay.com/204/105465/
     * @return string 参数的json字符串
     */
    protected function prepareAliParams($base_request, $business)
    {
        /** @var \AopClient $alipay */
        $alipay = $this->app->alipay->getAopClient();
        $request = new \AlipayTradeAppPayRequest();
        $request->setNotifyUrl($base_request['notify_url']);
        $request->setProdCode('QUICK_MSECURITY_PAY');
        $business['product_code'] = 'QUICK_MSECURITY_PAY';
        $request->setBizContent(json_encode($business, JSON_UNESCAPED_UNICODE));
        return $alipay->sdkExecute($request);
    }

    /**
     * 根据字节跳动小程序tt.pay接口生成指定的返回数据
     * @param int $service
     * @param string $openid
     * @param int $amount
     * @param array $request
     * @param null $notifyUrl
     * @return array
     * @throws InvalidConfigException
     */
    public function platformRequestConfig(int $service, string $openid, int $amount, array $request, $notifyUrl = null): array
    {
        $now = time();
        $params = [
            'merchant_id' => $this->app->config['mch_id'],
            'app_id' => $this->app->config['app_id'],
            'sign_type' => 'MD5',
            'timestamp' => strval($now),
            'version' => '2.0',
            'trade_type' => 'H5', 'product_code' => 'pay', 'payment_type' => 'direct',
            'uid' => $openid, 'total_amount' => $amount, 'currency' => 'CNY',
            'subject' => '', 'body' => '', 'out_order_no' => '',
            'trade_time' => $now, 'valid_time' => 600, 'notify_url' => $notifyUrl,
        ];
        if(empty($params['notify_url'])) $params['notify_url'] = 'moonpie-alipay';
        $params = array_merge($request, $params);
        //验证模式下的必要信息
        switch ($service) {
            case static::PAY_SERVICE_ALIPAY:
                if(empty($params['alipay_url'])) {
                    throw new InvalidConfigException('When Use Alipay, Please Provider The "alipay_url" Field');
                }
                break;
            case static::PAY_SERVICE_WEPAY:
                if(empty($params['wx_url'])) {
                    throw new InvalidConfigException('When Use Wechat Payment, Please Provider The "wx_url" Field');
                }
                $params['wx_type'] = 'MWEB';
                break;
            default:
                if(empty($params['alipay_url']) && empty($params['wx_url'])) {
                    throw new InvalidConfigException('When Use Common Platform Payment, Please Provider The "alipay_url" or "wx_url" Field');
                }
                if(!empty($params['wx_url'])) $params['wx_type'] = 'MWEB';
                break;
        }
        $params['sign'] = $this->generate_sign_new($params, $this->app->config['key'], 'md5');
//risk_info	string	required	否	N/A
    }
    protected function generate_sign_new(array $params, string $key, string $encryptMethod): string
    {
        $except_keys = ['risk_info', 'sign'];
        $sign_params = array_filter(Support\Arr::except($params, $except_keys));
        ksort($sign_params);
        $build = urldecode(http_build_query($sign_params));
        return call_user_func_array($encryptMethod, [$build . $key]);
    }

}
