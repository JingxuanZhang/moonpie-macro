<?php


namespace Moonpie\Macro\ByteMiniPayment\Transfer;


use Moonpie\Macro\ByteMiniPayment\Kernel\BaseClient;
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
        $client = $this->app->alipay->getAopClient();
        Loader::import('aop.request.AlipayFundTransToaccountTransferRequest');
        $request = new \AlipayFundTransToaccountTransferRequest();
        $request->setBizContent(json_encode($params, JSON_UNESCAPED_UNICODE));
        $response = $client->execute($request);
        if(!$response) return false;
        $response_key = str_replace('.', '_', $request->getApiMethodName()) . '_response';
        return $response->{$response_key};
    }

    /**
     * 根据商户订单号获取单笔转账的情况
     * @param string $orderNo
     * @return bool|\SimpleXMLElement
     * @throws \Exception
     */
    public function queryAccountOrder(string $orderNo)
    {
        $client = $this->app->alipay->getAopClient();
        Loader::import('aop.request.AlipayFundTransOrderQueryRequest');
        $request = new \AlipayFundTransOrderQueryRequest();
        $request->setBizContent(json_encode(['out_biz_no' => $orderNo]));
        $response = $client->execute($request);
        if(!$response) return false;
        $response_key = str_replace('.', '_', $request->getApiMethodName()) . '_response';
        return $response->{$response_key};
    }
}