<?php

/**
 * 微信扫码支付
 */
class wxpay_native {

    /**
     * 支付信息初始化
     * @param array $payment_info
     */
    public function __construct($payment_info = array()) {
            if(!isset($payment_info['payment_config']['wx_appid'])){
                throw new \think\Exception('请配置微信支付', 10006);
            }
        define('WXN_APPID', $payment_info['payment_config']['wx_appid']);
        define('WXN_MCHID', $payment_info['payment_config']['wx_mch_id']);
        define('WXN_KEY', $payment_info['payment_config']['wx_key']);
        define('WXN_APPSECRET', $payment_info['payment_config']['wx_appsecret']);
        define('WXN_SSLCERT_PATH', $payment_info['payment_config']['wx_sslcert_path']);
        define('WXN_SSLKEY_PATH', $payment_info['payment_config']['wx_sslkey_path']);
    }

    /**
     * 组装包含支付信息的url(模式1)  失效
     */
    public function get_payforms() {
        require_once PLUGINS_PATH . '/payments/wxpay_native/lib/WxPay.Api.php';
        require_once PLUGINS_PATH . '/payments/wxpay_native/WxPay.NativePay.php';
        require_once PLUGINS_PATH . '/payments/wxpay_native/log.php';
        $notify = new NativePay();
        return $notify->GetPrePayUrl($order_info['pay_sn']);
    }

    /**
     * 组装包含支付信息的url(模式2)
     */
    public function get_payform($order_info) {
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
        $input->SetTime_expire(date("YmdHis", TIMESTAMP + 3600));
        $input->SetGoods_tag('');
        $input->SetNotify_url(str_replace('/index.php', '', HOME_SITE_URL) . '/payment/wxpay_native_notify.html');
        $input->SetTrade_type("NATIVE");
        //$input->SetOpenid($openId);
        $input->SetProduct_id($order_info['pay_sn']);
        $result = WxPayApi::unifiedOrder($input);

        if(isset($result["code_url"])){
            return $result["code_url"];
        }else{
            halt($result);
        }
    }

    /**
     * 异步验证
     */
    public function verify_notify() {
        require_once PLUGINS_PATH . '/payments/wxpay_native/lib/WxPay.Api.php';
        require_once PLUGINS_PATH . '/payments/wxpay_native/lib/WxPay.Notify.php';

        $notify = new \WxPayNotify();
        $notify->Handle(true);
        $xml = file_get_contents('php://input');
        $data = $notify->FromXml($xml);
        
        if (!array_key_exists("transaction_id", $data)) {
            $verify_notify = false;
        } else {
            $transaction_id = $data['transaction_id'];
            $input = new \WxPayOrderQuery();
            $input->SetTransaction_id($transaction_id);
            $wxpay = new \WxPayApi();
            $result = $wxpay->orderQuery($input);
            if (array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
                $verify_notify = TRUE;
            } else {
                $verify_notify = false;
            }
        }
        if ($verify_notify) {
            $notify_result = array(
                'out_trade_no' => $data["out_trade_no"], #商户订单号
                'trade_no' => $data['transaction_id'], #交易凭据单号
                'total_fee' => $data["total_fee"] / 100, #涉及金额
                'order_type'=>$data["attach"],
                'trade_status' => '1',
            );
        } else {
            $notify_result = array(
                'trade_status' => '0',
            );
        }
        return $notify_result;
    }
    
    
    /**
     * 原路退款
     */
    public function trade_refund($order_info,$refund_amount)
    {
        require_once PLUGINS_PATH . '/payments/wxpay_native/lib/WxPay.Api.php';
        require_once PLUGINS_PATH . '/payments/wxpay_native/lib/WxPay.Notify.php';
        
	    $transaction_id = $order_info['trade_no'];
	    //total_fee总订单价格
        $total_fee = ($order_info['total_order_amount']-$order_info['rcb_amount']-$order_info['pd_amount'])*100;
        $refund_fee = $refund_amount*100;
        $input = new WxPayRefund();
        $input->SetTransaction_id($transaction_id);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($refund_fee);

        $input->SetOut_refund_no("sdkphp" . date("YmdHis"));
        $input->SetOp_user_id('');
        $wxpay = new \WxPayApi();
        $result = $wxpay->refund($input);
        
        if ($result['return_code'] == 'SUCCESS') {
            if ($result['result_code'] == 'SUCCESS') {
                return ds_callback(TRUE);
            } elseif ($result['err_code'] == 'NOTENOUGH') {//未结算资金不足时使用可用资金去退款
                $input->SetRefund_account('REFUND_SOURCE_RECHARGE_FUNDS');
                $result = $wxpay->refund($input);
                if ($result['return_code'] == 'SUCCESS') {
                    if ($result['result_code'] == 'SUCCESS') {
                        return ds_callback(TRUE);
                    } else {
                        return ds_callback(FALSE, $result['err_code_des']);
                    }
                } else {
                    return ds_callback(FALSE, $result['return_msg']);
                }
            } else {
                return ds_callback(FALSE, $result['err_code_des']);
            }
        } else {
            return ds_callback(FALSE, $result['return_msg']);
        }
    }
    
    /**
     * 转账
     */
    public function fund_transfer($withdraw_info)
    {
        require_once PLUGINS_PATH . '/payments/wxpay_native/lib/WxPay.Api.php';
        
        
        $inputObj = new WxPayResults();
	$inputObj->SetData('mch_appid', WxPayConfig::APPID);
        $inputObj->SetData('mchid', WxPayConfig::MCHID);
        $inputObj->SetData('nonce_str', WxPayApi::getNonceStr());
        $inputObj->SetData('partner_trade_no', $withdraw_info['pdc_sn']);
        $inputObj->SetData('openid', $withdraw_info['pdc_bank_no']);
        $inputObj->SetData('check_name', 'NO_CHECK');
        $inputObj->SetData('amount', $withdraw_info['pdc_amount']*100);
        $inputObj->SetData('desc', config('ds_config.site_name')."账户提现");
        $inputObj->SetData('spbill_create_ip', $_SERVER['REMOTE_ADDR']);
        $inputObj->SetSign();//签名
        $xml = $inputObj->ToXml();

        $url='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        
        $startTimeStamp = WxPayApi::getMillisecond(); //请求开始时间
        $response = WxPayApi::postXmlCurl($xml, $url, true, 6);
        $obj=new WxPayResults();
        $result = $obj->FromXml($response);

        if($result['return_code'] == 'SUCCESS'){
            if($result['result_code'] == 'SUCCESS'){
                return ds_callback(TRUE,'',array('pdc_trade_sn'=>$result['payment_no']));
            }else{
                return ds_callback(FALSE, $result['err_code_des']);
            }
        }else{
            return ds_callback(FALSE, $result['return_msg']);
        }
    }

}
