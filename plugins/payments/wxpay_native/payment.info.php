<?php

return array(
    'payment_code' => 'wxpay_native',
    'payment_name' => '微信扫码支付',
    'payment_desc' => '微信扫码支付',
    'payment_is_online' => '1',
    'payment_platform' => 'pc', #支付平台 pc h5 app
    'payment_author' => '长沙德尚',
    'payment_website' => 'https://pay.weixin.qq.com',
    'payment_version' => '1.0',
    'payment_config' => array(
        array('name' => 'wx_appid', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'wx_appsecret', 'type' => 'password', 'value' => '', 'desc' => '描述'),
        array('name' => 'wx_mch_id', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'wx_key', 'type' => 'password', 'value' => '', 'desc' => '描述'),
        array('name' => 'wx_trade_refund_state', 'type' => 'radio', 'value' => '0', 'desc' => '描述'),
        array('name' => 'wx_trade_transfer_state', 'type' => 'radio', 'value' => '0', 'desc' => '描述'),
        array('name' => 'wx_sslcert_path', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'wx_sslkey_path', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        
    ),
);
?>
