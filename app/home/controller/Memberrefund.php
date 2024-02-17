<?php

/*
 * 订单退款
 */

namespace app\home\controller;
use think\facade\View;
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
class Memberrefund extends BaseMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/memberrefund.lang.php');
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/memberorder.lang.php');
        //向模板页面输出退款退货状态
        $this->getRefundStateArray();
    }

    /**
     * 添加订单商品部分退款
     *
     */
    public function add_refund() {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $reason_list = $refundreturn_model->getReasonList($condition); //退款退货原因
        View::assign('reason_list', $reason_list);
        $order_id = intval(input('param.order_id'));
        $goods_id = intval(input('param.goods_id')); //订单商品表编号
        if ($order_id < 1 || $goods_id < 1) {//参数验证
            $this->error(lang('param_error'),url('Memberorder/index'));
        }
        $condition = array();
        $condition[] = array('buyer_id','=',session('member_id'));
        $condition[] = array('order_id','=',$order_id);
        
        $order = $refundreturn_model->getRightOrderList($condition, $goods_id);
        $order_amount = $order['order_amount']; //订单金额
        $order_refund_amount = $order['refund_amount']; //订单退款金额
        $goods_list = $order['goods_list'];
        $goods = $goods_list[0];
        $goods_pay_price = $goods['goods_pay_price']; //商品实际成交价
        if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
            $goods_pay_price = $order_amount - $order_refund_amount;
            $goods['goods_pay_price'] = $goods_pay_price;
        }
        
        View::assign('goods', $goods);
        View::assign('order', $order);
        View::assign('order_common', $order['extend_order_common']);
        View::assign('goods_list', $order['goods_list']);
        

        $goods_id = $goods['rec_id'];
        $condition = array();
        $condition[]=array('buyer_id','=',$order['buyer_id']);
        $condition[]=array('order_id','=',$order['order_id']);
        $condition[]=array('order_goods_id','=',$goods_id);
        $refund_list = $refundreturn_model->getRefundreturnList($condition);
        $refund = array();
        if (!empty($refund_list) && is_array($refund_list)) {
            $refund = $refund_list[0];
        }
        $refund_state = $refundreturn_model->getRefundState($order); //根据订单状态判断是否可以退款退货
        if ((isset($refund['refund_id']) && $refund['refund_id'] > 0) || $refund_state != 1) {//检查订单状态,防止页面刷新不及时造成数据错误
            $this->error(lang('param_error'),url('Memberorder/index'));
        }
        if (request()->isPost() && $goods_id > 0) {
            $refund_array = array();
            $refund_amount = floatval(input('post.refund_amount')); //退款金额
            if (($refund_amount < 0) || ($refund_amount > $goods_pay_price)) {
                $refund_amount = $goods_pay_price;
            }
            $goods_num = intval(input('post.goods_num')); //退货数量
            if (($goods_num < 0) || ($goods_num > $goods['goods_num'])) {
                $goods_num = 1;
            }
            $refund_array['reason_info'] = '';
            $reason_id = intval(input('post.reason_id')); //退货退款原因
            $refund_array['reason_id'] = $reason_id;
            $reason_array = array();
            $reason_array['reason_info'] = lang('ds_other');
            $reason_list[0] = $reason_array;
            if (!empty($reason_list[$reason_id])) {
                $reason_array = $reason_list[$reason_id];
                $refund_array['reason_info'] = $reason_array['reason_info'];
            }

            $pic_array = array();
            $pic_array['buyer'] = $this->upload_pic(); //上传凭证
            $info = serialize($pic_array);
            $refund_array['pic_info'] = $info;
            $trade_model = model('trade');
            $order_shipped = $trade_model->getOrderState('order_shipped'); //订单状态30:已发货
            if ($order['order_state'] == $order_shipped) {
                $refund_array['order_lock'] = '2'; //锁定类型:1为不用锁定,2为需要锁定
            }else{
                $refund_array['order_lock'] = '1';
            }
            $refund_array['refund_type'] = input('post.refund_type'); //类型:1为退款,2为退货
            $show_url = 'Memberreturn/index';
            $refund_array['return_type'] = '2'; //退货类型:1为不用退货,2为需要退货
            if ($refund_array['refund_type'] != '2') {
                $refund_array['refund_type'] = '1';
                $refund_array['return_type'] = '1';
                $show_url = 'Memberrefund/index';
            }
            $refund_array['refund_amount'] = ds_price_format($refund_amount);
            $refund_array['goods_num'] = $goods_num;
            $refund_array['buyer_message'] = input('post.buyer_message');
            $refund_array['add_time'] = TIMESTAMP;
            $state = $refundreturn_model->addRefundreturn($refund_array, $order, $goods);
            if ($state) {
                if ($order['order_state'] == $order_shipped) {
                    $refundreturn_model->editOrderLock($order_id);
                }
                $this->success(lang('ds_common_save_succ'),$show_url);
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        } else {
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_refund');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('my_address_edit');
            return View::fetch($this->template_dir.'add_refund');
        }
    }

    /**
     * 添加全部退款即取消订单
     *
     */
    public function add_refund_all() {
        $trade_model = model('trade');
        $refundreturn_model = model('refundreturn');
        $order_id = intval(input('param.order_id'));
        $condition = array();
        $condition[] = array('buyer_id','=',session('member_id'));
        $condition[] = array('order_id','=',$order_id);
        $order = $refundreturn_model->getRightOrderList($condition);
        View::assign('order', $order);
        View::assign('order_common', $order['extend_order_common']);
        View::assign('goods_list', $order['goods_list']);


        $order_amount = $order['order_amount']; //订单金额
        $condition = array();
        $condition[]=array('buyer_id','=',$order['buyer_id']);
        $condition[]=array('order_id','=',$order['order_id']);
        $condition[]=array('goods_id','=','0');
        $refund_list = $refundreturn_model->getRefundreturnList($condition);
        $refund = array();
        if (!empty($refund_list) && is_array($refund_list)) {
            $refund = $refund_list[0];
        }
        $order_paid = $trade_model->getOrderState('order_paid'); //订单状态20:已付款
        $payment_code = $order['payment_code']; //支付方式
        if (isset($refund['refund_id']) && $refund['refund_id'] > 0){
            $this->error(lang('refund_error_tip'), 'home/memberrefund/index');
        }
        if ($order['order_state'] != $order_paid || $payment_code == 'offline') {//检查订单状态,防止页面刷新不及时造成数据错误
            $this->error(lang('param_error'), 'home/memberrefund/index');
        }
        if (!request()->isPost()) {
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_refund');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('my_address_edit');
            return View::fetch($this->template_dir.'add_refund_all');
        } else {
            $refund_array = array();
            $refund_array['refund_type'] = '1'; //类型:1为退款,2为退货
            $refund_array['order_lock'] = '2'; //锁定类型:1为不用锁定,2为需要锁定
            $refund_array['goods_id'] = '0';
            $refund_array['order_goods_id'] = '0';
            $refund_array['reason_id'] = '0';
            $refund_array['reason_info'] = lang('refund_notice4');
            $refund_array['goods_name'] = lang('all_orders_refunded');
            $refund_array['refund_amount'] = ds_price_format($order_amount);
            $refund_array['buyer_message'] = input('post.buyer_message');
            $refund_array['add_time'] = TIMESTAMP;
            $pic_array = array();
            $pic_array['buyer'] = $this->upload_pic(); //上传凭证
            $info = serialize($pic_array);
            $refund_array['pic_info'] = $info;
            $state = $refundreturn_model->addRefundreturn($refund_array, $order);
            if ($state) {
                $refundreturn_model->editOrderLock($order_id);
                $this->success(lang('ds_common_save_succ'), 'home/memberrefund/index');
            } else {
                $this->error(lang('ds_common_save_fail'));
            }

        }
    }

    /**
     * 退款记录列表页
     *
     */
    public function index() {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $condition[]=array('buyer_id','=',session('member_id'));
        $condition[]=array('refund_type','=','1'); //类型:1为退款,2为退货
        $keyword_type = array('order_sn', 'refund_sn', 'goods_name');
        $key = input('get.key');
        $type = input('get.type');
        if (trim($key) != '' && in_array($type, $keyword_type)) {
            $condition[] = array($type,'like', '%' . $key . '%');
        }
        $add_time_from = input('get.add_time_from');
        $add_time_to = input('get.add_time_to');
        if (trim($add_time_from) != '') {
            $add_time_from = strtotime(trim($add_time_from));
            if ($add_time_from !== false) {
                $condition[] = array('add_time','>=', $add_time_from);
            }
        }
        if (trim($add_time_to) != '') {
            $add_time_to = strtotime(trim($add_time_to));
            if ($add_time_to !== false) {
                $add_time_to=$add_time_to+86399;
                $condition[] = array('add_time','<=', $add_time_to);
            }
        }
        
        $refund_list = $refundreturn_model->getRefundList($condition,10);
        View::assign('refund_list', $refund_list);
        View::assign('show_page', $refundreturn_model->page_info->render());
        
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_refund');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('buyer_refund');
        return View::fetch($this->template_dir.'index');
    }

    /**
     * 退款记录查看
     *
     */
    public function view() {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $condition[] = array('buyer_id','=',session('member_id'));
        $condition[] = array('refund_id','=',intval(input('param.refund_id')));
        $condition[] = array('refund_type','=','1'); //类型:1为退款,2为退货
        $refund = $refundreturn_model->getRefundreturnInfo($condition);
        View::assign('refund', $refund);
        $info['buyer'] = array();
        if (!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        $pic_list = empty($info['buyer']) ? '' : $info['buyer'];
        View::assign('pic_list', $pic_list);
        $condition = array();
        $condition[] = array('order_id','=',$refund['order_id']);
        $order = $refundreturn_model->getRightOrderList($condition, $refund['order_goods_id']);
        View::assign('order', $order);
        View::assign('order_common', $order['extend_order_common']);
        View::assign('goods_list', $order['goods_list']);
        
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_refund');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('my_address_edit');
        return View::fetch($this->template_dir. 'view');
    }

    /**
     * 上传凭证
     *
     */
    private function upload_pic() {
        $refund_pic = array();
        $refund_pic[1] = 'refund_pic1';
        $refund_pic[2] = 'refund_pic2';
        $refund_pic[3] = 'refund_pic3';
        $pic_array = array();
        $count = 1;
        foreach ($refund_pic as $pic) {
            if (!empty($_FILES[$pic]['name'])) {
            $res = ds_upload_pic(ATTACH_PATH . DIRECTORY_SEPARATOR . 'refund', $pic);
            if ($res['code']) {
                $pic_array[$count] = $res['data']['file_name'];
            } else {
                $pic_array[$count] = '';
            }
            $count++;
        }
        }
        return $pic_array;
    }

    function getRefundStateArray($type = 'all') {
        $state_array = array(
            '1' => lang('refund_state_confirm'),
            '2' => lang('ds_agree'),
            '3' => lang('ds_disagree')
        ); //卖家处理状态:1为待审核,2为同意,3为不同意
        View::assign('state_array', $state_array);

        $admin_array = array(
            '1' => lang('in_processing'),
            '2' => lang('to_be_processed'),
            '3' => lang('has_been_completed')
        ); //确认状态:1为买家或卖家处理中,2为待平台管理员处理,3为退款退货已完成
        View::assign('admin_array', $admin_array);

        $state_data = array(
            'admin' => $admin_array
        );
        if ($type == 'all') {
            return $state_data; //返回所有
        }
        return $state_data['admin'];
    }
    /**
     *    栏目菜单
     */
    function getMemberItemList() {
        $item_list = array(
            array(
                'name' => 'buyer_refund',
                'text' => lang('ds_member_path_buyer_refund'),
                'url' => url('Memberrefund/index'),
            ),
            array(
                'name' => 'buyer_return',
                'text' => lang('ds_member_path_buyer_return'),
                'url' => url('Memberreturn/index'),
            ),
            array(
                'name' => 'buyer_vr_refund',
                'text' => lang('refund_virtual_currency_code'),
                'url' => url('Membervrrefund/index'),
            ),
        );
        return $item_list;
    }
}
