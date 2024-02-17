<?php

class alipay_h5 {

    //配置
    private $config = array();

    public function __construct($payment_info = array()) {
        if (!empty($payment_info)) {
            $this->config = array(
                //应用ID,您的APPID。
                'app_id' => $payment_info['payment_config']['alipay_appid'],
                //商户私钥
                'merchant_private_key' => $payment_info['payment_config']['private_key'],
                //异步通知地址
                'notify_url' => str_replace('/index.php', '', HOME_SITE_URL) . '/payment/alipay_h5_notify.html',
                //编码格式
                'charset' => "UTF-8",
                //签名方式
                'sign_type' => "RSA2",
                //支付宝网关
                'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
                //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
                'app_cert_path' => $payment_info['payment_config']['app_cert_path'],
                'alipay_cert_path' => $payment_info['payment_config']['alipay_cert_path'],
                'root_cert_path' => $payment_info['payment_config']['root_cert_path'],
            );
        }
    }

    public function get_payform($order_info) {
        require_once PLUGINS_PATH . '/payments/alipay/aop/AopCertClient.php';
        require_once PLUGINS_PATH . '/payments/alipay/aop/request/AlipayTradeWapPayRequest.php';
        $aop = new AopCertClient ();
        $appCertPath = $this->config['app_cert_path'];
        $alipayCertPath = $this->config['alipay_cert_path'];
        $rootCertPath = $this->config['root_cert_path'];

        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $this->config['app_id'];
        $aop->rsaPrivateKey = $this->config['merchant_private_key'];
        $aop->alipayrsaPublicKey = $aop->getPublicKey($alipayCertPath);
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'utf-8';
        $aop->format = 'json';
        $aop->isCheckAlipayPublicCert = true; //是否校验自动下载的支付宝公钥证书，如果开启校验要保证支付宝根证书在有效期内
        $aop->appCertSN = $aop->getCertSN($appCertPath); //调用getCertSN获取证书序列号
        $aop->alipayRootCertSN = $aop->getRootCertSN($rootCertPath); //调用getRootCertSN获取支付宝根证书序列号
        $request = new AlipayTradeWapPayRequest ();
        $request->setNotifyUrl($this->config['notify_url']);
        //不同订单支付成功对应的跳转界面
        if($order_info['order_type'] == 'real_order'){
            $url = config('ds_config.h5_site_url').'/pages/member/order/OrderList';
        }elseif ($order_info['order_type'] == 'vr_order') {
            $url = config('ds_config.h5_site_url').'/pages/member/vrorder/OrderList';
        } elseif ($order_info['order_type'] == 'pd_order') {
            $url = config('ds_config.h5_site_url').'/pages/member/recharge/RechargeList';
        }
        if(input('param.uniapp')){
          $url = preg_replace('/^(http|https):\/\//','dsmall://',$url);
        }
        $request->setReturnUrl($url);
        $request->setBizContent("{" .
                "\"total_amount\":" . round($order_info['api_pay_amount'],2) . "," .
                "\"subject\":\"" . $order_info['subject'] . "\"," .
                "\"body\":\"" . $order_info['order_type'] . "\"," .
                "\"out_trade_no\":\"" . $order_info['order_type'] . '-' . $order_info['pay_sn'] . "\"," .
                "\"timeout_express\":\"1m\"," .
                "\"product_code\":\"QUICK_WAP_WAY\"" .
                "  }");
        $result = $aop->pageExecute($request);
        echo $result;exit;
    }

    /**
     * 获取return信息
     */
    public function verify_return() {
        require_once PLUGINS_PATH . '/payments/alipay/aop/AopCertClient.php';
        $arr = $_GET;
        $aop = new AopCertClient ();
        $appCertPath = $this->config['app_cert_path'];
        $alipayCertPath = $this->config['alipay_cert_path'];
        $rootCertPath = $this->config['root_cert_path'];

        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $this->config['app_id'];
        $aop->rsaPrivateKey = $this->config['merchant_private_key'];
        $aop->alipayrsaPublicKey = $aop->getPublicKey($alipayCertPath);
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'utf-8';
        $aop->format = 'json';
        $aop->isCheckAlipayPublicCert = true; //是否校验自动下载的支付宝公钥证书，如果开启校验要保证支付宝根证书在有效期内
        $aop->appCertSN = $aop->getCertSN($appCertPath); //调用getCertSN获取证书序列号
        $aop->alipayRootCertSN = $aop->getRootCertSN($rootCertPath); //调用getRootCertSN获取支付宝根证书序列号
        $result = $aop->rsaCheckV1($arr, $aop->alipayrsaPublicKey, $aop->signType);
        if ($result) {
            return true;
        }
        return false;
    }

}
