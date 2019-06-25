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
 * app_id	string	required	N/A	是	支付分配给业务方的 id
method	string	required	N/A	否	固定值 "tp.trade.confirm"
sign	string	required	N/A	否	商户签名
sign_type	string	required	N/A	是	签名算法，暂支持 MD5
timestamp	string	required	N/A	是	发送请求的时间戳
trade_no	string	required	N/A	是	支付订单号
merchant_id	string	required	N/A	是	商户 id
uid	string	required	N/A	是	用户的唯一标识 id，开发者请传 openid，获取方法
total_amount	number	required	0	是	订单金额，单位为分
pay_channel	string	required	N/A	否	支付渠道，目前只支持支付宝，值为 "ALIPAY_NO_SIGN"
pay_type	string	required	N/A	否	支付方式，目前只支持支付宝，值为 "ALIPAY_APP"
risk_info	string	required	N/A	否	风控信息，标准 json 格式字符串(JSON.stringify({ip: "...."}))，目前需要传入用户的真实 IP
params	string	required	N/A	是	传递给支付方的支付信息，标准 json 格式字符串(JSON.stringify({url: "...."}))，不同的支付方参数格式不一样
return_url	string	optional	N/A	否	(支付宝)支付完成返回的地址
show_url	string	optional	N/A	否	(支付宝)支付失败返回的地址
 */

namespace Moonpie\Macro\ByteMiniPayment\Jssdk;

use Moonpie\Macro\ByteMiniPayment\Application;
use EasyWeChat\Kernel\Support;

/**
 * 这里模拟使用抖音的前端调用支付功能
 */
class Client
{
    protected $app;
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 支付宝支付
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
        if($returnUrl) {
            $params['return_url'] = $returnUrl;
        }
        if($showUrl) {
            $params['show_url'] = $showUrl;
        }
        $base_request = [
            'timestamp' => $params['timestamp'],
            'notify_url' => $notifyUrl ?? $this->app['config']['notify_url'],
        ];
        $request['total_amount'] = bcdiv($amount, 100, 2);
        $params['params'] = \GuzzleHttp\json_encode([
            //'url' => http_build_query($this->prepareAliParams($base_request, $request)),
            'url' => $this->prepareAliParams($base_request, $request),
        ]);

        $params['sign'] = $this->generate_sign($params, $this->app['config']->key, 'md5');
        $params['timestamp'] = strval($params['timestamp']);

        return $params;
    }
    protected function generate_sign(array $params, $key, $encryptMethod)
    {
        //首先过滤需要签名的字段
        $keys = ['app_id', 'sign_type', 'timestamp', 'trade_no', 'merchant_id', 'uid',
            'total_amount', 'params'];
        $sign_params = Support\Arr::only($params, $keys);
        ksort($sign_params);
        $build = urldecode(http_build_query($sign_params));
        return call_user_func_array($encryptMethod, [ $build . $key ]);
    }

    /**
     * app_id	String	是	32	支付宝分配给开发者的应用ID	2014072300007148
    method	String	是	128	接口名称	alipay.trade.app.pay
    format	String	否	40	仅支持JSON	JSON
    charset	String	是	10	请求使用的编码格式，如utf-8,gbk,gb2312等	utf-8
    sign_type	String	是	10	商户生成签名字符串所使用的签名算法类型，目前支持RSA2和RSA，推荐使用RSA2	RSA2
    sign	String	是	256	商户请求参数的签名串，详见签名	详见示例
    timestamp	String	是	19	发送请求的时间，格式"yyyy-MM-dd HH:mm:ss"	2014-07-24 03:07:50
    version	String	是	3	调用的接口版本，固定为：1.0	1.0
    notify_url	String	是	256	支付宝服务器主动通知商户服务器里指定的页面 http/https 路径。建议商户使用 https	https://api.xx.com/receive_notify.htm
    biz_content	String	是	-	业务请求参数的集合，最大长度不限，除公共参数外所有请求参数都必须放在这个参数中传递，具体参照各产品快速接入文档

    业务参数
    参数	类型	是否必填	最大长度	描述	示例值
    body	String	否	128	对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。	Iphone6 16G
    subject	String	是	256	商品的标题/交易标题/订单标题/订单关键字等。	大乐透
    out_trade_no	String	是	64	商户网站唯一订单号	70501111111S001111119
    timeout_express	String	否	6	该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。
    注：若为空，则默认为15d。	90m
    total_amount	String	是	9	订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]	9.00
    product_code	String	是	64	销售产品码，商家和支付宝签约的产品码，为固定值 QUICK_MSECURITY_PAY	QUICK_MSECURITY_PAY
    goods_type	String	否	2	商品主类型：0—虚拟类商品；1—实物类商品
    注：虚拟类商品不支持使用花呗渠道	0
    passback_params	String	否	512	公用回传参数，如果请求时传递了该参数，则返回给商户时会回传该参数。支付宝会在异步通知时将该参数原样返回。本参数必须进行 UrlEncode 之后才可以发送给支付宝	merchantBizType%3d3C%26merchantBizNo%3d2016010101111
    promo_params	String	否	512	优惠参数
    注：仅与支付宝协商后可用	{"storeIdType":"1"}
    extend_params	String	否		业务扩展参数，详见下表的 业务扩展参数说明	{"sys_service_provider_id":"2088511833207846"}
    enable_pay_channels	String	否	128	可用渠道，用户只能在指定渠道范围内支付
    当有多个渠道时用“,”分隔
    注：与 disable_pay_channels 互斥	pcredit,moneyFund,debitCardExpress
    disable_pay_channels	String	否	128	禁用渠道，用户不可用指定渠道支付
    当有多个渠道时用“,”分隔
    注：与 enable_pay_channels 互斥	pcredit,moneyFund,debitCardExpress
    store_id	String	否	32	商户门店编号。该参数用于请求参数中以区分各门店，非必传项。	NJ_001
    ext_user_info	ExtUserInfo	否		外部指定买家，详见外部用户ExtUserInfo参数说明
     * @link https://docs.open.alipay.com/204/105465/
     * @return string 参数的json字符串
     */
    protected function prepareAliParams($base_request, $business)
    {
        /** @var \AopClient $alipay */
        $alipay = $this->app->alipay->getAopClient();
        if(1 > 2) {
            $base = [
                'app_id' => $alipay->appId,
                'method' => 'alipay.trade.app.pay',
                'format' => $alipay->format,
                'charset' => 'utf-8',
                'sign_type' => $alipay->signType,
                'sign' => '',
                'timestamp' => date('Y-m-d H:i:s', $base_request['timestamp']),
                'version' => '1.0',
                'notify_url' => $base_request['notify_url'],
                'biz_content' => '',
            ];
            /*$business = [
                'body' => $request['body'],
                'subject' => $request['subject'],
                'out_trade_no' => $request['out_trade_no'],
                'timeout' => $request['timeout'], 'total_amount' => $request['total_amount'],
                'product_code' => $request['product_code'],
                'goods_type' => 0, 'passback_params' => $request['passback_params'],

                'promo_params' => '',
                'extend_params' => '',
                'enable_pay_channels' => '', 'disable_pay_channels',
                'store_id' => '', 'ext_user_info' => [],
            ];*/
            $business['product_code'] = 'QUICK_MSECURITY_PAY';
            $params = array_merge($base, $business);
            $params['biz_content'] = http_build_query($business);
            $params['sign'] = $alipay->generateSign($params, $alipay->signType);
            return $params;
        }else {
            $request = new \AlipayTradeAppPayRequest();
            $request->setNotifyUrl($base_request['notify_url']);
            $request->setProdCode('QUICK_MSECURITY_PAY');
            $business['product_code'] = 'QUICK_MSECURITY_PAY';
            $request->setBizContent(json_encode($business, JSON_UNESCAPED_UNICODE));
            return $alipay->sdkExecute($request);
        }
    }
}
