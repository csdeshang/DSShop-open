<?php

namespace app\api\controller;

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
 * 用户提现账户控制器
 */
class Memberbank extends MobileMember {

    public function initialize() {
        parent::initialize();
    }

    /**
     * @api {POST} api/memberbank/bank_list 获取用户提现账户
     * @apiVersion 3.0.6
     * @apiGroup Memberbank
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {String} result.bank_list 提现账户
     * @apiSuccess {String} result.bank_list.memberbank_id ID
     * @apiSuccess {String} result.bank_list.memberbank_type 类型bank银行 alipay支付宝
     * @apiSuccess {String} result.bank_list.memberbank_truename 收款人姓名
     * @apiSuccess {String} result.bank_list.memberbank_name 收款银行
     * @apiSuccess {String} result.bank_list.memberbank_no 收款账户
     */
    public function bank_list() {
        $memberbank_model = model('memberbank');
        $bank_list = $memberbank_model->getMemberbankList(array('member_id' => $this->member_info['member_id']));
        if (!empty($this->member_info['member_h5_wxopenid'])) {
            if (empty($bank_list)) {
                $bank_list = array();
            }
            $bank_list[] = array('memberbank_id' => -1, 'memberbank_type' => 'weixin', 'memberbank_no' => $this->member_info['member_wxnickname']);
        }
        ds_json_encode(10000, '', array('bank_list' => $bank_list));
    }

    /**
     * @api {POST} api/memberbank/bank_info 获取提现账户
     * @apiVersion 3.0.6
     * @apiGroup Memberbank
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {String} memberbank_id 提现账户ID
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {String} result.bank_info 提现账户信息
     * @apiSuccess {String} result.bank_info.memberbank_id ID
     * @apiSuccess {String} result.bank_info.memberbank_type 类型bank银行 alipay支付宝
     * @apiSuccess {String} result.bank_info.memberbank_truename 收款人姓名
     * @apiSuccess {String} result.bank_info.memberbank_name 收款银行
     * @apiSuccess {String} result.bank_info.memberbank_no 收款账户
     */
    public function bank_info() {
        $memberbank_id = intval(input('param.memberbank_id'));
        if ($memberbank_id<=0) {
            ds_json_encode(10001, lang('param_error'));
        }

        $memberbank_model = model('memberbank');

        $condition = array();
        $condition[] = array('memberbank_id','=',$memberbank_id);
        $condition[] = array('member_id','=',$this->member_info['member_id']);
        
        $bank_info = $memberbank_model->getMemberbankInfo($condition);
        if (!empty($bank_info)) {
            ds_json_encode(10000, '', array('bank_info' => $bank_info));
        } else {
            ds_json_encode(10001, lang('memberbank_does_not_exist'));
        }
    }

    /**
     * @api {POST} api/memberbank/bank_del 删除提现账户
     * @apiVersion 3.0.6
     * @apiGroup Memberbank
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {String} memberbank_id 提现账户ID
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function bank_del() {
        $memberbank_id = intval(input('param.memberbank_id'));
        if ($memberbank_id<=0) {
            ds_json_encode(10001, lang('param_error'));
        }

        $memberbank_model = model('memberbank');

        $condition = array();
        $condition[] = array('memberbank_id','=',$memberbank_id);
        $condition[] = array('member_id','=',$this->member_info['member_id']);
        $result = $memberbank_model->delMemberbank($condition);
        if (!empty($result)) {
            ds_json_encode(10000, lang('ds_common_op_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
    }

    /**
     * @api {POST} api/memberbank/bank_add 新增提现账户
     * @apiVersion 3.0.6
     * @apiGroup Memberbank
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {String} memberbank_type 类型bank银行 alipay支付宝
     * @apiParam {String} memberbank_truename 收款人姓名
     * @apiParam {String} memberbank_name 收款银行
     * @apiParam {String} memberbank_no 收款账户
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function bank_add() {
        $memberbank_model = model('memberbank');

        $data = array(
            'member_id' => $this->member_info['member_id'],
            'memberbank_type' => input('post.memberbank_type'),
            'memberbank_truename' => input('post.memberbank_truename'),
            'memberbank_name' => input('post.memberbank_name'),
            'memberbank_no' => input('post.memberbank_no'),
        );
        $memberbank_validate = ds_validate('memberbank');
        if (!$memberbank_validate->scene('add')->check($data)) {
            ds_json_encode(10001, $memberbank_validate->getError());
        }

        $result = $memberbank_model->addMemberbank($data);
        if ($result) {
            ds_json_encode(10000, '', array('memberbank_id' => $result));
        } else {
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
    }

    /**
     * @api {POST} api/memberbank/bank_edit 编辑提现账户
     * @apiVersion 3.0.6
     * @apiGroup Memberbank
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {String} memberbank_id 提现账户ID
     * @apiParam {String} memberbank_type 类型bank银行 alipay支付宝
     * @apiParam {String} memberbank_truename 收款人姓名
     * @apiParam {String} memberbank_name 收款银行
     * @apiParam {String} memberbank_no 收款账户
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function bank_edit() {
        $memberbank_id = intval(input('param.memberbank_id'));
        if ($memberbank_id<=0) {
            ds_json_encode(10001, lang('param_error'));
        }
        
        $memberbank_model = model('memberbank');

        //验证提现账户是否为本人
        $memberbank_info = $memberbank_model->getOneMemberbank($memberbank_id);
        if ($memberbank_info['member_id'] != $this->member_info['member_id']) {
            ds_json_encode(10001, lang('param_error'));
        }

        $data = array(
            'memberbank_type' => input('post.memberbank_type'),
            'memberbank_truename' => input('post.memberbank_truename'),
            'memberbank_name' => input('post.memberbank_name'),
            'memberbank_no' => input('post.memberbank_no'),
        );
        $memberbank_validate = ds_validate('memberbank');
        if (!$memberbank_validate->scene('edit')->check($data)) {
            ds_json_encode(10001, $memberbank_validate->getError());
        }

        $result = $memberbank_model->editMemberbank($data, array('memberbank_id' => $memberbank_id, 'member_id' => $this->member_info['member_id']));
        if ($result) {
            ds_json_encode(10000, lang('ds_common_op_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
    }

}
