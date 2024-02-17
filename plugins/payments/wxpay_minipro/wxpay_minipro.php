<?php

/**
 * 微信支付接口类
 * JSAPI 适用于微信内置浏览器访问WAP时支付
 */
class wxpay_minipro {

    public function __construct($payment_info = array()) {

        define('WXN_APPID', $payment_info['payment_config']['xcx_appid']);
        define('WXN_APPSECRET', $payment_info['payment_config']['xcx_appsecret']);
        define('WXN_MCHID', $payment_info['payment_config']['xcx_mch_id']);
        define('WXN_KEY', $payment_info['payment_config']['xcx_key']);
    }

    public function get_payform($order_info) {
        //引入PC端微信公共类
        require_once PLUGINS_PATH . '/payments/wxpay_native/lib/WxPay.Api.php';
        require_once PLUGINS_PATH . '/payments/wxpay_native/WxPay.JsApiPay.php';
        
        //获取用户openid
        $tools = new JsApiPay();
        $openId = input('param.openid');
        
        //统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody(config('ds_config.site_name') . $order_info['pay_sn'] . '订单');
        $input->SetAttach($order_info['order_type']);
        $input->SetOut_trade_no($order_info['pay_sn'].'_'.TIMESTAMP);//31个字符,微信限制为32字符以内  TIMESTAMP 用来防止做随机数,用户支付订单后取消,已产生的订单不能重复支付
        $input->SetTotal_fee(bcmul($order_info['api_pay_amount'] , 100,0));
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", TIMESTAMP + 600));
        $input->SetGoods_tag("");
        $input->SetNotify_url(str_replace('/index.php', '', HOME_SITE_URL) . '/payment/wxpay_minipro_notify.html');
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        
    
        if($order['return_code']=='FAIL'){
            ds_json_encode(10001,$order['return_msg']);
        }else{
            $jsApiParameters = $tools->GetJsApiParameters($order);
            ds_json_encode(10000,'',$jsApiParameters);
        }
        

        
    }

}
