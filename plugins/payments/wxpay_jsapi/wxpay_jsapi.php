<?php

/**
 * 微信支付接口类
 * JSAPI 适用于微信内置浏览器访问WAP时支付
 */
class wxpay_jsapi {

    public function __construct($payment_info = array()) {

        define('WXN_APPID', $payment_info['payment_config']['wx_appid']);
        define('WXN_APPSECRET', $payment_info['payment_config']['wx_appsecret']);
        define('WXN_MCHID', $payment_info['payment_config']['wx_mch_id']);
        define('WXN_KEY', $payment_info['payment_config']['wx_key']);
    }

    public function get_payform($order_info) {
        //引入PC端微信公共类
        require_once PLUGINS_PATH . '/payments/wxpay_native/lib/WxPay.Api.php';
        require_once PLUGINS_PATH . '/payments/wxpay_native/WxPay.JsApiPay.php';
        
        //获取用户openid
        $tools = new JsApiPay();
        $openId = $tools->GetOpenid();
        
        //统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody(config('ds_config.site_name') . $order_info['pay_sn'] . '订单');
        $input->SetAttach($order_info['order_type']);
        $input->SetOut_trade_no($order_info['pay_sn'].'_'.TIMESTAMP);//31个字符,微信限制为32字符以内  TIMESTAMP 用来防止做随机数,用户支付订单后取消,已产生的订单不能重复支付
        $input->SetTotal_fee(bcmul($order_info['api_pay_amount'] , 100,0));
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", TIMESTAMP + 600));
        $input->SetGoods_tag("");
        $input->SetNotify_url(str_replace('/index.php', '', HOME_SITE_URL) . '/payment/wxpay_jsapi_notify.html');
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        
        if($order['return_code']=='FAIL'){
            halt($order);
        }
        
        $jsApiParameters = $tools->GetJsApiParameters($order);
        
        //不同订单支付成功对应的跳转界面
        if($order_info['order_type'] == 'real_order'){
            $url = config('ds_config.h5_site_url').'/pages/member/order/OrderList';
        }elseif ($order_info['order_type'] == 'vr_order') {
            $url = config('ds_config.h5_site_url').'/pages/member/vrorder/OrderList';
        } elseif ($order_info['order_type'] == 'pd_order') {
            $url = config('ds_config.h5_site_url').'/pages/member/recharge/RechargeList';
        }
        
        
        
        $str = <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=utf-8" />
<title>微信安全支付</title>
</head>
<body>
正在加载…
<script type="text/javascript">
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			$jsApiParameters,
			function(res){
                            if (res.err_msg == 'get_brand_wcpay_request:ok') {
                                //alert(lang.WeChat_pays_off);
                                self.location = "$url";
                            }else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
                                //alert(lang.cancel_WeChat_payment);
                                self.location = "$url";
                            } else {
                                 //alert(lang.WeChat_payments_fail);
                                 self.location = "$url";
                            }
                            //WeixinJSBridge.log(res.err_msg);
                            //alert(res.err_code+res.err_desc+res.err_msg);
			}
		);
	}
        window.onload = function() {
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
        }
</script>
</body>
</html> 
EOT;
        echo $str;
exit;
        
        
    }

}
