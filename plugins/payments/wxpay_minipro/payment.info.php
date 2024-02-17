<?php

return array(
    'payment_code' => 'wxpay_minipro',
    'payment_name' => '小程序支付',
    'payment_desc' => '小程序支付',
    'payment_is_online' => '1',
    'payment_platform' => 'h5', #支付平台 pc h5 app
    'payment_author' => '长沙德尚',
    'payment_website' => 'https://mp.weixin.qq.com',
    'payment_version' => '1.0',
    'payment_config' => array(
        array('name' => 'xcx_appid', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'xcx_appsecret', 'type' => 'password', 'value' => '', 'desc' => '描述'),
        array('name' => 'xcx_mch_id', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'xcx_key', 'type' => 'password', 'value' => '', 'desc' => '描述'),
    ),
);
?>
