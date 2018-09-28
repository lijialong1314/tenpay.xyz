**说明**

本项目基于PHP Workerman框架做的个人收款Demo，前端使用WebSocket通讯协议。集成了PAYJS、有赞两种个人收款方案。

[PAYJS](https://payjs.cn/ref/DXVLLD)：可以帮你和微信支付官方签约成为【微信支付个人商户】，微信支付每天自动给您结算昨天的交易款，每笔交易0.38%手续费。

[有赞](https://www.youzanyun.com/apilist/detail/group_trade/pay_qrcode/youzan.pay.qrcode.create)：	利用有赞云接口给自己的微小店创建收款二维码，费率为每笔交易1%。

**环境要求**

1. php 7.0 以上
2. Nginx或者Apache Httpd
3. Redis

**使用方法**

1、修改src/Config目录下的配置文件，细分了很多个

- 007-config.php：[腾讯007验证码](https://007.qq.com/)的配置，作为前端界面的一个安全校验，目前是免费使用的，需要的赶紧去注册
- payjs_config.php：PAYJS的相关配置
- youzan_config.php：有赞的相关配置
- redis_config.php：redis的连接信息
- ssl_config.php：WebSocket使用的WSS协议需要的证书配置
- system_config.php：demo内部使用的一些协议配置

2、启动Workerman：

```php
php src/start.php start
```

**Demo**

https://tenpay.xyz

**感谢**

PAYJS SDK：https://github.com/xhat/payjs

YouZanPay SDK：https://github.com/xu42/pay