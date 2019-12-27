<?php
/**
 * Copyright (c) 2018-2019.
 * This file is part of the moonpie production
 *   (c) johnzhang <875010341@qq.com>
 *   This source file is subject to the MIT license that is bundled
 *  with this source code in the file LICENSE.
 */

namespace Moonpie\Macro\Alipay\Transfer;


use Moonpie\Macro\Alipay\Kernel\BaseClient;
use think\Loader;

class Client extends BaseClient
{
    /**
     * 单笔金额转至支付宝用户帐户接口
     * @see https://docs.open.alipay.com/api_28/alipay.fund.trans.toaccount.transfer
     * @param array $params
     * @return \SimpleXMLElement|false
     * @throws \Exception
     */
    public function toAccount(array $params)
    {
        $require_keys = ['out_biz_no', 'payee_type', 'payee_account', 'amount'];
        foreach($require_keys as $require_key) {
            if(empty($params[$require_key])) {
                throw new \RuntimeException(sprintf('require parameter "%s" is lost.', $require_key));
            }
        }
        $client = $this->app->getAopClient();
        $request = new \AlipayFundTransToaccountTransferRequest();
        $request->setBizContent(json_encode($params, JSON_UNESCAPED_UNICODE));
        $response = $client->execute($request);
        return $this->prepareAlipayResponse($request, $response);
    }

    /**
     * 根据商户订单号获取单笔转账的情况
     * @param string $orderNo
     * @return bool|\SimpleXMLElement
     * @throws \Exception
     */
    public function queryAccountOrder(string $orderNo)
    {
        $client = $this->app->getAopClient();
        $request = new \AlipayFundTransOrderQueryRequest();
        $request->setBizContent(json_encode(['out_biz_no' => $orderNo]));
        $response = $client->execute($request);
        return $this->prepareAlipayResponse($request, $response);
    }
}