<?php

return array(
    'payment_code' => 'alipay_h5',
    'payment_name' => '支付宝手机支付',
    'payment_desc' => 'H5端支付宝支付接口',
    'payment_is_online' => '1',
    'payment_platform' => 'h5', #支付平台 pc h5 app
    'payment_author' => '长沙德尚',
    'payment_website' => 'http://www.alipay.com',
    'payment_version' => '1.0',
    'payment_config' => array(
        array('name' => 'alipay_appid', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'app_cert_path', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'alipay_cert_path', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'root_cert_path', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'private_key', 'type' => 'password', 'value' => '', 'desc' => '描述'),
    ),
);
?>
