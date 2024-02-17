<?php

namespace app\common\logic;

use think\facade\Db;

class Predeposit {

    /**
     * 自动转账
     */
    function pdcash_pay_auto($pdc_id) {

        $predeposit_model = model('predeposit');
        $condition = array();
        $condition[] = array('pdc_id', '=', $pdc_id);
        $condition[] = array('pdc_payment_state', '=', 0);
        $info = $predeposit_model->getPdcashInfo($condition);

        if (empty($info)) {
            return ds_callback(false, '订单不存在');
        }
        if ($info['pdc_bank_type'] == 'alipay') {
            $logic_payment = model('payment', 'logic');
            $payment_code = 'alipay';
            $result = $logic_payment->getPaymentInfo($payment_code);
            if ($result['code']) {
                $payment_info = $result['data'];
                if ($payment_info['payment_config']['alipay_trade_transfer_state'] == 1) {
                    $payment_api = new $payment_code($payment_info);
                    $result = $payment_api->fund_transfer($info);
                    if (!$result['code']) {
                        return ds_callback(false, $result['msg']);
                    }
                    $pdc_trade_sn = $result['data']['pdc_trade_sn'];
                    return $this->pdcash_pay_auto_1($info, $payment_code, $pdc_trade_sn);
//                    $result = $predeposit_model->editPdcash(array('pdc_payment_code' => $payment_code, 'pdc_trade_sn' => $result['data']['pdc_trade_sn']), array('pdc_id' => $id));
                    if (!$result) {
                        return ds_callback(false, '支付宝自动支付失败');
                    }
                }
            }
        } elseif ($info['pdc_bank_type'] == 'weixin') {

            $logic_payment = model('payment', 'logic');
            $payment_code = 'wxpay_native';
            $result = $logic_payment->getPaymentInfo($payment_code);
            if ($result['code']) {
                $payment_info = $result['data'];
                if ($payment_info['payment_config']['wx_trade_transfer_state'] == 1) {
                    $payment_api = new $payment_code($payment_info);
                    $result = $payment_api->fund_transfer($info);
                    if (!$result['code']) {
                        return ds_callback(false, $result['msg']);
                    }
                    $pdc_trade_sn = $result['data']['pdc_trade_sn'];
                    return $this->pdcash_pay_auto_1($info, $payment_code, $pdc_trade_sn);
//                        $result = $predeposit_model->editPdcash(array('pdc_payment_code'=>$payment_code,'pdc_trade_sn'=>$result['data']['pdc_trade_sn']), array('pdc_id'=>$id));
                    if (!$result) {
                        return ds_callback(false, '微信自动支付失败');
                    }
                }
            }
        }else{
            return ds_callback(false, '系统自动支付失败');
        }
    }

    // 自动转账完成，系统进行自动扣款，修改状态
    function pdcash_pay_auto_1($info, $payment_code, $pdc_trade_sn) {

        Db::startTrans();
        try {
            //查询用户信息
            $member_model = model('member');
            $member_info = $member_model->getMemberInfo(array('member_id' => $info['pdc_member_id']));

            $predeposit_model = model('predeposit');

            //扣除冻结的预存款
            $data = array();
            $data['member_id'] = $member_info['member_id'];
            $data['member_name'] = $member_info['member_name'];
            $data['amount'] = $info['pdc_amount'];
            $data['order_sn'] = $info['pdc_sn'];
            $data['admin_name'] = '系统';
            $predeposit_model->changePd('cash_pay', $data);

            $update = array();
            $update['pdc_payment_state'] = 1;
            $update['pdc_payment_admin'] = '系统';
            $update['pdc_payment_time'] = TIMESTAMP;
            $update['pdc_payment_code'] = $payment_code;
            $update['pdc_trade_sn'] = $pdc_trade_sn;

            $condition = array();
            $condition[] = array('pdc_id', '=', $info['pdc_id']);

            $result = $predeposit_model->editPdcash($update, $condition);
            
            return ds_callback(true, '系统自动支付成功');

            Db::commit();
        } catch (\Exception $e) {

            return ds_callback(false, $e->getMessage());
            Db::rollback();
        }
    }
}
