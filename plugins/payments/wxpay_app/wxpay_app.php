<?php

class wxpay_app {
    public function __construct($payment_info = array())
    {
        define('WXN_APPID', $payment_info['payment_config']['wx_appid']);
        define('WXN_APPSECRET', $payment_info['payment_config']['wx_appsecret']);
        define('WXN_MCHID', $payment_info['payment_config']['wx_mch_id']);
        define('WXN_KEY', $payment_info['payment_config']['wx_key']);
    }
    public function get_payform($order_info){
        
        require_once PLUGINS_PATH . '/payments/wxpay_native/lib/WxPay.Api.php';
        require_once PLUGINS_PATH . '/payments/wxpay_native/WxPay.NativePay.php';
        require_once PLUGINS_PATH . '/payments/wxpay_native/log.php';
        
        //统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody(config('ds_config.site_name') . $order_info['pay_sn'] . '订单');
        $input->SetAttach($order_info['order_type']);
        $input->SetOut_trade_no($order_info['pay_sn'].'_'.TIMESTAMP);//31个字符,微信限制为32字符以内  TIMESTAMP 用来防止做随机数,用户支付订单后取消,已产生的订单不能重复支付
        $input->SetTotal_fee(bcmul($order_info['api_pay_amount'] , 100,0));
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", TIMESTAMP + 600));
        $input->SetGoods_tag("");
        $input->SetNotify_url(str_replace('/index.php', '', HOME_SITE_URL) . '/payment/wxpay_app_notify.html');
        $input->SetTrade_type('APP');
        
        $result = WxPayApi::unifiedOrder($input);
        if (is_array($result) && $result['return_code'] == 'SUCCESS') {
            if ($result['result_code'] == 'SUCCESS') {
                $result['timestamp'] = TIMESTAMP.'';
                ds_json_encode(10000,'',array('content'=> $this->sign_again($result)));
            } else {
                ds_json_encode(10001,$result['err_code_des']);
            }
        } else {
            ds_json_encode(10001,$result['return_msg']);
        }
    }

    function sign_again($order) {
        $values=array();
        $values['appid'] = WXN_APPID;
        $values['partnerid'] = WXN_MCHID;
        $values['prepayid'] = $order['prepay_id'];
        $values['package'] = 'Sign=WXPay';
        $values['noncestr'] = $order['nonce_str'];
        $values['timestamp'] = $order['timestamp'];
        
        ksort($values);
        $buff = "";
        foreach ($values as $key => $value) {
            $buff .= $key . "=" . $value . "&";
        }

        $string = trim($buff, "&");
        $string = $string . "&key=" . WXN_KEY;
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        $values['sign'] = $result;
        return $values;
    }

}
