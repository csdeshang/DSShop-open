<?php

$lang['alipay_appid'] = '支付宝APPID';
$lang['alipay_appid_desc'] = '支付宝的APPID<a href="https://b.alipay.com/" target="_blank">申请地址</a><a href="https://opensupport.alipay.com/support/FAQ/45637fe7-e31c-42fd-add3-d0be2a090a5c" target="_blank">帮助文档</a>';
$lang['app_cert_path'] = '应用证书路径';
$lang['app_cert_path_desc'] = '（要确保证书文件可读），以linux系统 宝塔控制面板环境为例：/www/wwwroot/www.域名.com/plugins/payments/alipay/cert/appCertPublicKey.crt';
$lang['alipay_cert_path'] = '支付宝公钥证书路径';
$lang['alipay_cert_path_desc'] = '（要确保证书文件可读），以linux系统 宝塔控制面板环境为例：/www/wwwroot/www.域名.com/plugins/payments/alipay/cert/alipayCertPublicKey_RSA2.crt';
$lang['root_cert_path'] = '支付宝根证书路径';
$lang['root_cert_path_desc'] = '（要确保证书文件可读），以linux系统 宝塔控制面板环境为例：/www/wwwroot/www.域名.com/plugins/payments/alipay/cert/alipayRootCert.crt';
$lang['private_key'] = '应用私钥';
$lang['private_key_desc'] = '';
$lang['alipay_trade_refund_state'] = '支付原路退款';
$lang['alipay_trade_refund_state_desc'] = '使用支付宝支付订单退款时(不使用预存款和充值卡支付), 微信退款原路返回';
$lang['alipay_trade_transfer_state'] = '提现打款';
$lang['alipay_trade_transfer_state_desc'] = '用户使用支付宝账号申请提现时直接打款到支付宝';

$lang['wx_appid'] = 'APPID';
$lang['wx_appid_desc'] = '微信中的AppId<a href="https://pay.weixin.qq.com" target="_blank">申请地址</a>';
$lang['wx_appsecret'] = 'APPSECRET';
$lang['wx_appsecret_desc'] = '微信中的AppSecret';
$lang['wx_mch_id'] = 'MCH_ID';
$lang['wx_mch_id_desc'] = '商户自己申请时，填写自己申请的微信支付商户号；服务商申请时，填写服务商提供的微信支付商户号';
$lang['wx_key'] = 'KEY';
$lang['wx_key_desc'] = '商户自己申请时，填写自己申请的商户平台的32位密钥；服务商申请时，填写服务商提供的32位密钥';
$lang['wx_trade_refund_state'] = '支付原路退款';
$lang['wx_trade_refund_state_desc'] = '使用微信支付订单退款时(不使用预存款和充值卡支付), 微信退款原路返回. 需要配置微信支付同时配置好apiclient_cert和apiclient_key才可以进行后续操作<a href="https://kf.qq.com/faq/161222NneAJf161222U7fARv.html" target="_blank">申请地址</a>';
$lang['wx_trade_transfer_state'] = '提现打款';
$lang['wx_trade_transfer_state_desc'] = '用户使用微信账号申请提现时直接打款到微信。需要配置微信支付同时配置好apiclient_cert和apiclient_key才可以进行后续操作<a href="https://kf.qq.com/faq/161222NneAJf161222U7fARv.html" target="_blank">申请地址</a>';
$lang['wx_sslcert_path'] = 'apiclient_cert.pem路径';
$lang['wx_sslcert_path_desc'] = 'apiclient_cert.pem文件在服务器的路径, 以linux系统 宝塔控制面板环境为例："/www/wwwroot/www.域名.com/plugins/payments/wxpay_native/cert/apiclient_cert.pem"';
$lang['wx_sslkey_path'] = 'apiclient_key.pem路径';
$lang['wx_sslkey_path_desc'] = 'apiclient_key.pem文件在服务器的路径, 以linux系统 宝塔控制面板环境为例："/www/wwwroot/www.域名.com/plugins/payments/wxpay_native/cert/apiclient_key.pem"';



$lang['allinpay_appid'] = 'APPID';
$lang['allinpay_appid_desc'] = '通联支付中的AppId<a href="http://www.allinpay.com/" target="_blank">申请地址</a>';
$lang['allinpay_mch_id'] = '商户号';
$lang['allinpay_mch_id_desc'] = '商户自己申请时，填写自己申请的通联支付商户号';
$lang['allinpay_key'] = 'KEY';
$lang['allinpay_key_desc'] = '商户自己申请时，填写自己申请的商户平台的密钥';
$lang['allinpay_sub_appid1'] = '微信公众号APPID';
$lang['allinpay_sub_appid1_desc'] = '通联支付中的微信公众号AppId<a href="https://mp.weixin.qq.com/" target="_blank">申请地址</a>';
$lang['allinpay_sub_appsecret1'] = '微信公众号APPSECRET';
$lang['allinpay_sub_appsecret1_desc'] = '通联支付中的微信公众号APPSECRET';
$lang['allinpay_sub_appid2'] = '微信小程序APPID';
$lang['allinpay_sub_appid2_desc'] = '通联支付中的微信小程序AppId<a href="https://mp.weixin.qq.com/" target="_blank">申请地址</a>';

$lang['xcx_appid'] = 'APPID';
$lang['xcx_appid_desc'] = '小程序中的AppId';
$lang['xcx_appsecret'] = 'APPSECRET';
$lang['xcx_appsecret_desc'] = '小程序中的AppSecret';
$lang['xcx_mch_id'] = 'MCH_ID';
$lang['xcx_mch_id_desc'] = '商户自己申请时，填写自己申请的微信支付商户号；服务商申请时，填写服务商提供的微信支付商户号';
$lang['xcx_key'] = 'KEY';
$lang['xcx_key_desc'] = '商户自己申请时，填写自己申请的商户平台的32位密钥；服务商申请时，填写服务商提供的32位密钥';

$lang['payment_index_pc'] = 'PC支付';
$lang['payment_index_h5'] = 'H5支付';
$lang['payment_index_app'] = 'APP支付';
$lang['payment_index_name'] = '支付方式';
$lang['payment_index_enable_ing'] = '开启中';
$lang['payment_index_disable_ing'] = '关闭中';
$lang['payment_index_enable'] = '启用';
$lang['payment_index_disable'] = '禁用';
return $lang;
?>
