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

namespace Moonpie\Macro\ByteMiniPayment\Order;


use Moonpie\Macro\ByteMiniPayment\Kernel\BaseClient;
use EasyWeChat\Kernel\Support;
use think\Loader;

/**
 * 这里模拟使用抖音的前端调用支付功能
 */
class Client extends BaseClient
{
    protected function prepends()
    {
        return [
            'format' => 'JSON',
            'charset' => 'utf-8',
            'version' => '1.0',
        ];
    }

    /**
     * 请求抖音小程序下单接口
     * @param array $params
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function create($params)
    {
        $params['method'] = 'tp.trade.create';
        $params['sign_type'] = 'MD5';
        $params['timestamp'] = $params['timestamp'] ?? time();
        $params['merchant_id'] = $this->app['config']['mch_id'];
        $params['notify_url'] = $params['notify_url'] ?? $this->app['config']['notify_url'];
        return $this->request($this->wrap('gateway'), $params);
    }

    public function queryByOutTradeNumber($orderNo)
    {
        return $this->query([
            'out_trade_no' => $orderNo,
        ]);
    }

    public function queryByTransId($transId)
    {
        return $this->query([
            'trade_no' => $transId,
        ]);
    }

    protected function query($param)
    {
        $client = $this->app->alipay->getAopClient();
        Loader::import('aop.request.AlipayTradeQueryRequest');
        $request = new \AlipayTradeQueryRequest();
        $request->setBizContent(\GuzzleHttp\json_encode($param, JSON_UNESCAPED_UNICODE));
        $response = $client->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $result = $response->{$responseNode};
        return $result;
    }

}
