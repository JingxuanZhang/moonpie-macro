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
### 调用支付宝发起支付
#### 封装自[官方文档](https://developer.toutiao.com/docs/payment/#%E7%BB%99%E5%BC%80%E5%8F%91%E8%80%85%E4%BD%BF%E7%94%A8%E7%9A%84%E6%9C%8D%E5%8A%A1%E7%AB%AF%E4%B8%8B%E5%8D%95%E6%8E%A5%E5%8F%A3)
```php

$params = [
    'out_order_no' => $order->getOrderId(),	//String	必选	32	订单号	20160727001
    'uid' => $byteUser->getOpenid(), //	String	必选	32	唯一标识用户的id，小程序开发者请传open_id。open_id获取方法
    'total_amount' => $this->getNeedPayFee(),	//Long	必选	32	金额，分为单位，应传整型
    'currency' => 'CNY', //	String	必选	9	币种	CNY
    'subject' => $this->getPaymentExtra( 'subject', $this->getTransBody()), //subject	String	必选	200	商户订单名称
    'body' => $this->getPaymentExtra( 'body', $this->getTransBody()),	//String	必选	200	商户订单详情
    //'pay_discount	String	可选	1000	折扣 格式（3段）:订单号^金额^方式|订单号^金额^方式。 方式目前仅支持红包: coupon如：423423^1^coupon。 可选，目前暂不支持	combine
    'trade_time' => time(), //trade_time	String	必选	14	下单时间戳，unix时间戳
    'valid_time' => time() + 3600, //valid_time	String	必选	14	订单有效时间（单位 秒）	15
    //notify_url	String	必选	500	服务器异步通知http地址
    //service_fee	String	可选	20	平台手续费
    //risk_info	String	必选	2048	风控信息，标准的json字符串格式，目前需要传入用户的真实ip和device_id： "{"ip":"123.123.123.1", "device_id":"1234"}"	{"ip":"123.123.123.1", "device_id":"1234"}
    //ext_param	String	可选	1024	扩展参数，json格式， 用来上送商户自定义信息
    'risk_info' => json_encode([
        'ip' => request()->ip(),
        'device_id' => $this->getPaymentExtra('device_id', '10093920932'),
    ]),
    'notify_url' => $this->getCallbackUrl(),
];
$response = $app->order->create($params);
```
### 支付回调响应
```php
$response = $app->handlePaidNotify(function ($message, $fail)use($app) {
     //return $fail('handle success');
    return true;
});
$response->send();
```
看着和EasyWechat的一样，这就对了，这就是我们的目标