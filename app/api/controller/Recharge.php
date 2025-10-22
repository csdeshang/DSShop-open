<?php

namespace app\api\controller;

use think\facade\Lang;
use think\facade\Db;

/**
 * ============================================================================
 * DSShop单店铺商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 充值控制器
 */
class Recharge extends MobileMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '/predeposit.lang.php');
    }

    /**
     * @api {POST} api/Recharge/index 新增充值信息
     * @apiVersion 3.0.6
     * @apiGroup Recharge
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {Float} pdr_amount 充值金额
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {String} result.pay_sn  支付单号
     */
    public function index() {
        $pdr_amount = abs(floatval(input('post.pdr_amount')));
        if ($pdr_amount > 10000000) {
            ds_json_encode(10001, '充值金额不能大于1000万');
        }

        if ($pdr_amount <= 0) {
            ds_json_encode(10001, '充值金额不正确!');
        } else {
            $predeposit_model = model('predeposit');
            $data = array();
            $data['pdr_sn'] = $pay_sn = makePaySn($this->member_info['member_id']);
            $data['pdr_member_id'] = $this->member_info['member_id'];
            $data['pdr_member_name'] = $this->member_info['member_name'];
            $data['pdr_amount'] = $pdr_amount;
            $data['pdr_addtime'] = TIMESTAMP;
            $insert = $predeposit_model->addPdRecharge($data);
            if ($insert) {
                ds_json_encode(10000, '', array('pay_sn' => $pay_sn));
            } else {
                ds_json_encode(10001, '提交失败!');
            }
        }
    }

    /**
     * @api {POST} api/Recharge/pd_cash_add 申请提现
     * @apiVersion 3.0.6
     * @apiGroup Recharge
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {String} pdc_bank_name 银行名称
     * @apiParam {String} pdc_bank_no 银行卡号
     * @apiParam {String} pdc_bank_user 银行用户名
     * @apiParam {String} password 支付密码
     * @apiParam {Float} pdc_amount 提现金额
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function pd_cash_add() {
        $pdc_amount = abs(floatval(input('post.pdc_amount')));

        $memberbank_id = intval(input('param.memberbank_id'));
        if ($memberbank_id > 0) {
            $memberbank = model('memberbank')->getMemberbankInfo(array('member_id' => session('member_id'), 'memberbank_id' => $memberbank_id));
            if (empty($memberbank)) {
                ds_json_encode(10001, lang('param_error'));
            }
            $pdc_bank_type = $memberbank['memberbank_type'];
            $pdc_bank_name = $memberbank['memberbank_type'] == 'alipay' ? lang('pay_method_alipay') : $memberbank['memberbank_name'];
            $pdc_bank_no = $memberbank['memberbank_no'];
            $pdc_bank_user = $memberbank['memberbank_truename'];
        } elseif ($memberbank_id == -1) {//使用微信
            if (!empty($this->member_info['member_h5_wxopenid'])) {
                $pdc_bank_type = 'weixin';
                $pdc_bank_name = lang('pay_method_wechat');
                $pdc_bank_no = $this->member_info['member_h5_wxopenid'];
                $pdc_bank_user = $this->member_info['member_wxnickname'];
            } else {
                ds_json_encode(10001, lang('param_error'));
            }
        } else {
            ds_json_encode(10001, lang('param_error'));
        }
        $data = [
            'pdc_amount' => $pdc_amount,
            'pdc_bank_type' => $pdc_bank_type,
            'pdc_bank_name' => $pdc_bank_name,
            'pdc_bank_no' => $pdc_bank_no,
            'pdc_bank_user' => $pdc_bank_user,
            'password' => input('post.password')
        ];

        $recharge_validate = ds_validate('predeposit');
        if (!$recharge_validate->scene('pd_cash_add')->check($data)) {
            ds_json_encode(10001, $recharge_validate->getError());
        }

        $predeposit_model = model('predeposit');
        $member_model = model('member');
        $memberinfo = $member_model->getMemberInfoByID($this->member_info['member_id']);
        //验证支付密码
        if (md5(input('post.password')) != $memberinfo['member_paypwd']) {
            ds_json_encode(10001, '支付密码错误');
        }
        //验证金额是否足够
        if (floatval($memberinfo['available_predeposit']) < $pdc_amount) {
            ds_json_encode(10001, '金额不足本次提现');
        }
        //是否超过提现周期
        $condition = array();
        $condition[] = array('pdc_member_id', '=', $this->member_info['member_id']);
        $condition[] = array('pdc_payment_state', 'in', [0, 1]);
        $condition[] = array('pdc_addtime', '>', TIMESTAMP - intval(config('ds_config.member_withdraw_cycle')) * 86400);
        $last_withdraw = $predeposit_model->getPdcashInfo($condition);
        if ($last_withdraw) {
            ds_json_encode(10001, lang('predeposit_last_withdraw_time_error') . date('Y-m-d', $last_withdraw['pdc_addtime'] + (intval(config('ds_config.member_withdraw_cycle')) * 86400)));
        }
        //是否不小于最低提现金额
        if ($pdc_amount < floatval(config('ds_config.member_withdraw_min'))) {
            ds_json_encode(10001, lang('predeposit_withdraw_min') . config('ds_config.member_withdraw_min') . lang('ds_yuan'));
        }
        //是否不超过最高提现金额
        if ($pdc_amount > floatval(config('ds_config.member_withdraw_max'))) {
            ds_json_encode(10001, lang('predeposit_withdraw_max') . config('ds_config.store_withdraw_max') . lang('ds_yuan'));
        }

        Db::startTrans();
        try {

            $pdc_sn = makePaySn($memberinfo['member_id']);
            $data = array();
            $data['pdc_sn'] = $pdc_sn;
            $data['pdc_member_id'] = $memberinfo['member_id'];
            $data['pdc_member_name'] = $memberinfo['member_name'];
            $data['pdc_bank_type'] = $pdc_bank_type;
            $data['pdc_amount'] = $pdc_amount;
            $data['pdc_bank_name'] = $pdc_bank_name;
            $data['pdc_bank_no'] = $pdc_bank_no;
            $data['pdc_bank_user'] = $pdc_bank_user;
            $data['pdc_addtime'] = TIMESTAMP;
            $data['pdc_payment_state'] = 0;

            $insert = $predeposit_model->addPdcash($data);
            if (!$insert) {
                ds_json_encode(10001, '提交失败！');
            }
            //冻结可用预存款
            $data = array();
            $data['member_id'] = $memberinfo['member_id'];
            $data['member_name'] = $memberinfo['member_name'];
            $data['amount'] = $pdc_amount;
            $data['order_sn'] = $pdc_sn;
            $predeposit_model->changePd('cash_apply', $data);
            Db::commit();
            ds_json_encode(10000, lang('ds_common_op_succ'), array('status' => 'ok'));
        } catch (\Exception $e) {
            Db::rollback();
            ds_json_encode(10001, '系统繁忙，提交失败');
        }
    }

    /**
     * @api {POST} api/Recharge/recharge_order 获取充值信息
     * @apiVersion 3.0.6
     * @apiGroup Recharge
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {String} paysn 充值单号
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object} result.payment_list  返回数据
     * @apiSuccess {String} result.payment_list.payment_code  支付方式代码
     * @apiSuccess {String} result.payment_list.payment_name  支付方式名称
     * @apiSuccess {Object} result.pdinfo  充值信息 （返回字段参考pdrecharge表）
     * @apiSuccess {Object} result.base_site_url  域名
     */
    public function recharge_order() {
        $pay_sn = input('param.paysn');
        if (!preg_match('/^\d{20}$/', $pay_sn)) {
            ds_json_encode(10001, '订单号错误!');
            exit();
        }

        //查询支付单信息
        $predeposit_model = model('predeposit');
        $pd_info = $predeposit_model->getPdRechargeInfo(array('pdr_sn' => $pay_sn, 'pdr_member_id' => $this->member_info['member_id']));
        if (empty($pd_info)) {
            ds_json_encode(10001, '订单不存在!');
            exit();
        }
        if (intval($pd_info['pdr_payment_state'])) {
            ds_json_encode(10001, '您的订单已经支付，请勿重复支付!');
            exit();
        }


        $payment_model = model('payment');

        $condition = array();
        $condition[] = array('payment_platform', '=', 'h5');
        $payment_list = $payment_model->getPaymentOpenList($condition);
        $payment_array = array();
        if (!empty($payment_list)) {
            foreach ($payment_list as $value) {
                $payment_array[] = array('payment_code' => $value['payment_code'], 'payment_name' => $value['payment_name']);
            }
        } else {
            ds_json_encode(10001, '暂未找到合适的支付方式!');
            exit();
        }
        unset($pd_info['pdr_payment_code']);
        unset($pd_info['pdr_trade_sn']);
        unset($pd_info['pdr_payment_state']);
        unset($pd_info['pdr_paymenttime']);
        unset($pd_info['pdr_admin']);

        ds_json_encode(10000, '', array('payment_list' => $payment_array, 'pdinfo' => $pd_info, 'base_site_url' => BASE_SITE_URL));
    }

    public function member_v() {
        $member_info = array();
        $member_info['user_name'] = $this->member_info['member_name'];
        $member_info['avator'] = get_member_avatar_for_id($this->member_info['member_id']);
        $member_info['point'] = $this->member_info['member_points'];
        $member_gradeinfo = model('member')->getOneMemberGrade(intval($this->member_info['member_exppoints']));
        $member_info['level_name'] = $member_gradeinfo['level_name'];
        $member_info['favorites_goods'] = model('favorites')->getGoodsFavoritesCountByMemberId($this->member_info['member_id']);
        $member_info['member_id'] = $this->member_info['member_id']; //

        $member_info['member_id_64'] = base64_encode(intval($this->member_info['member_id']) * 1); //
        $list_setting = rkcache('config', true);
        $member_info['vip_1fee'] = $list_setting['vip_1fee'];
        $member_info['vip_2fee'] = $list_setting['vip_2fee'];
        ds_json_encode(10000, '', array('member_info' => $member_info));
    }

    /**
     * 在线升级到会员级别
     */
    public function recharge_vip1() {
        $pdr_amount = abs(floatval(input('post.pdr_amount')));
        $list_setting = rkcache('config', true);
        if ($pdr_amount <= 0 || $pdr_amount != abs(floatval($list_setting['vip_1fee']))) {

            ds_json_encode(10001, '金额参数错误!');
            exit();
        }

        $predeposit_model = model('predeposit');

        $data = array();

        $data['pdr_sn'] = $pay_sn = makePaySn($this->member_info['member_id']);

        $data['pdr_member_id'] = $this->member_info['member_id'];

        $data['pdr_member_name'] = $this->member_info['member_name'];

        $data['pdr_amount'] = $pdr_amount;

        $data['pdr_addtime'] = TIMESTAMP;

        $data['pdr_vipid'] = '1';

        $insert = $predeposit_model->addVipRecharge($data);

        if ($insert) {
            ds_json_encode(10000, '', array('pay_sn' => $pay_sn));
        } else {
            ds_json_encode(10001, '参数错误!');
        }
    }

    public function recharge_vip2() {
        $pdr_amount = abs(floatval(input('post.pdr_amount')));
        $list_setting = rkcache('config', true);
        if ($pdr_amount <= 0 || $pdr_amount != abs(floatval($list_setting['vip_2fee']))) {
            ds_json_encode(10001, '金额参数错误!');
            exit();
        }

        $predeposit_model = model('predeposit');
        $data = array();
        $data['pdr_sn'] = $pay_sn = makePaySn($this->member_info['member_id']);
        $data['pdr_member_id'] = $this->member_info['member_id'];
        $data['pdr_member_name'] = $this->member_info['member_name'];
        $data['pdr_amount'] = $pdr_amount;
        $data['pdr_addtime'] = TIMESTAMP;
        $data['pdr_vipid'] = '2';
        $insert = $predeposit_model->addVipRecharge($data);

        if ($insert) {
            ds_json_encode(10000, '', array('pay_sn' => $pay_sn));
        } else {
            ds_json_encode(10001, '参数错误!');
        }
    }

    public function viprecharge_order() {
        $pay_sn = input('post.paysn');
        if (!preg_match('/^\d{20}$/', $pay_sn)) {
            ds_json_encode(10001, '订单号错误!');
            exit();
        }

        //查询支付单信息
        $predeposit_model = model('predeposit');
        $pd_info = $predeposit_model->getVipRechargeInfo(array('pdr_sn' => $pay_sn, 'pdr_member_id' => $this->member_info['member_id']));
        if (empty($pd_info)) {
            ds_json_encode(10001, '订单不存在!');
            exit();
        }
        if (intval($pd_info['pdr_payment_state'])) {
            ds_json_encode(10001, '您的订单已经支付，请勿重复支付!');
            exit();
        }


        $payment_model = model('payment');
        $condition = array();
        $condition[] = array('payment_platform', '=', 'h5');
        $payment_list = $payment_model->getPaymentOpenList($condition);
        $payment_array = array();
        if (!empty($payment_list)) {
            foreach ($payment_list as $value) {
                $payment_array[] = array('payment_code' => $value['payment_code'], 'payment_name' => $value['payment_name']);
            }
        } else {
            ds_json_encode(10001, '暂未找到合适的支付方式');
            exit();
        }
        unset($pd_info['pdr_payment_code']);
        unset($pd_info['pdr_trade_sn']);
        unset($pd_info['pdr_payment_state']);
        unset($pd_info['pdr_paymenttime']);
        unset($pd_info['pdr_admin']);
        ds_json_encode(10000, '', array('payment_list' => $payment_array, 'pdinfo' => $pd_info));
    }
}

?>
