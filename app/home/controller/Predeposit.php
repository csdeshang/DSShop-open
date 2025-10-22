<?php

/**
 * 预存款管理
 */

namespace app\home\controller;

use think\facade\View;
use think\facade\Db;
use think\facade\Lang;

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
 * 控制器
 */
class Predeposit extends BaseMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '/predeposit.lang.php');
    }

    /**
     * 充值添加
     */
    public function recharge_add() {
        if (!request()->isPost()) {
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('predeposit');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('recharge_add');
            return View::fetch($this->template_dir . 'pd_recharge_add');
        } else {
            $pdr_amount = abs(floatval(input('post.pdr_amount')));
            if ($pdr_amount <= 0) {
                $this->error(lang('predeposit_recharge_add_pricemin_error'));
            }
            $predeposit_model = model('predeposit');
            $data = array();
            $data['pdr_sn'] = $pay_sn = makePaySn(session('member_id'));
            $data['pdr_member_id'] = session('member_id');
            $data['pdr_member_name'] = session('member_name');
            $data['pdr_amount'] = $pdr_amount;
            $data['pdr_addtime'] = TIMESTAMP;
            $insert = $predeposit_model->addPdRecharge($data);
            if ($insert) {
                //转向到商城支付页面
                $this->redirect(url('Buy/pd_pay', ['pay_sn' => $pay_sn]));
            }
        }
    }

    /**
     * 平台充值卡
     */
    public function rechargecard_add() {
        if (!request()->isPost()) {
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('predeposit');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('rechargecard_add');
            return View::fetch($this->template_dir . 'rechargecard_add');
            return;
        } else {
            $sn = (string) input('post.rc_sn');
            if (!$sn || strlen($sn) > 50) {
                $this->error(lang('platform_recharge_card_number_cannot_empty'));
                exit;
            }

            try {
                $res = model('predeposit')->addRechargecard($sn, $this->member_info);
                if ($res['message']) {
                    $this->error($res['message']);
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success(lang('platform_recharge_card_successfully_used'), url('Predeposit/rcb_log_list'));
        }
    }

    /**
     * 充值列表
     */
    public function index() {
        $condition = array();
        $condition[] = array('pdr_member_id', '=', session('member_id'));
        $pdr_sn = input('pdr_sn');
        if (!empty($pdr_sn)) {
            $condition[] = array('pdr_sn', '=', $pdr_sn);
        }

        $predeposit_model = model('predeposit');
        $predeposit_list = $predeposit_model->getPdRechargeList($condition, 10, '*', 'pdr_id desc');

        View::assign('predeposit_list', $predeposit_list);
        View::assign('show_page', $predeposit_model->page_info->render());

        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('predeposit');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('recharge_list');
        return View::fetch($this->template_dir . 'pd_recharge_list');
    }

    /**
     * 查看充值详细
     *
     */
    public function recharge_show() {
        $pdr_id = intval(input('param.id'));
        if ($pdr_id <= 0) {
            $this->error(lang('param_error'));
        }

        $predeposit_model = model('predeposit');
        $condition = array();
        $condition[] = array('pdr_member_id', '=', session('member_id'));
        $condition[] = array('pdr_id', '=', $pdr_id);
        $condition[] = array('pdr_payment_state', '=', 1);
        $info = $predeposit_model->getPdRechargeInfo($condition);
        if (!$info) {
            $this->error(lang('predeposit_record_error'));
        }
        View::assign('info', $info);
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('predeposit');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('recharge_show');
        return View::fetch($this->template_dir . 'recharge_show');
    }

    /**
     * 删除充值记录
     *
     */
    public function recharge_del() {
        $pdr_id = intval(input('param.id'));
        if ($pdr_id <= 0) {
            ds_json_encode(10001, lang('predeposit_parameter_error'));
        }

        $predeposit_model = model('predeposit');
        $condition = array();
        $condition[] = array('pdr_member_id', '=', session('member_id'));
        $condition[] = array('pdr_id', '=', $pdr_id);
        $condition[] = array('pdr_payment_state', '=', 0);
        $result = $predeposit_model->delPdRecharge($condition);
        if ($result) {
            ds_json_encode(10000, lang('ds_common_del_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_del_fail'));
        }
    }

    /**
     * 预存款变更日志
     */
    public function pd_log_list() {
        $condition = array();
        $condition[] = array('lg_member_id', '=', session('member_id'));

        $predeposit_model = model('predeposit');
        $predeposit_list = $predeposit_model->getPdLogList($condition, 10, '*', 'lg_id desc');

        View::assign('show_page', $predeposit_model->page_info->render());
        View::assign('predeposit_list', $predeposit_list);
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('predeposit');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('loglist');
        return View::fetch($this->template_dir . 'pd_log_list');
    }

    /**
     * 充值卡余额变更日志
     */
    public function rcb_log_list() {
        $rcblog_model = model('rcblog');
        $rcblog_list = $rcblog_model->getRechargecardBalanceLogList(array('member_id' => session('member_id')), 10, 'rcblog_id desc');

        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('predeposit');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('rcb_log_list');
        View::assign('show_page', $rcblog_model->page_info->render());
        View::assign('rcblog_list', $rcblog_list);
        return View::fetch($this->template_dir . 'rcb_log_list');
    }

    /**
     * 申请提现
     */
    public function pd_cash_add() {
        if (request()->isPost()) {
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

            $predeposit_validate = ds_validate('predeposit');
            if (!$predeposit_validate->scene('pd_cash_add')->check($data)) {
                ds_json_encode(10001, $predeposit_validate->getError());
            }

            $predeposit_model = model('predeposit');
            $member_model = model('member');
            $member_info = $member_model->getMemberInfoByID(session('member_id'));
            //验证支付密码
            if (md5(input('post.password')) != $member_info['member_paypwd']) {
                ds_json_encode(10001, lang('payment_password_error'));
            }
            //验证金额是否足够
            if (floatval($member_info['available_predeposit']) < $pdc_amount) {
                ds_json_encode(10001, lang('predeposit_cash_shortprice_error'));
            }
            //是否超过提现周期
            $condition = array();
            $condition[] = array('pdc_member_id', '=', $this->member_info['member_id']);
            $condition[] = array('pdc_payment_state', 'in', [0, 1]);
            $condition[] = array('pdc_addtime', '>', TIMESTAMP - intval(config('ds_config.member_withdraw_cycle')) * 86400);
            $last_withdraw = $predeposit_model->getPdcashInfo($condition);
            if ($last_withdraw) {
                ds_json_encode(10001, lang('predeposit_last_withdraw_time_error') . date('Y-m-d', $last_withdraw['pdc_addtime']));
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
                $pdc_sn = makePaySn(session('member_id'));
                $data = array();
                $data['pdc_sn'] = $pdc_sn;
                $data['pdc_member_id'] = session('member_id');
                $data['pdc_member_name'] = session('member_name');
                $data['pdc_bank_type'] = $pdc_bank_type;
                $data['pdc_amount'] = $pdc_amount;
                $data['pdc_bank_name'] = $pdc_bank_name;
                $data['pdc_bank_no'] = $pdc_bank_no;
                $data['pdc_bank_user'] = $pdc_bank_user;
                $data['pdc_addtime'] = TIMESTAMP;
                $data['pdc_payment_state'] = 0;
                $insert = $predeposit_model->addPdcash($data);
                if (!$insert) {
                    throw new \think\Exception(lang('predeposit_cash_add_fail'), 10001);
                }
                //冻结可用预存款
                $data = array();
                $data['member_id'] = $member_info['member_id'];
                $data['member_name'] = $member_info['member_name'];
                $data['amount'] = $pdc_amount;
                $data['order_sn'] = $pdc_sn;
                $predeposit_model->changePd('cash_apply', $data);
                Db::commit();
                ds_json_encode(10000, lang('predeposit_cash_add_success'));
            } catch (\Exception $e) {
                Db::rollback();
                ds_json_encode(10001, $e->getMessage());
            }
        } else {
            $memberbank_list = model('memberbank')->getMemberbankList(array('member_id' => session('member_id')));
            if (!empty($this->member_info['member_h5_wxopenid'])) {
                if (empty($memberbank_list)) {
                    $memberbank_list = array();
                }
                $memberbank_list[] = array('memberbank_id' => -1, 'memberbank_type' => 'weixin', 'memberbank_no' => $this->member_info['member_wxnickname']);
            }
            View::assign('memberbank_list', $memberbank_list);
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('predeposit');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('pd_cash_add');
            return View::fetch($this->template_dir . 'pd_cash_add');
        }
    }

    /**
     * 提现列表
     */
    public function pd_cash_list() {
        $condition = array();
        $condition[] = array('pdc_member_id', '=', session('member_id'));

        $sn_search = input('post.sn_search');
        if (!empty($sn_search)) {
            $condition[] = array('pdc_sn', '=', $sn_search);
        }
        $paystate_search = input('post.paystate_search');
        if (isset($paystate_search)) {
            $condition[] = array('pdc_payment_state', '=', intval($paystate_search));
        }

        $pdcash_list = Db::name('pdcash')->where($condition)->order('pdc_id desc')->paginate();
        View::assign('pdcash_list', $pdcash_list);
        View::assign('show_page', $pdcash_list->render());

        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('predeposit');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('cashlist');
        return View::fetch($this->template_dir . 'pd_cash_list');
    }

    /**
     * 提现记录详细
     */
    public function pd_cash_info() {
        $pdc_id = intval(input('param.id'));
        if ($pdc_id <= 0) {
            $this->error(lang('param_error'), 'home/predeposit/pd_cash_list');
        }
        $predeposit_model = model('predeposit');
        $condition = array();
        $condition[] = array('pdc_member_id', '=', session('member_id'));
        $condition[] = array('pdc_id', '=', $pdc_id);
        $info = $predeposit_model->getPdcashInfo($condition);
        if (empty($info)) {
            $this->error(lang('predeposit_record_error'), 'home/predeposit/pd_cash_list');
        }

        $this->setMemberCurItem('cashinfo');
        $this->setMemberCurMenu('predeposit');
        View::assign('info', $info);
        return View::fetch($this->template_dir . 'pd_cash_info');
    }

    /**
     *    栏目菜单
     */
    function getMemberItemList() {
        $item_list = array(
            array(
                'name' => 'loglist',
                'text' => lang('detail_list'),
                'url' => url('Predeposit/pd_log_list'),
            ),
            array(
                'name' => 'recharge_list',
                'text' => lang('prepaid_phone_list'),
                'url' => url('Predeposit/index'),
            ),
            array(
                'name' => 'cashlist',
                'text' => lang('withdrawal_list'),
                'url' => url('Predeposit/pd_cash_list'),
            ),
            array(
                'name' => 'rcb_log_list',
                'text' => lang('balance_recharge_card'),
                'url' => url('Predeposit/rcb_log_list'),
            ),
        );

        if (request()->action() == 'rechargeinfo') {
            $item_list[] = array(
                'name' => 'rechargeinfo',
                'text' => lang('ds_member_path_predeposit_rechargeinfo'),
                'url' => url('Predeposit/rechargeinfo'),
            );
        }

        if (request()->action() == 'recharge_add') {
            $item_list[] = array(
                'name' => 'recharge_add',
                'text' => lang('predeposit_online_recharge'),
                'url' => url('Predeposit/recharge_add'),
            );
        }

        if (request()->action() == 'rechargecard_add') {
            $item_list[] = array(
                'name' => 'rechargecard_add',
                'text' => lang('predeposit_recharge_card_recharge'),
                'url' => url('Predeposit/rechargecard_add'),
            );
        }

        if (request()->action() == 'cashadd') {
            $item_list[] = array(
                'name' => 'cashadd',
                'text' => lang('ds_member_path_predeposit_cashadd'),
                'url' => url('Predeposit/cashadd'),
            );
        }

        if (request()->action() == 'cashinfo') {
            $item_list[] = array(
                'name' => 'cashinfo',
                'text' => lang('ds_member_path_predeposit_cashinfo'),
                'url' => url('Predeposit/cashinfo'),
            );
        }
        if (request()->action() == 'pd_cash_add') {
            $item_list[] = array(
                'name' => 'pd_cash_add',
                'text' => lang('predeposit_application_withdrawal'),
                'url' => (string) url('Predeposit/pd_cash_add'),
            );
        }

        return $item_list;
    }
}
