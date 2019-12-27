<?php
/**
 * Copyright (c) 2018-2019.
 * This file is part of the moonpie production
 *   (c) johnzhang <875010341@qq.com>
 *   This source file is subject to the MIT license that is bundled
 *  with this source code in the file LICENSE.
 */

namespace Moonpie\Macro\Alipay\Refund;


use Moonpie\Macro\Alipay\Kernel\BaseClient;

class Client extends BaseClient
{
    /**
     * 通过支付宝交易号退款
     * @param $transactionId string 支付宝订单号
     * @param $refundAmount float 希望退款的金额,单位元
     * @param string $refundNumber 如果商家发起的是部分退款，则此参数必传
     * @param array $config 支付宝该接口的其他可选参数
     * @return bool|\SimpleXMLElement
     * @throws \Exception
     */
    public function byTransactionId($transactionId, $refundAmount, $refundNumber = '', array $config = [])
    {
        $params = array_merge($config, [
            'trade_no' => $transactionId, 'refund_amount' => $refundAmount,
        ]);
        if (!empty($refundNumber)) $params['out_request_no'] = $refundNumber;
        return $this->handleRaiseAction($params);
    }

    /**
     * 通过商家订单号退款
     * @param $outTradeNumber string 商家订单号
     * @param $refundAmount float 希望退款的金额,单位元
     * @param string $refundNumber 如果商家发起的是部分退款，则此参数必传
     * @param array $config 支付宝该接口的其他可选参数
     * @return bool|\SimpleXMLElement
     * @throws \Exception
     */
    public function byOutTradeNumber($outTradeNumber, $refundAmount, $refundNumber = '', array $config = [])
    {
        $params = array_merge($config, [
            'out_trade_no' => $outTradeNumber, 'refund_amount' => $refundAmount,
        ]);
        if (!empty($refundNumber)) $params['out_request_no'] = $refundNumber;
        return $this->handleRaiseAction($params);
    }

    /**
     * 统一收单交易退款接口
     * @see https://docs.open.alipay.com/api_1/alipay.trade.refund
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    protected function handleRaiseAction(array $params)
    {
        $client = $this->app->getAopClient();
        $request = new \AlipayTradeRefundRequest();
        $request->setBizContent(json_encode($params, JSON_UNESCAPED_UNICODE));
        $response = $client->execute($request);
        return $this->prepareAlipayResponse($request, $response);
    }

    /**
     * 统一收单交易退款查询
     * @see https://docs.open.alipay.com/api_1/alipay.trade.fastpay.refund.query/
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    protected function handleQueryAction(array $params)
    {
        $client = $this->app->getAopClient();
        $request = new \AlipayTradeFastpayRefundQueryRequest();
        $request->setBizContent(json_encode($params, JSON_UNESCAPED_UNICODE));
        $response = $client->execute($request);
        return $this->prepareAlipayResponse($request, $response);
    }

    /**
     * 通过支付宝交易流水号查询退款
     * @param $transactionId string 支付宝交易号
     * @param $refundNumber string 商家退款申请号，如果没有退款申请号则应该是商家订单号
     * @param array $config 接口请求中的其他参数
     * @return mixed
     * @throws \Exception
     */
    public function queryByTransactionId($transactionId, $refundNumber, array $config = [])
    {
        $params = array_merge($config, [
            'out_request_no' => $refundNumber, 'trade_no' => $transactionId
        ]);
        return $this->handleQueryAction($params);
    }

    /**
     * 通过商家交易流水号查询退款
     * @param $outTradeNumber string 商家交易号
     * @param $refundNumber string 商家退款请求号，如果没有则是商家交易号
     * @param array $config 接口请求中的其他参数
     * @return mixed
     * @throws \Exception
     */
    public function queryByOutTradeNumber($outTradeNumber, $refundNumber, array $config = [])
    {
        $params = array_merge($config, [
            'out_request_no' => $refundNumber, 'out_trade_no' => $outTradeNumber
        ]);
        return $this->handleQueryAction($params);
    }
}