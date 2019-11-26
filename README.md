##微应用集合
### 介绍
一款基于EasyWechat的类库，现在这么多小程序应用（支付宝、抖音、百度），既然都是小程序，为何不放在一个类库呢。
EasyWechat解决了微信端的，剩下的就交给我吧。
### 安装
```php
composer require moonpie/macro
```
### 用法
```php
<?php
use Moonpie\Macro\Factory;
$config = [];
$payment_app = Factory::payment($config);
$official_app = Factory::officialAccount($config);
//-----------

//对于抖音应用
$config = [
    'app_id' => '1232j3kj2k3',
    'secret' => 'fjsadkfjskd',
];
$byte_app = Factory::byteMiniProgram($config);
$byte_payment_app = Factory::byteMiniPayment($config);

//其他应用后续完善

```