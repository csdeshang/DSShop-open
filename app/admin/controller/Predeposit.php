<?php

namespace app\admin\controller;
use think\facade\View;
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
 * 控制器
 */
class Predeposit extends AdminControl {

    const EXPORT_SIZE = 1000;
    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/predeposit.lang.php');
    }

    /*
     * 充值明细
     */

    public function pdrecharge_list() {
        $condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', input('param.query_start_date'));
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', input('param.query_end_date'));
        $start_unixtime = $if_start_date ? strtotime(input('param.query_start_date')) : null;
        $end_unixtime = $if_end_date ? strtotime(input('param.query_end_date')) : null;
        if ($start_unixtime) {
            $condition[]=array('pdr_addtime','>=', $start_unixtime);
        }
        if ($end_unixtime) {
            $end_unixtime=$end_unixtime+86399;
            $condition[]=array('pdr_addtime','<=', $end_unixtime);
        }
        if (input('param.mname') != '') {
            $condition[] = array('pdr_member_name','like', "%" . input('param.mname') . "%");
        }
        if (input('param.paystate_search') != '') {
            $condition[] = array('pdr_payment_state','=',input('param.paystate_search'));
        }
        $predeposit_model = model('predeposit');
        $recharge_list = $predeposit_model->getPdRechargeList($condition, 20, '*', 'pdr_id desc');
        View::assign('recharge_list', $recharge_list);
        View::assign('show_page', $predeposit_model->page_info->render());
        
        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        
        $this->setAdminCurItem('pdrecharge_list');
        return View::fetch();
    }

    /**
     * 充值编辑(更改成收到款)
     */
    public function recharge_edit() {
        $id = intval(input('param.id'));
        if ($id <= 0) {
            $this->error(lang('param_error'), 'Predeposit/pdrecharge_list');
        }
        //查询充值信息
        $predeposit_model = model('predeposit');
        $condition = array();
        $condition[] = array('pdr_id','=',$id);
        $condition[] = array('pdr_payment_state','=',0);
        $info = $predeposit_model->getPdRechargeInfo($condition);
        if (empty($info)) {
            $this->error(lang('admin_predeposit_record_error'), 'Predeposit/pdrecharge_list');
        }
        if (!request()->isPost()) {
            //显示支付接口列表
            $payment_list = model('payment')->getPaymentOpenList();
            //去掉预存款和货到付款
            foreach ($payment_list as $key => $value) {
                if ($value['payment_code'] == 'predeposit') {
                    unset($payment_list[$key]);
                }
            }
            View::assign('payment_list', $payment_list);
            View::assign('info', $info);
            return View::fetch('recharge_edit');
        }

        //取支付方式信息
        $payment_model = model('payment');
        $condition = array();
        $condition[] = array('payment_code','=',input('post.payment_code'));
        $payment_info = $payment_model->getPaymentOpenInfo($condition);
        if (!$payment_info) {
            $this->error(lang('payment_index_sys_not_support'));
        }

        $condition = array();
        $condition[] = array('pdr_sn','=',$info['pdr_sn']);
        $condition[] = array('pdr_payment_state','=',0);
        $update = array();
        $update['pdr_payment_state'] = 1;
        $update['pdr_paymenttime'] = strtotime(input('post.payment_time'));
        $update['pdr_payment_code'] = $payment_info['payment_code'];
        $update['pdr_trade_sn'] = input('post.trade_no');
        $update['pdr_admin'] = $this->admin_info['admin_name'];
        $log_msg = lang('admin_predeposit_recharge_edit_state') . ',' . lang('admin_predeposit_sn') . ':' . $info['pdr_sn'];

        Db::startTrans();
        try {
            
            //更改充值状态
            $state = $predeposit_model->editPdRecharge($update, $condition);
            if (!$state) {
                throw Exception(lang('predeposit_payment_pay_fail'));
            }
            //变更会员预存款
            $data = array();
            $data['member_id'] = $info['pdr_member_id'];
            $data['member_name'] = $info['pdr_member_name'];
            $data['amount'] = $info['pdr_amount'];
            $data['pdr_sn'] = $info['pdr_sn'];
            $data['admin_name'] = $this->admin_info['admin_name'];
            $predeposit_model->changePd('recharge', $data);
            Db::commit();
            $this->log($log_msg, 1);
            $this->success(lang('admin_predeposit_recharge_edit_success'), 'Predeposit/pdrecharge_list');
        } catch (\Exception $e) {
            Db::rollback();
            $this->log($log_msg, 0);
            $this->error($e->getMessage(), 'Predeposit/pdrecharge_list');
        }
    }

    /**
     * 充值查看
     */
    public function recharge_info() {
        $id = intval(input('param.id'));
        if ($id <= 0) {
            $this->error(lang('param_error'), 'Predeposit/pdrecharge_list');
        }
        //查询充值信息
        $predeposit_model = model('predeposit');
        $condition = array();
        $condition[] = array('pdr_id','=',$id);
        $info = $predeposit_model->getPdRechargeInfo($condition);
        if (empty($info)) {
            $this->error(lang('admin_predeposit_record_error'), 'Predeposit/pdrecharge_list');
        }
        View::assign('info', $info);
        return View::fetch('recharge_info');
    }

    /**
     * 充值删除
     */
    public function recharge_del() {
        $pdr_id = input('param.pdr_id');
        $pdr_id_array = ds_delete_param($pdr_id);
        if($pdr_id_array === FALSE){
            ds_json_encode('10001', lang('param_error'));
        }
        $predeposit_model = model('predeposit');
        $condition = array();
        $condition[] = array('pdr_id','in', $pdr_id_array);
        $condition[] = array('pdr_payment_state','=',0);
        $result = $predeposit_model->delPdRecharge($condition);
        if ($result) {
            ds_json_encode('10000', lang('ds_common_del_succ'));
        } else {
            ds_json_encode('10001', lang('ds_common_del_fail'));
        }
    }



    /*
     * 预存款明细
     */

    public function pdlog_list() {
        $condition = array();
        $stime = input('get.stime');
        $etime = input('get.etime');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $stime);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $etime);
        $start_unixtime = $if_start_date ? strtotime($stime) : null;
        $end_unixtime = $if_end_date ? strtotime($etime) : null;
        if ($start_unixtime) {
            $condition[]=array('lg_addtime','>=', $start_unixtime);
        }
        if ($end_unixtime) {
            $end_unixtime=$end_unixtime+86399;
            $condition[]=array('lg_addtime','<=', $end_unixtime);
        }
        $mname = input('get.mname');
        if (!empty($mname)) {
            $condition[] = array('lg_member_name','=',$mname);
        }
        $aname = input('get.aname');
        if (!empty($aname)) {
            $condition[] = array('lg_admin_name','=',$aname);
        }
        $predeposit_model = model('predeposit');
        $list_log = $predeposit_model->getPdLogList($condition, 10, '*', 'lg_id desc');
        View::assign('show_page', $predeposit_model->page_info->render());
        View::assign('list_log', $list_log);
        
        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        
        $this->setAdminCurItem('pdlog_list');
        return View::fetch();
    }

    /*
     * 提现设置
     */
    public function pdcash_set(){
        $config_model = model('config');
        if(!request()->isPost()){
            $list_setting = rkcache('config', true);
            View::assign('list_setting',$list_setting);
            $this->setAdminCurItem('pdcash_set');
            return View::fetch();
        }else{
            $update_array=array(
                'member_withdraw_min'=>abs(round(input('post.member_withdraw_min'),2)),
                'member_withdraw_max'=>abs(round(input('post.member_withdraw_max'),2)),
                'member_withdraw_cycle'=>abs(intval(input('post.member_withdraw_cycle'))),
            );
            $result = $config_model->editConfig($update_array);
            if ($result) {
                $this->log(lang('ds_update').lang('admin_predeposit_cashset'),1);
                $this->success(lang('ds_common_op_succ'), 'Predeposit/pdcash_set');
            }else{
                $this->log(lang('ds_update').lang('admin_predeposit_cashset'),0);
            }
        }
    }
    /*
     * 提现列表
     */
    public function pdcash_list() {
        $condition = array();
        $stime = input('get.stime');
        $etime = input('get.etime');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $stime);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $etime);
        $start_unixtime = $if_start_date ? strtotime($stime) : null;
        $end_unixtime = $if_end_date ? strtotime($etime) : null;
        if ($start_unixtime) {
            $condition[]=array('pdc_addtime','>=', $start_unixtime);
        }
        if ($end_unixtime) {
            $end_unixtime=$end_unixtime+86399;
            $condition[]=array('pdc_addtime','<=', $end_unixtime);
        }
        $mname = input('get.mname');
        if (!empty($mname)) {
            $condition[] = array('pdc_member_name','like', "%" . $mname . "%");
        }
        $pdc_bank_user = input('get.pdc_bank_user');
        if (!empty($pdc_bank_user)) {
            $condition[] = array('pdc_bank_user','like', "%" . $pdc_bank_user . "%");
        }
        $paystate_search = input('get.paystate_search');
        if ($paystate_search != '') {
            $condition[] = array('pdc_payment_state','=',$paystate_search);
        }
        $predeposit_model = model('predeposit');
        $predeposit_list = $predeposit_model->getPdcashList($condition, 20, '*', 'pdc_payment_state asc,pdc_id desc');
        View::assign('predeposit_list', $predeposit_list);
        View::assign('show_page', $predeposit_model->page_info->render());
        
        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        
        $this->setAdminCurItem('pdcash_list');
        return View::fetch('pdcash_list');
    }

    /**
     * 删除提现记录
     */
    public function pdcash_del() {
        $pdc_id = intval(input('param.pdc_id'));
        if ($pdc_id <= 0) {
             ds_json_encode(10001, lang('param_error'));
        }
        $predeposit_model = model('predeposit');
        $condition = array();
        $condition[] = array('pdc_id','=',$pdc_id);
        $condition[] = array('pdc_payment_state','=',0);
        $info = $predeposit_model->getPdcashInfo($condition);
        if (!$info) {
            ds_json_encode(10001, lang('param_error'));
        }
        try {
            $result = $predeposit_model->delPdcash($condition);
            if (!$result) {
                ds_json_encode(10001, lang('admin_predeposit_cash_del_fail'));
            }
            //退还冻结的预存款
            $member_model = model('member');
            $member_info = $member_model->getMemberInfo(array('member_id' => $info['pdc_member_id']));
            //扣除冻结的预存款
            $admininfo = $this->getAdminInfo();
            $data = array();
            $data['member_id'] = $member_info['member_id'];
            $data['member_name'] = $member_info['member_name'];
            $data['amount'] = $info['pdc_amount'];
            $data['order_sn'] = $info['pdc_sn'];
            $data['admin_name'] = $admininfo['admin_name'];
            $predeposit_model->changePd('cash_del', $data);
            ds_json_encode(10000, lang('admin_predeposit_cash_del_success'));
        } catch (\Exception $e) {
            ds_json_encode(10001, lang($e->getMessage()));
        }
    }

    /**
     * 更改提现为支付状态
     */
    public function pdcash_pay() {
        $id = intval(input('param.id'));
        if ($id <= 0) {
            $this->error(lang('param_error'),'Predeposit/pdcash_list');
        }
        $predeposit_model = model('predeposit');
        $condition = array();
        $condition[] = array('pdc_id','=',$id);
        $condition[] = array('pdc_payment_state','=',0);
        $info = $predeposit_model->getPdcashInfo($condition);
        if (!is_array($info) || count($info) < 0) {
            $this->error(lang('admin_predeposit_record_error'), 'Predeposit/pdcash_list');
        }
        //查询用户信息
        $member_model = model('member');
        $member_info = $member_model->getMemberInfo(array('member_id' => $info['pdc_member_id']));

        $update = array();
        $admininfo = $this->getAdminInfo();
        $update['pdc_payment_state'] = 1;
        $update['pdc_payment_admin'] = $admininfo['admin_name'];
        $update['pdc_payment_time'] = TIMESTAMP;
        $update['pdc_trade_sn'] = input('param.pdc_trade_sn');
        $log_msg = lang('admin_predeposit_cash_edit_state') . ',' . lang('admin_predeposit_cs_sn') . ':' . $info['pdc_sn'];

        Db::startTrans();
        try {
            
            $result = $predeposit_model->editPdcash($update, $condition);
            if (!$result) {
                $this->error(lang('admin_predeposit_cash_edit_fail'));
            }
            //扣除冻结的预存款
            $data = array();
            $data['member_id'] = $member_info['member_id'];
            $data['member_name'] = $member_info['member_name'];
            $data['amount'] = $info['pdc_amount'];
            $data['order_sn'] = $info['pdc_sn'];
            $data['admin_name'] = $admininfo['admin_name'];
            $predeposit_model->changePd('cash_pay', $data);

            Db::commit();
            $this->log($log_msg, 1);
            dsLayerOpenSuccess(lang('admin_predeposit_cash_edit_success'));
        } catch (\Exception $e) {
            Db::rollback();
            $this->log($log_msg, 0);
            $this->error($e->getMessage(), 'Predeposit/pdcash_list');
        }
    }
    
    /**
     * 系统自动转账
     */
    public function pdcash_pay_auto(){
        $id = intval(input('param.id'));
        if ($id <= 0) {
            $this->error(lang('admin_predeposit_parameter_error'),'Predeposit/pdcash_list');
        }
        $logic_predeposit = model('predeposit', 'logic');
        $result = $logic_predeposit->pdcash_pay_auto($id);
        
        if (!$result['code']) {
            $this->error($result['msg']);
        }else{
            dsLayerOpenSuccess(lang('admin_predeposit_cash_edit_success'));
        }
    }

    /**
     * 查看提现信息
     */
    public function pdcash_view() {
        $id = intval(input('param.id'));
        if ($id <= 0) {
            $this->error(lang('param_error'), 'Predeposit/pdcash_list');
        }
        $predeposit_model = model('predeposit');
        $condition = array();
        $condition[] = array('pdc_id','=',$id);
        $info = $predeposit_model->getPdcashInfo($condition);
        if (!is_array($info) || count($info) < 0) {
            $this->error(lang('admin_predeposit_record_error'), 'Predeposit/pdcash_list');
        }
        
        $info['if_pdcash_pay_auto'] = false;
        //系统是否开启自动转账功能
        if ($info['pdc_bank_type'] == 'alipay') {
            $logic_payment = model('payment', 'logic');
            $payment_code = 'alipay';
            $result = $logic_payment->getPaymentInfo($payment_code);
            if ($result['code']) {
                $payment_info = $result['data'];
                if ($payment_info['payment_config']['alipay_trade_transfer_state'] == 1) {
                    $info['if_pdcash_pay_auto'] = true;
                }
            }
        } elseif ($info['pdc_bank_type'] == 'weixin') {
            $logic_payment = model('payment', 'logic');
            $payment_code = 'wxpay_native';
            $result = $logic_payment->getPaymentInfo($payment_code);
            if ($result['code']) {
                $payment_info = $result['data'];
                if ($payment_info['payment_config']['wx_trade_transfer_state'] == 1) {
                    $info['if_pdcash_pay_auto'] = true;
                }
            }
        }
        
        View::assign('info', $info);
        return View::fetch();
    }

    /*
     * 调节预存款
     */

    public function pd_add() {
        if (!(request()->isPost())) {
            $member_id = intval(input('get.member_id'));
            if($member_id>0){
                $condition = array();
                $condition[] = array('member_id','=',$member_id);
                $member = model('member')->getMemberInfo($condition);
                if(!empty($member)){
                    View::assign('member_info',$member);
                }
            }
            return View::fetch();
        } else {
            $data = array(
                'member_id' => input('post.member_id'),
                'amount' => input('post.amount'),
                'operatetype' => input('post.operatetype'),
                'lg_desc' => input('post.lg_desc'),
            );
            //验证 BEGIN
            $predeposit_validate = ds_validate('predeposit');
            if (!$predeposit_validate->scene('pd_add')->check($data)) {
                $this->error($predeposit_validate->getError());
            }
            //验证 END

            $money = abs(floatval(input('post.amount')));
            $memo = trim(input('post.lg_desc'));
            if ($money <= 0) {
                $this->error('输入的金额必需大于0');
            }
            //查询会员信息
            $member_mod = model('member');
            $member_id = intval(input('post.member_id'));
            $operatetype = input('post.operatetype');
            $member_info = $member_mod->getMemberInfo(array('member_id' => $member_id));

            if (!is_array($member_info) || count($member_info) <= 0) {
                $this->error('用户不存在', 'Predeposit/pd_add');
            }
            $available_predeposit = floatval($member_info['available_predeposit']);
            $freeze_predeposit = floatval($member_info['freeze_predeposit']);
            if ($operatetype == 2 && $money > $available_predeposit) {
                $this->error(('预存款不足，会员当前预存款') . $available_predeposit, 'Predeposit/pd_add');
            }
            if ($operatetype == 3 && $money > $available_predeposit) {
                $this->error(('可冻结预存款不足，会员当前预存款') . $available_predeposit, 'Predeposit/pd_add');
            }
            if ($operatetype == 4 && $money > $freeze_predeposit) {
                $this->error(('可恢复冻结预存款不足，会员当前冻结预存款') . $freeze_predeposit, 'Predeposit/pd_add');
            }
            $predeposit_model = model('predeposit');
            #生成对应订单号
            $order_sn = makePaySn($member_id);
            $admininfo = $this->getAdminInfo();
            $log_msg = "管理员【" . $admininfo['admin_name'] . "】操作会员【" . $member_info['member_name'] . "】预存款，金额为" . $money . ",编号为" . $order_sn;
            $admin_act = "sys_add_money";
            switch ($operatetype) {
                case 1:
                    $admin_act = "sys_add_money";
                    $log_msg = "管理员【" . $admininfo['admin_name'] . "】操作会员【" . $member_info['member_name'] . "】预存款【增加】，金额为" . $money . ",编号为" . $order_sn;
                    break;
                case 2:
                    $admin_act = "sys_del_money";
                    $log_msg = "管理员【" . $admininfo['admin_name'] . "】操作会员【" . $member_info['member_name'] . "】预存款【减少】，金额为" . $money . ",编号为" . $order_sn;
                    break;
                case 3:
                    $admin_act = "sys_freeze_money";
                    $log_msg = "管理员【" . $admininfo['admin_name'] . "】操作会员【" . $member_info['member_name'] . "】预存款【冻结】，金额为" . $money . ",编号为" . $order_sn;
                    break;
                case 4:
                    $admin_act = "sys_unfreeze_money";
                    $log_msg = "管理员【" . $admininfo['admin_name'] . "】操作会员【" . $member_info['member_name'] . "】预存款【解冻】，金额为" . $money . ",编号为" . $order_sn;
                    break;
                default:
                    $this->error(lang('ds_common_op_fail'), 'Predeposit/pdlog_list');
                    break;
            }
            Db::startTrans();
            try {
                
                //扣除冻结的预存款
                $data = array();
                $data['member_id'] = $member_info['member_id'];
                $data['member_name'] = $member_info['member_name'];
                $data['amount'] = $money;
                $data['order_sn'] = $order_sn;
                $data['admin_name'] = $admininfo['admin_name'];
                $data['pdr_sn'] = $order_sn;
                $data['lg_desc'] = $memo;
                $predeposit_model->changePd($admin_act, $data);
                Db::commit();
                $this->log($log_msg, 1);
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
//                $this->success(lang('ds_common_op_succ'), 'Predeposit/pdlog_list');
            } catch (\Exception $e) {
                Db::rollback();
                $this->log($log_msg, 0);
                $this->error($e->getMessage(), 'Predeposit/pdlog_list');
            }
        }
    }

    //取得会员信息
    public function checkmember() {
        $name = input('post.name');
        if (!$name) {
            exit(json_encode(array('id' => 0)));
            die;
        }
        $obj_member = model('member');
        $member_info = $obj_member->getMemberInfo(array('member_name' => $name));
        if (is_array($member_info) && count($member_info) > 0) {
            exit(json_encode(array('id' => $member_info['member_id'], 'name' => $member_info['member_name'], 'available_predeposit' => $member_info['available_predeposit'], 'freeze_predeposit' => $member_info['freeze_predeposit'])));
        } else {
            exit(json_encode(array('id' => 0)));
        }
    }

    /**
     * 导出预存款充值记录
     *
     */
    public function export_step1() {
        $condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', input('param.query_start_date'));
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', input('param.query_end_date'));
        $start_unixtime = $if_start_date ? strtotime(input('param.query_start_date')) : null;
        $end_unixtime = $if_end_date ? strtotime(input('param.query_end_date')) : null;
        if ($start_unixtime) {
            $condition[] = array('pdr_addtime','>=', $start_unixtime);
        }
        if ($end_unixtime) {
            $end_unixtime=$end_unixtime+86399;
            $condition[] = array('pdr_addtime','<=', $end_unixtime);
        }
        if (input('param.mname') != '') {
            $condition[] = array('pdr_member_name','like', "%" . input('param.mname') . "%");
        }
        if (input('param.paystate_search') != '') {
            $condition[] = array('pdr_payment_state','=',input('param.paystate_search'));
        }


        $predeposit_model = model('predeposit');
        if (!is_numeric(input('param.page'))) {
            $count = $predeposit_model->getPdRechargeCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) { //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                View::assign('export_list', $array);
                return View::fetch('/public/excel');
            } else { //如果数量小，直接下载
                $data = $predeposit_model->getPdRechargeList($condition, '', '*', 'pdr_id desc', self::EXPORT_SIZE);
                $rechargepaystate = array(0 => '未支付', 1 => '已支付');
                foreach ($data as $k => $v) {
                    $data[$k]['pdr_payment_state'] = $rechargepaystate[$v['pdr_payment_state']];
                }
                $this->createExcel($data);
            }
        } else { //下载
            $limit1 = (input('param.page') - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $predeposit_model->getPdRechargeList($condition, $limit2, '*', 'pdr_id desc');
            $rechargepaystate = array(0 => '未支付', 1 => '已支付');
            foreach ($data as $k => $v) {
                $data[$k]['pdr_payment_state'] = $rechargepaystate[$v['pdr_payment_state']];
            }
            $this->createExcel($data);
        }
    }

    /**
     * 生成导出预存款充值excel
     *
     * @param array $data
     */
    private function createExcel($data = array()) {
        Lang::load(base_path() .'admin/lang/'.config('lang.default_lang').'/export.lang.php');
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_yc_no'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_yc_member'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_yc_ctime'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_yc_ptime'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_yc_pay'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_yc_money'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_yc_paystate'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_yc_memberid'));
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $v['pdr_sn']);
            $tmp[] = array('data' => $v['pdr_member_name']);
            $tmp[] = array('data' => date('Y-m-d H:i:s', $v['pdr_addtime']));
            if (intval($v['pdr_paymenttime'])) {
                if (date('His', $v['pdr_paymenttime']) == 0) {
                    $tmp[] = array('data' => date('Y-m-d', $v['pdr_paymenttime']));
                } else {
                    $tmp[] = array('data' => date('Y-m-d H:i:s', $v['pdr_paymenttime']));
                }
            } else {
                $tmp[] = array('data' => '');
            }
            $tmp[] = array('data' => $v['pdr_payment_code']);
            $tmp[] = array('format' => 'Number', 'data' => ds_price_format($v['pdr_amount']));
            $tmp[] = array('data' => $v['pdr_payment_state']);
            $tmp[] = array('data' => $v['pdr_member_id']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_yc_yckcz'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_yc_yckcz'), CHARSET) . input('param.page') . '-' . date('Y-m-d-H', TIMESTAMP));
    }


    /**
     * 导出预存款提现记录
     *
     */
    public function export_cash_step1() {
        $condition = array();
        $stime = input('get.stime');
        $etime = input('get.etime');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $stime);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $etime);
        $start_unixtime = $if_start_date ? strtotime($stime) : null;
        $end_unixtime = $if_end_date ? strtotime($etime) : null;
        if ($start_unixtime) {
            $condition[] = array('pdc_addtime','>=', $start_unixtime);
        }
        if ($end_unixtime) {
            $end_unixtime=$end_unixtime+86399;
            $condition[] = array('pdc_addtime','<=', $end_unixtime);
        }
        $mname = input('get.mname');
        if (!empty($mname)) {
            $condition[] = array('pdc_member_name','like', "%" . $mname . "%");
        }
        $pdc_bank_user = input('get.pdc_bank_user');
        if (!empty($pdc_bank_user)) {
            $condition[] = array('pdc_bank_user','like', "%" . $pdc_bank_user . "%");
        }
        $paystate_search = input('get.paystate_search');
        if ($paystate_search != '') {
            $condition[] = array('pdc_payment_state','=',$paystate_search);
        }

        $predeposit_model = Model('predeposit');

        if (!is_numeric(input('param.page'))) {
            $count = $predeposit_model->getPdCashCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) { //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                View::assign('export_list', $array);
                return View::fetch('/public/excel');
            } else { //如果数量小，直接下载
                $data = $predeposit_model->getPdCashList($condition, '', '*', 'pdc_id desc', self::EXPORT_SIZE);
                $cashpaystate = array(0 => '未支付', 1 => '已支付');
                foreach ($data as $k => $v) {
                    $data[$k]['pdc_payment_state'] = $cashpaystate[$v['pdc_payment_state']];
                }
                $this->createCashExcel($data);
            }
        } else { //下载
            $limit1 = (input('param.page') - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $predeposit_model->getPdCashList($condition, $limit2, '*', 'pdc_id desc');
            $cashpaystate = array(0 => '未支付', 1 => '已支付');
            foreach ($data as $k => $v) {
                $data[$k]['pdc_payment_state'] = $cashpaystate[$v['pdc_payment_state']];
            }
            $this->createCashExcel($data);
        }
    }

    /**
     * 生成导出预存款提现excel
     *
     * @param array $data
     */
    private function createCashExcel($data = array()) {
        Lang::load(base_path() .'admin/lang/'.config('lang.default_lang').'/export.lang.php');
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tx_no'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tx_member'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tx_money'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tx_ctime'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tx_state'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tx_memberid'));
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $v['pdc_sn']);
            $tmp[] = array('data' => $v['pdc_member_name']);
            $tmp[] = array('format' => 'Number', 'data' => ds_price_format($v['pdc_amount']));
            $tmp[] = array('data' => date('Y-m-d H:i:s', $v['pdc_addtime']));
            $tmp[] = array('data' => $v['pdc_payment_state']);
            $tmp[] = array('data' => $v['pdc_member_id']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_tx_title'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_tx_title'), CHARSET) . input('param.page') . '-' . date('Y-m-d-H', TIMESTAMP));
    }

    /**
     * 预存款明细信息导出
     */
    public function export_mx_step1() {
        $condition = array();
        $stime = input('get.stime');
        $etime = input('get.etime');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $stime);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $etime);
        $start_unixtime = $if_start_date ? strtotime($stime) : null;
        $end_unixtime = $if_end_date ? strtotime($etime) : null;
        if ($start_unixtime) {
            $condition[] = array('lg_addtime','>=',$start_unixtime);
        }
        if ($end_unixtime) {
            $end_unixtime=$end_unixtime+86399;
            $condition[] = array('lg_addtime','<=',$end_unixtime);
        }
        $mname = input('get.mname');
        if (!empty($mname)) {
            $condition[] = array('lg_member_name','=',$mname);
        }
        $aname = input('get.aname');
        if (!empty($aname)) {
            $condition[] = array('lg_admin_name','=',$aname);
        }


        $predeposit_model = Model('predeposit');
        if (!is_numeric(input('param.page'))) {
            $count = $predeposit_model->getPdLogCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) { //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                View::assign('export_list', $array);
                return View::fetch('/public/excel');
            } else { //如果数量小，直接下载
                $data = $predeposit_model->getPdLogList($condition, '', '*', 'lg_id desc', self::EXPORT_SIZE);
                $this->createmxExcel($data);
            }
        } else { //下载
            $limit1 = (input('param.page') - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $predeposit_model->getPdLogList($condition, $limit2, '*', 'lg_id desc');
            $this->createmxExcel($data);
        }
    }

    /**
     * 导出预存款明细excel
     *
     * @param array $data
     */
    private function createmxExcel($data = array()) {
        Lang::load(base_path() .'admin/lang/'.config('lang.default_lang').'/export.lang.php');
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_mx_member'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_mx_ctime'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_mx_av_money'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_mx_freeze_money'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_mx_system'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_mx_mshu'));
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $v['lg_member_name']);
            $tmp[] = array('data' => date('Y-m-d H:i:s', $v['lg_addtime']));
            if (floatval($v['lg_av_amount']) == 0) {
                $tmp[] = array('data' => '');
            } else {
                $tmp[] = array('format' => 'Number', 'data' => ds_price_format($v['lg_av_amount']));
            }
            if (floatval($v['lg_freeze_amount']) == 0) {
                $tmp[] = array('data' => '');
            } else {
                $tmp[] = array('format' => 'Number', 'data' => ds_price_format($v['lg_freeze_amount']));
            }
            $tmp[] = array('data' => $v['lg_admin_name']);
            $tmp[] = array('data' => $v['lg_desc']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_mx_rz'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_mx_rz'), CHARSET) . input('param.page') . '-' . date('Y-m-d-H', TIMESTAMP));
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'pdrecharge_list',
                'text' => '充值明细',
                'url' => url('Predeposit/pdrecharge_list')
            ),
            array(
                'name' => 'pdcash_set',
                'text' => lang('admin_predeposit_cashset'),
                'url' => url('Predeposit/pdcash_set')
            ),
            array(
                'name' => 'pdcash_list',
                'text' => '提现管理',
                'url' => url('Predeposit/pdcash_list')
            ),
            array(
                'name' => 'pdlog_list',
                'text' => '预存款明细',
                'url' => url('Predeposit/pdlog_list')
            ),
            array(
                'name' => 'pd_add',
                'text' => '预存款调节',
                'url' => "javascript:dsLayerOpen('".url('Predeposit/pd_add')."','预存款调节')"
            ),
        );
        return $menu_array;
    }
}

?>
