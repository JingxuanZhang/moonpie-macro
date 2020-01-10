## 抖音支付宝支付相关接口
### 应用配置
```php
$config = [
    'app_id' => '支付设置中的app_id',
    'key' => '支付设置中的支付secret',
    'mch_id' => '支付设置中的商户号',
    'alipay' => [
        'app_id' => '支付宝分配给您的应用ID',
        'rsa_private_file' => '支付宝要求您创建的rsa加密私钥文件路径',
        //'rsa_public_file' => '支付宝要求您创建的rsa加密公钥文件路径',
        'ali_public_file' => '支付宝为您的应用生成的公钥数据文件路径',
        'sign_type' => 'RSA2', //新版签名方式
        'notify_url' => 'http://www.baidu.com', //默认支付回调响应地址
    ],
    'wechat' => [
       // 必要配置
           'app_id'             => 'xxxx',
           'mch_id'             => 'your-mch-id',
           'key'                => 'key-for-signature',   // API 密钥
       
           // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
           'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
           'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
       
           'notify_url'         => '默认的订单回调地址',     // 你也可以在下单时单独设置来想覆盖它
    ]

    // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
    'response_type' => 'array',

    'log' => [
        'default' => 'prod',
        'channels' => [
            'prod' => [
                'driver' => 'daily',
                'path' => RUNTIME_PATH . 'log/tt-payment-local.log',
                'level' => 'debug',
            ]
        ],
    ],

    'http' => [
        'max_retries' => 1,
        'retry_delay' => 500,
        'timeout' => 5.0,
        'verify' => false,
        // 'base_uri' => 'https://api.weixin.qq.com/', // 如果你在国外想要覆盖默认的 url 的时候才使用，根据不同的模块配置不同的 uri
    ],
];
$app = Factory::byteMiniPayment($config);
```
### 调用tt.pay发起支付
#### 封装自[官方文档](https://microapp.bytedance.com/dev/cn/mini-app/develop/open-capacity/payment/tt.pay)
```php

$params = [
    'out_order_no' => $order->getOrderId(),	//String	必选	32	订单号	20160727001
    'uid' => $byteUser->getOpenid(), //	String	必选	32	唯一标识用户的id，小程序开发者请传open_id。open_id获取方法
    'total_amount' => $this->getNeedPayFee(),	//Long	必选	32	金额，分为单位，应传整型
    'currency' => 'CNY', //	String	必选	9	币种	CNY
    'subject' => $this->getPaymentExtra( 'subject', $this->getTransBody()), //subject	String	必选	200	商户订单名称
    'body' => $this->getPaymentExtra( 'body', $this->getTransBody()),	//String	必选	200	商户订单详情
    'trade_time' => time(), //trade_time	String	必选	14	下单时间戳，unix时间戳
    'valid_time' => 3600, //valid_time	String	必选	14	订单有效时间（单位 秒）	15
    'risk_info' => json_encode([
        'ip' => request()->ip(),
        'device_id' => $this->getPaymentExtra('device_id', '29329392093'),
    ]),
    'notify_url' => $this->getCallbackUrl(),
];
$response = $app->jssdk->platformRequestConfig(1, $openid, $amount, $params);
```
### (支付宝)支付回调响应
```php
$response = $app->handlePaidNotify(function ($message, $fail)use($app) {
     //return $fail('handle success');
    return true;
});
$response->send();
```
### (微信)支付回调响应
```php
$response = $app->wepay->handlePaidNotify(function ($message, $fail)use($app) {
     //return $fail('handle success');
    return true;
});
$response->send();
看着和EasyWechat的一样，这就对了，这就是我们的目标