<?php

class alipay {

    private $config;

    public function __construct($payment_info = array(), $order_info = array()) {
        if (!empty($payment_info)) {
            if(!isset($payment_info['payment_config']['alipay_appid'])){
                throw new \think\Exception('请配置支付宝支付', 10006);
            }
            $this->config = array(
                //应用ID,您的APPID。
                'app_id' => $payment_info['payment_config']['alipay_appid'],
                //商户私钥
                'merchant_private_key' => $payment_info['payment_config']['private_key'],
                //异步通知地址
                'notify_url' => str_replace('/index.php', '', HOME_SITE_URL) . '/payment/alipay_notify.html', //通知URL,
                //同步跳转
                'return_url' => str_replace('/index.php', '', HOME_SITE_URL) . "/payment/alipay_return.html", //返回URL,
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

    /**
     * 获取支付接口的请求地址
     *
     * @return string
     */
    public function get_payform($order_info) {
        require_once dirname(__FILE__) . '/aop/AopCertClient.php';
        require_once dirname(__FILE__) . '/aop/request/AlipayTradePagePayRequest.php';
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
        $request = new AlipayTradePagePayRequest ();
        $request->setNotifyUrl($this->config['notify_url']);
        $request->setReturnUrl($this->config['return_url']);
        $request->setBizContent("{" .
                "\"out_trade_no\":\"" . $order_info['order_type'] . '-' . $order_info['pay_sn'] . "\"," .
                "\"product_code\":\"FAST_INSTANT_TRADE_PAY\"," .
                "\"total_amount\":" . round($order_info['api_pay_amount'],2) . "," .
                "\"subject\":\"" . $order_info['subject'] . "\"," .
                "\"body\":\"" . $order_info['order_type'] . "\"" .
                "  }");
        $result = $aop->pageExecute($request);
        echo $result;exit;
    }

    public function return_verify() {
        require_once dirname(__FILE__) . '/aop/AopCertClient.php';
        $arr = $_GET;
        $return_result = array(
            'trade_status' => '0',
        );

        $temp = explode('-', input('param.out_trade_no'));
        $out_trade_no = $temp['1'];  //返回的支付单号
        $order_type = $temp['0'];

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
            $return_result = array(
                'out_trade_no' => $out_trade_no, #商户订单号
                'trade_no' => input('param.trade_no'), #交易凭据单号
                'total_fee' => input('param.total_amount'), #涉及金额
                'order_type' => $order_type,
                'trade_status' => '1',
            );
        }

        return $return_result;
    }

    public function verify_notify() {
        require_once dirname(__FILE__) . '/aop/AopCertClient.php';
        $arr = $_POST;
        $notify_result = array(
            'trade_status' => '0',
        );
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
            if ($arr['trade_status'] == 'TRADE_SUCCESS') {
                $out_trade_no = explode('-', input('param.out_trade_no'));
                $out_trade_no = $out_trade_no['1'];
                $notify_result = array(
                    'out_trade_no' => $out_trade_no, #商户订单号
                    'trade_no' => input('param.trade_no'), #交易凭据单号
                    'total_fee' => input('param.total_amount'), #涉及金额
                    'order_type' => input('param.body'),
                    'trade_status' => '1',
                );
            }
        }
        return $notify_result;
    }

    /**
     * 原路退款
     */
    public function trade_refund($order_info, $refund_amount) {
        require_once dirname(__FILE__) . '/aop/AopCertClient.php';
        require_once dirname(__FILE__) . '/aop/request/AlipayTradeRefundRequest.php';
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
        $request = new AlipayTradeRefundRequest ();
        $request->setBizContent("{" .
                "\"out_request_no\":\"".$order_info['out_request_no']."\"," .
                "\"trade_no\":\"".$order_info['trade_no']."\"," .
                "\"refund_amount\":".$refund_amount."," .
                "\"refund_reason\":\"".'订单退款'."\"" .
                "  }");
        $result = $aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return ds_callback(TRUE, $result->$responseNode->msg);
        } else {
            return ds_callback(FALSE, $result->$responseNode->sub_msg);
        }
    }

    /* 统一转账 */

    public function fund_transfer($withdraw_info) {

        require_once dirname(__FILE__) . '/aop/AopCertClient.php';
        require_once dirname(__FILE__) . '/aop/request/AlipayFundTransUniTransferRequest.php';


        /**
         * 证书类型AopCertClient功能方法使用测试，特别注意支付宝根证书预计2037年会过期，请在适当时间下载更新支付更证书
         * 1、execute 证书模式调用示例
         * 2、sdkExecute 证书模式调用示例
         * 3、pageExecute 证书模式调用示例
         */
//1、execute 使用
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

        $request = new AlipayFundTransUniTransferRequest ();
        $request->setBizContent("{" .
                "\"out_biz_no\":\"" . $withdraw_info['pdc_sn'] . "\"," .
                "\"trans_amount\":" . $withdraw_info['pdc_amount'] . "," .
                "\"product_code\":\"TRANS_ACCOUNT_NO_PWD\"," .
                "\"biz_scene\":\"DIRECT_TRANSFER\"," .
                "\"order_title\":\"" . config('ds_config.site_name') . "账户提现\"," .
                "\"original_order_id\":\"\"," .
                "\"payee_info\":{" .
                "\"identity\":\"" . $withdraw_info['pdc_bank_no'] . "\"," .
                "\"identity_type\":\"ALIPAY_LOGON_ID\"," .
                "\"name\":\"" . $withdraw_info['pdc_bank_user'] . "\"" .
                "    }," .
                "\"remark\":\"\"" .
                "  }");
        $result = $aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return ds_callback(TRUE, $result->$responseNode->msg,array('pdc_trade_sn'=>$result->$responseNode->order_id));
        } else {
            return ds_callback(FALSE, $result->$responseNode->sub_msg);
        }
    }

}
