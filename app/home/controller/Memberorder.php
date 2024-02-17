<?php

namespace app\home\controller;
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
class Memberorder extends BaseMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/memberorder.lang.php');
    }

    public function index() {
        $order_model = model('order');

        //搜索
        $condition = array();
        $condition[]=array('buyer_id','=',session('member_id'));

        $order_sn = input('param.order_sn');
        if ($order_sn != '') {
            $condition[]=array('order_sn','like','%'.$order_sn.'%');
        }
        $query_start_date = input('param.query_start_date');
        $query_end_date = input('param.query_end_date');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        if ($start_unixtime) {
            $condition[] = array('add_time','>=', $start_unixtime);
        }
        if ($end_unixtime) {
            $end_unixtime=$end_unixtime+86399;
            $condition[] = array('add_time','<=', $end_unixtime);
        }
        $state_type = input('param.state_type');
        if ($state_type != '') {
            $condition[] = array('order_state','=',str_replace(
                array('state_new', 'state_pay', 'state_send', 'state_success', 'state_noeval', 'state_cancel'), array(ORDER_STATE_NEW, ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS, ORDER_STATE_SUCCESS, ORDER_STATE_CANCEL),$state_type));
        }
        if ($state_type == 'state_noeval') {
            $condition[]=array('evaluation_state','=',0);
            $condition[] = array('refund_state','=',0);
            $condition[]=array('order_state','=',ORDER_STATE_SUCCESS);
        }
        
        //回收站
        $recycle = input('param.recycle');
        if ($recycle) {
            $condition[]=array('delete_state','=',1);
        } else {
            $condition[]=array('delete_state','=',0);
        }
        
        
        $order_list = $order_model->getOrderList($condition, 5, '*', 'order_id desc','', array('order_common','order_goods','ppintuanorder'));
        View::assign('show_page', $order_model->page_info->render());

        $refundreturn_model = model('refundreturn');
        $order_list = $refundreturn_model->getGoodsRefundList($order_list);

        //订单列表以支付单pay_sn分组显示
        $order_group_list = array();
        $order_pay_sn_array = array();
        foreach ($order_list as $order_id => $order) {
            //显示取消订单
            $order['if_cancel'] = $order_model->getOrderOperateState('buyer_cancel', $order);
            //显示退款取消订单
            $order['if_refund_cancel'] = $order_model->getOrderOperateState('refund_cancel', $order);
            //显示收货
            $order['if_receive'] = $order_model->getOrderOperateState('receive', $order);
            //显示锁定中
            $order['if_lock'] = $order_model->getOrderOperateState('lock', $order);
            //显示物流跟踪
            $order['if_deliver'] = $order_model->getOrderOperateState('deliver', $order);
            //显示评价
            $order['if_evaluation'] = $order_model->getOrderOperateState('evaluation', $order);
            //显示删除订单(放入回收站)
            $order['if_delete'] = $order_model->getOrderOperateState('delete', $order);
            //显示永久删除
            $order['if_drop'] = $order_model->getOrderOperateState('drop', $order);
            //显示还原订单
            $order['if_restore'] = $order_model->getOrderOperateState('restore', $order);

            foreach ($order['extend_order_goods'] as $value) {
                $value['goods_type_cn'] = get_order_goodstype($value['goods_type']);
                $value['goods_url'] = url('Goods/index', ['goods_id' => $value['goods_id']]);
                if ($value['goods_type'] == 5) {
                    $order['zengpin_list'][] = $value;
                } else {
                    $order['goods_list'][] = $value;
                }
            }

            if (empty($order['zengpin_list'])) {
                $order['goods_count'] = count($order['goods_list']);
            } else {
                $order['goods_count'] = count($order['goods_list']) + 1;
            }
            $order_group_list[$order['pay_sn']]['order_list'][] = $order;

            //如果有在线支付且未付款的订单则显示合并付款链接
            if ($order['order_state'] == ORDER_STATE_NEW) {
                if (!isset($order_group_list[$order['pay_sn']]['pay_amount'])) {
                    $order_group_list[$order['pay_sn']]['pay_amount'] = 0;
                }
                $order_group_list[$order['pay_sn']]['pay_amount'] += $order['order_amount'] - $order['pd_amount'] - $order['rcb_amount'];
            }
            $order_group_list[$order['pay_sn']]['add_time'] = $order['add_time'];

            //记录一下pay_sn，后面需要查询支付单表
            $order_pay_sn_array[] = $order['pay_sn'];
        }

        //取得这些订单下的支付单列表
        $condition = array();
        $condition[] = array('pay_sn','in',array_unique($order_pay_sn_array));
        $order_pay_list = $order_model->getOrderpayList($condition,'*','','pay_sn');
        foreach ($order_group_list as $pay_sn => $pay_info) {
            $order_group_list[$pay_sn]['pay_info'] = isset($order_pay_list[$pay_sn])?$order_pay_list[$pay_sn]:'';
        }
        View::assign('order_group_list', $order_group_list);
        View::assign('order_pay_list', $order_pay_list);

        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_order');
        /* 设置买家当前栏目 */
        if ($recycle) {
            $this->setMemberCurItem('member_order_recycle');
        } else {
            $this->setMemberCurItem('member_order');
        }

        return View::fetch($this->template_dir.'index');
    }

    /**
     * 物流跟踪
     */
    public function search_deliver() {
        
        $order_id = intval(input('param.order_id'));
        if ($order_id <= 0) {
            $this->error(lang('param_error'), '');
        }

        $order_model = model('order');
        $condition[] = array('order_id','=',$order_id);
        $condition[] = array('buyer_id','=',session('member_id'));
        $order_info = $order_model->getOrderInfo($condition, array('order_common', 'order_goods'));
        if (empty($order_info) || !in_array($order_info['order_state'], array(ORDER_STATE_SEND, ORDER_STATE_SUCCESS))) {
            $this->error(lang('no_information_found'));
        }
        View::assign('order_info', $order_info);

        //卖家发货信息
        $daddress_info = model('daddress')->getAddressInfo(array('daddress_id' => $order_info['extend_order_common']['daddress_id']));
        View::assign('daddress_info', $daddress_info);

        //取得配送公司代码
        $express = rkcache('express', true);
        View::assign('express_code', isset($express[$order_info['extend_order_common']['shipping_express_id']])?$express[$order_info['extend_order_common']['shipping_express_id']]['express_code']:'');
        View::assign('express_name', isset($express[$order_info['extend_order_common']['shipping_express_id']])?$express[$order_info['extend_order_common']['shipping_express_id']]['express_name']:'');
        View::assign('express_url', isset($express[$order_info['extend_order_common']['shipping_express_id']])?$express[$order_info['extend_order_common']['shipping_express_id']]['express_url']:'');
        View::assign('shipping_code', $order_info['shipping_code']);

        
        View::assign('left_show', 'order_view');
        
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_order');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('search_deliver');
        return View::fetch($this->template_dir.'search_deliver');
    }

    /**
     * 从第三方取快递信息
     *
     */
    public function get_express() {
        $result = model('express')->queryExpress(input('param.express_code'),input('param.shipping_code'),input('param.phone'));
        if ($result['Success'] != true)
            exit(json_encode(false));
        $content['Traces'] = array_reverse($result['Traces']);
        $output = array();
        if (is_array($content['Traces'])) {
            foreach ($content['Traces'] as $k => $v) {
                if ($v['AcceptTime'] == '')
                    continue;
                $output[] = $v['AcceptTime'] . '&nbsp;&nbsp;' . $v['AcceptStation'];
            }
        }
        if (empty($output))
            exit(json_encode(false));

        echo json_encode($output);
    }

    /**
     * 订单详细
     *
     */
    public function show_order() {
        $order_id = intval(input('param.order_id'));
        if ($order_id <= 0) {
            $this->error(lang('member_order_none_exist'));
        }
        $order_model = model('order');
        $condition = array();
        $condition[] = array('order_id','=',$order_id);
        $condition[] = array('buyer_id','=',session('member_id'));
        $order_info = $order_model->getOrderInfo($condition, array('order_goods', 'order_common'));
        if (empty($order_info) || $order_info['delete_state'] == ORDER_DEL_STATE_DROP) {
            $this->error(lang('member_order_none_exist'));
        }

        $refundreturn_model = model('refundreturn');
        $order_list = array();
        $order_list[$order_id] = $order_info;
        $order_list = $refundreturn_model->getGoodsRefundList($order_list, 1); //订单商品的退款退货显示
        $order_info = $order_list[$order_id];
        $refund_all = isset($order_info['refund_list'][0]) ? $order_info['refund_list'][0] : '';
        if (!empty($refund_all)) {//订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
            View::assign('refund_all', $refund_all);
        }

        //显示锁定中
        $order_info['if_lock'] = $order_model->getOrderOperateState('lock', $order_info);

        //显示取消订单
        $order_info['if_cancel'] = $order_model->getOrderOperateState('buyer_cancel', $order_info);

        //显示退款取消订单
        $order_info['if_refund_cancel'] = $order_model->getOrderOperateState('refund_cancel', $order_info);

        //显示收货
        $order_info['if_receive'] = $order_model->getOrderOperateState('receive', $order_info);

        //显示物流跟踪
        $order_info['if_deliver'] = $order_model->getOrderOperateState('deliver', $order_info);

        //显示评价
        $order_info['if_evaluation'] = $order_model->getOrderOperateState('evaluation', $order_info);


        //显示系统自动取消订单日期
        if ($order_info['order_state'] == ORDER_STATE_NEW) {
            $order_info['order_cancel_day'] = $order_info['add_time'] + config('ds_config.order_auto_cancel_day') * 24 * 3600;
        }

        //显示快递信息
        if ($order_info['shipping_code'] != '') {
            $express = rkcache('express', true);
            if(isset($express[$order_info['extend_order_common']['shipping_express_id']])){
                $order_info['express_info']['express_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['express_code'];
                $order_info['express_info']['express_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['express_name'];
                $order_info['express_info']['express_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['express_url'];
            }else{
                $order_info['express_info']['express_code'] = '';
                $order_info['express_info']['express_name'] = '';
                $order_info['express_info']['express_url'] = '';
            }
        }

        //显示系统自动收获时间
        if ($order_info['order_state'] == ORDER_STATE_SEND) {
            $order_info['order_confirm_day'] = $order_info['delay_time'] + config('ds_config.order_auto_receive_day') * 24 * 3600;
        }

        //如果订单已取消，取得取消原因、时间，操作人
        if ($order_info['order_state'] == ORDER_STATE_CANCEL) {
            $order_info['close_info'] = $order_model->getOrderlogInfo(array('order_id' => $order_info['order_id']), 'log_id desc');
        }

        foreach ($order_info['extend_order_goods'] as $value) {
            $value['image_240_url'] = goods_cthumb($value['goods_image'], 240);
            $value['goods_type_cn'] = get_order_goodstype($value['goods_type']);
            $value['goods_url'] = url('Goods/index', ['goods_id' => $value['goods_id']]);
            if ($value['goods_type'] == 5) {
                $order_info['zengpin_list'][] = $value;
            } else {
                $order_info['goods_list'][] = $value;
            }
        }
        if (empty($order_info['zengpin_list'])) {
            $order_info['goods_count'] = count($order_info['goods_list']);
        } else {
            $order_info['goods_count'] = count($order_info['goods_list']) + 1;
        }
        View::assign('order_info', $order_info);
        //卖家发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = model('daddress')->getAddressInfo(array('daddress_id' => $order_info['extend_order_common']['daddress_id']));
            View::assign('daddress_info', $daddress_info);
        }

        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_order');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('my_album');

        return View::fetch($this->template_dir.'show_order');
    }

    /**
     * 买家订单状态操作
     *
     */
    public function change_state() {
        $state_type = input('param.state_type');
        $order_id = intval(input('param.order_id'));

        $order_model = model('order');

        $condition = array();
        $condition[] = array('order_id','=',$order_id);
        $condition[] = array('buyer_id','=',session('member_id'));
        $order_info = $order_model->getOrderInfo($condition);

        if ($state_type == 'order_cancel') {
            $result = $this->_order_cancel($order_info, input('post.'));
        } else if ($state_type == 'order_receive') {
            $result = $this->_order_receive($order_info, input('post.'));
        } else if (in_array($state_type, array('order_delete', 'order_drop', 'order_restore'))) {
            $result = $this->_order_recycle($order_info, input('get.'));
        } else {
            exit();
        }

        if (!$result['code']) {
            ds_json_encode(10001,$result['msg']);
        } else {
            ds_json_encode(10000,$result['msg']);
        }
    }

    /**
     * 取消订单
     */
    private function _order_cancel($order_info, $post) {
        if (!request()->isPost()) {
            View::assign('order_info', $order_info);
            echo View::fetch($this->template_dir.'cancel');
            exit();
        } else {
            $order_model = model('order');
            $logic_order = model('order','logic');
            $if_allow = $order_model->getOrderOperateState('buyer_cancel', $order_info);
            if (!$if_allow) {
                return ds_callback(false,  lang('have_right_operate'));
            }
            $msg = $post['state_info1'] != '' ? $post['state_info1'] : $post['state_info'];
            try{
                Db::startTrans();
                $logic_order->changeOrderStateCancel($order_info, 'buyer', session('member_name'), $msg);
            } catch (\Exception $e) {
                Db::rollback();
                return ds_callback(false, $e->getMessage());
            }
            Db::commit();    
            return ds_callback(true, lang('ds_common_op_succ'));
        }
    }

    /**
     * 收货
     */
    private function _order_receive($order_info, $post) {
        if (!request()->isPost()) {
            View::assign('order_info', $order_info);
            echo View::fetch($this->template_dir.'receive');
            exit();
        } else {
            $order_model = model('order');
            $logic_order = model('order','logic');
            $if_allow = $order_model->getOrderOperateState('receive', $order_info);
            if (!$if_allow) {
                return ds_callback(false,  lang('have_right_operate'));
            }
            
            return $logic_order->changeOrderStateReceive($order_info, 'buyer', session('member_name'));
        }
    }

    /**
     * 回收站
     */
    private function _order_recycle($order_info, $get) {
        $order_model = model('order');
        $logic_order = model('order','logic');
        $state_type = str_replace(array('order_delete', 'order_drop', 'order_restore'), array('delete', 'drop', 'restore'), input('param.state_type'));
        $if_allow = $order_model->getOrderOperateState($state_type, $order_info);
        if (!$if_allow) {
            return ds_callback(false, lang('have_right_operate'));
        }

        return $logic_order->changeOrderStateRecycle($order_info, 'buyer', $state_type);
    }

    /**
     *    栏目菜单
     */
    function getMemberItemList() {
        $item_list = array(
            array(
                'name' => 'member_order',
                'text' => lang('ds_member_path_order_list'),
                'url' => url('Memberorder/index'),
            ),
            array(
                'name' => 'member_order_recycle',
                'text' => lang('recycle_bin'),
                'url' => url('Memberorder/index',['recycle'=>'1']),
            ),
        );
        
        return $item_list;
        
    }
    
    

}
