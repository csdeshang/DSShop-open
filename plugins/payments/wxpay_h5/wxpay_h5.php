<?php

class wxpay_h5
{
    const DEBUG = 0;

    protected $config;

    public function __construct($payment_info = array())
    {
        define('WXN_APPID', $payment_info['payment_config']['wx_appid']);
        define('WXN_APPSECRET', $payment_info['payment_config']['wx_appsecret']);
        define('WXN_MCHID', $payment_info['payment_config']['wx_mch_id']);
        define('WXN_KEY', $payment_info['payment_config']['wx_key']);
    }
    /*mweb_url*/
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
        $input->SetNotify_url(str_replace('/index.php', '', HOME_SITE_URL) . '/payment/wxpay_h5_notify.html');
        $input->SetTrade_type("MWEB");
        
        $result = WxPayApi::unifiedOrder($input);
        
        //不同订单支付成功对应的跳转界面
        if($order_info['order_type'] == 'real_order'){
            $pay_type='pay_new';
        }elseif ($order_info['order_type'] == 'vr_order') {
            $pay_type='vr_pay_new';
        } elseif ($order_info['order_type'] == 'pd_order') {
            $pay_type='pd_pay';
        }
        $url = config('ds_config.h5_site_url').'/pages/member/buy/pay?notice=1&pay_sn='.$order_info['pay_sn'].'&pay_type='.$pay_type;
        if(input('param.uniapp')){
          $url = preg_replace('/^(http|https):\/\//','dsmall://',$url);
        }
        $mweb_url = $result['mweb_url'].'&redirect_url='.urlencode($url);
        echo '<script>window.location.href="'.$mweb_url.'"</script>';exit;
//        header("Location:".$mweb_url);
//        exit;
    }
}