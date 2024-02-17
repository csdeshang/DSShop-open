<?php

/*
 * 买家退货
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
class Memberreturn extends BaseMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/memberreturn.lang.php');
        //向模板页面输出退款退货状态
        $this->getRefundStateArray();
    }

    /**
     * 退货记录列表页
     *
     */
    public function index() {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $condition[]=array('buyer_id','=',session('member_id'));
        $condition[]=array('refund_type','=','2'); //类型:1为退款,2为退货

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


        $return_list = $refundreturn_model->getReturnList($condition, 10);
        View::assign('return_list', $return_list);
        View::assign('show_page', $refundreturn_model->page_info->render());
        

        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('membervrrefund');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('buyer_return');
        return View::fetch($this->template_dir . 'index');
    }

    /**
     * 发货
     *
     */
    public function ship() {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $condition[] = array('buyer_id','=',session('member_id'));
        $condition[] = array('refund_id','=',intval(input('param.return_id')));
        $condition[] = array('refund_type','=','2');//类型:1为退款,2为退货
        $return = $refundreturn_model->getRefundreturnInfo($condition);
        if(empty($return)){
            $this->error(lang('param_error'));
        }
        View::assign('return', $return);
        $express_list = rkcache('express', true);
        View::assign('express_list', $express_list);
        if ($return['goods_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
            ds_json_encode(10001,lang('param_error'));
        }
        if (request()->isPost()) {
            $refund_array = array();
            $refund_array['ship_time'] = TIMESTAMP;
            $refund_array['delay_time'] = TIMESTAMP;
            $refund_array['express_id'] = input('post.express_id');
            $refund_array['invoice_no'] = input('post.invoice_no');
            $refund_array['goods_state'] = '2';
            $state = $refundreturn_model->editRefundreturn($condition, $refund_array);
            if ($state) {
                ds_json_encode(10000,lang('ds_common_save_succ'));
            } else {
                ds_json_encode(10001,lang('ds_common_save_fail'));
            }
        }
        $refund = Db::name('refundreturn')->where($condition)->find();
        $info['buyer'] = array();
        if (!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        $pic_list = empty($info['buyer'])?'':$info['buyer'];
        View::assign('pic_list', $pic_list);

        $condition = array();
        $condition[] = array('order_id','=',$return['order_id']);
        $order = $refundreturn_model->getRightOrderList($condition, $return['order_goods_id']);
        View::assign('order', $order);
        View::assign('order_common', $order['extend_order_common']);
        View::assign('goods_list', $order['goods_list']);


        $trade_model = model('trade');
        $return_delay = $trade_model->getMaxDay('return_delay'); //发货默认5天后才能选择没收到
        View::assign('return_delay', $return_delay);
        View::assign('return_confirm', $trade_model->getMaxDay('return_confirm')); //卖家不处理收货时按同意并弃货处理
        View::assign('ship', 1);
        $this->setMemberCurMenu('member_refund');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('my_address_edit');
        return View::fetch($this->template_dir . 'view');
    }

    /**
     * 延迟时间
     *
     */
    public function delay() {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $condition[] = array('buyer_id','=',session('member_id'));
        $condition[] = array('refund_id','=',intval(input('param.return_id')));
        $condition[] = array('refund_type','=','2'); //类型:1为退款,2为退货
        $return = $refundreturn_model->getRefundreturnInfo($condition);
        if(empty($return)){
            $this->error(lang('param_error'));
        }
        View::assign('return', $return);
        if (request()->isPost()) {
            if ($return['goods_state'] != '3') {//检查状态,防止页面刷新不及时造成数据错误
                ds_json_encode(10001,lang('param_error'));
            }
            $refund_array = array();
            $refund_array['delay_time'] = TIMESTAMP;
            $refund_array['goods_state'] = '2';
            $state = $refundreturn_model->editRefundreturn($condition, $refund_array);
            if ($state) {
                ds_json_encode(10000,lang('ds_common_save_succ'));
            } else {
                ds_json_encode(10001,lang('ds_common_save_fail'));
            }
        } else {
            $trade_model = model('trade');
            $return_delay = $trade_model->getMaxDay('return_delay'); //发货默认5天后才能选择没收到
            View::assign('return_delay', $return_delay);
            View::assign('return_confirm', $trade_model->getMaxDay('return_confirm')); //卖家不处理收货时按弃货处理
            return View::fetch($this->template_dir . 'delay');
        }
    }

    /**
     * 退货记录查看页
     *
     */
    public function view() {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $condition[] = array('buyer_id','=',session('member_id'));
        $condition[] = array('refund_id','=',intval(input('param.return_id')));
        $condition[] = array('refund_type','=','2');//类型:1为退款,2为退货
        $return = $refundreturn_model->getRefundreturnInfo($condition);
        if(empty($return)){
            $this->error(lang('param_error'));
        }
        View::assign('return', $return);
        $express_list = rkcache('express', true);
        if ($return['express_id'] > 0 && !empty($return['invoice_no'])) {
            View::assign('return_e_name', isset($express_list[$return['express_id']])?$express_list[$return['express_id']]['express_name']:'');
        }
        $refund = Db::name('refundreturn')->where($condition)->find();
        $info['buyer'] = array();
        if (!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        $pic_list = empty($info['buyer'])?'':$info['buyer'];
        View::assign('pic_list', $pic_list);
        $condition = array();
        $condition[] = array('order_id','=',$return['order_id']);
        $order = $refundreturn_model->getRightOrderList($condition, $return['order_goods_id']);
        View::assign('order', $order);
        View::assign('ship', 0);
        View::assign('order_common', $order['extend_order_common']);
        View::assign('goods_list', $order['goods_list']);


        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_refund');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('my_address_edit');
        return View::fetch($this->template_dir . 'view');
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
        return $state_data[$type];
    }

}
