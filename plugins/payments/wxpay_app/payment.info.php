<?php

return array(
    'payment_code' => 'wxpay_app',
    'payment_name' => '微信APP支付',
    'payment_desc' => '微信APP支付',
    'payment_is_online' => '1',
    'payment_platform' => 'app', #支付平台 pc h5 app
    'payment_author' => '长沙德尚',
    'payment_website' => 'https://pay.weixin.qq.com',
    'payment_version' => '1.0',
    'payment_config' => array(
        array('name' => 'wx_appid', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'wx_appsecret', 'type' => 'password', 'value' => '', 'desc' => '描述'),
        array('name' => 'wx_mch_id', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'wx_key', 'type' => 'password', 'value' => '', 'desc' => '描述'),
    ),
);
?>
