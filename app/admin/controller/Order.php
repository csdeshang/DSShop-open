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
class Order extends AdminControl
{

    const EXPORT_SIZE = 1000;

    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/order.lang.php');
    }

    public function index()
    {
        $order_model = model('order');
        $condition = array();

        $order_sn = input('param.order_sn');
        if ($order_sn) {
            $condition[] = array('order_sn','=',$order_sn);
        }
        $order_state = input('param.order_state');
        if (in_array($order_state, array('0', '10', '20', '30', '40'))) {
            $condition[] = array('order_state','=',$order_state);
        }
        $payment_code = input('param.payment_code');
        if ($payment_code) {
            $condition[] = array('payment_code','=',$payment_code);
        }
        $buyer_name = input('param.buyer_name');
        if ($buyer_name) {
            $condition[] = array('buyer_name','=',$buyer_name);
        }
        $query_start_time = input('param.query_start_time');
        $query_end_time = input('param.query_end_time');
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_time);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_time);
        $start_unixtime = $if_start_time ? strtotime($query_start_time) : null;
        $end_unixtime = $if_end_time ? strtotime($query_end_time) : null;
        if ($start_unixtime) {
            $condition[] = array('add_time','>=',$start_unixtime);
        }
        if ($end_unixtime) {
            $condition[] = array('add_time','<=',$end_unixtime);
        }
        $order_list = $order_model->getOrderList($condition, 10);
        View::assign('show_page', $order_model->page_info->render());

        foreach ($order_list as $order_id => $order_info) {
            //显示取消订单
            $order_list[$order_id]['if_cancel'] = $order_model->getOrderOperateState('system_cancel', $order_info);
            //显示调整运费
            $order_list[$order_id]['if_modify_price'] = $order_model->getOrderOperateState('modify_price', $order_info);
            //显示收到货款
            $order_list[$order_id]['if_system_receive_pay'] = $order_model->getOrderOperateState('system_receive_pay', $order_info);
            //显示调整价格
            $order_list[$order_id]['if_spay_price'] = $order_model->getOrderOperateState('spay_price', $order_info);
            //显示发货状态
            $order_list[$order_id]['if_send'] = $order_model->getOrderOperateState('send', $order_info);
        }
        //显示支付接口列表(搜索)
        $payment_list = model('payment')->getPaymentOpenList();
        View::assign('payment_list', $payment_list);
        View::assign('order_list', $order_list);

        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        $this->setAdminCurItem('add');
        return View::fetch('index');
    }

    /**
     * 查看订单
     */
    public function order_print()
    {
        $order_id = ds_delete_param(input('param.order_id'));
        if (empty($order_id)) {
            $this->error(lang('param_error'));
        }
        $order_model = model('order');
        $condition[] = array('order_id','in',$order_id);
        $order_list = $order_model->getOrderList($condition, '', '*', 'order_id desc', 0, array('order_common', 'order_goods'));
        if (empty($order_list)) {
            $this->error(lang('member_printorder_ordererror'));
        }


        //订单商品
        foreach($order_list as $key =>$order_info){
            $goods_all_num = 0;
            $goods_total_price = 0;
            if (isset($order_info['extend_order_goods']) && !empty($order_info['extend_order_goods'])) {
                foreach ($order_info['extend_order_goods'] as $k => $v) {
                    $v['goods_name'] = str_cut($v['goods_name'], 100);
                    $goods_all_num += $v['goods_num'];
                    $v['goods_all_price'] = ds_price_format($v['goods_num'] * $v['goods_price']);
                    $goods_total_price += $v['goods_all_price'];
                    $order_list[$key]['extend_order_goods'][$k]=$v;
                }
                //优惠金额
                $order_list[$key]['promotion_amount'] = $goods_total_price - $order_info['goods_amount'];
                $order_list[$key]['goods_all_num'] = $goods_all_num;
                $order_list[$key]['goods_total_price'] = ds_price_format($goods_total_price);
                $order_list[$key]['total_page'] = ceil(count($order_info['extend_order_goods']) / 15);
            }
            
        }
        View::assign('order_list', $order_list);
        View::assign('seal_printexplain',config('ds_config.seal_printexplain'));
        View::assign('seal_img',config('ds_config.seal_img')?ds_get_pic(DIR_ADMIN,config('ds_config.seal_img')):'');
        $this->setAdminCurItem();
        return View::fetch('order_print');
    }

    /**
     * 平台订单状态操作
     *
     */
    public function change_state()
    {
        $order_id = intval(input('param.order_id'));
        if ($order_id <= 0) {
            $this->error(lang('miss_order_number'));
        }
        $order_model = model('order');

        //获取订单详细
        $condition = array();
        $condition[] = array('order_id','=',$order_id);
        $order_info = $order_model->getOrderInfo($condition);

        $state_type = input('param.type_state');
        if ($state_type == 'cancel') {
            $result = $this->_order_cancel($order_info);
            if ($result['code']){
                ds_json_encode(10000, $result['msg']);
            }
        } elseif ($state_type == 'spay_price') {
            //修改商品价格
            $result = $this->_order_spay_price($order_info, input('post.'));
        } elseif ($state_type == 'modify_price') {
            //修改商品运费
            $result = $this->_order_ship_price($order_info, input('post.'));
        } elseif ($state_type == 'receive_pay') {
            $result = $this->_order_receive_pay($order_info, input('post.'));
        }
        if (!$result['code']) {
            $this->error($result['msg']);
        } else {
            dsLayerOpenSuccess($result['msg']);
        }
    }


    /**
     * 系统取消订单
     */
    private function _order_cancel($order_info)
    {
        $order_model = model('order');
        $logic_order = model('order', 'logic');
        $if_allow = $order_model->getOrderOperateState('system_cancel', $order_info);
        if (!$if_allow) {
            return ds_callback(false, '无权操作');
        }
        try{
            Db::startTrans();
            $logic_order->changeOrderStateCancel($order_info, 'system', $this->admin_info['admin_name']);
        } catch (\Exception $e) {
            Db::rollback();
            return ds_callback(false, $e->getMessage());
        }
        Db::commit();
        $this->log(lang('order_log_cancel') . ',' . lang('order_number') . ':' . $order_info['order_sn'], 1);
        return ds_callback(true, lang('ds_common_op_succ'));
    }

    /**
     * 修改发货地址
     */
    private function _edit_order_daddress($daddress_id, $order_id)
    {
        $order_model = model('order');
        $data = array();
        $data['daddress_id'] = intval($daddress_id);
        $condition = array();
        $condition[] = array('order_id','=',$order_id);
        return $order_model->editOrdercommon($data, $condition);
    }

    /**
     * 修改商品价格
     * @param unknown $order_info
     */
    private function _order_spay_price($order_info, $post)
    {
        $order_model = model('order');
        $logic_order = model('order', 'logic');
        if (!request()->isPost()) {
            View::assign('order_info', $order_info);
            View::assign('order_id', $order_info['order_id']);
            echo View::fetch('edit_spay_price');
            exit();
        } else {
            $if_allow = $order_model->getOrderOperateState('spay_price', $order_info);
            if (!$if_allow) {
                return ds_callback(false, '无权操作');
            }
            return $logic_order->changeOrderSpayPrice($order_info, 'admin', session('member_name'), $post['goods_amount']);
        }
    }

    /**
     * 修改运费
     * @param unknown $order_info
     */
    private function _order_ship_price($order_info, $post)
    {
        $order_model = model('order');
        $logic_order = model('order', 'logic');
        if (!request()->isPost()) {
            View::assign('order_info', $order_info);
            View::assign('order_id', $order_info['order_id']);
            echo View::fetch('edit_price');
            exit();
        } else {
            $if_allow = $order_model->getOrderOperateState('modify_price', $order_info);
            if (!$if_allow) {
                return ds_callback(false, '无权操作');
            }
            return $logic_order->changeOrderShipPrice($order_info, 'admin', session('member_name'), $post['shipping_fee']);
        }
    }


    /**
     * 系统收到货款
     * @throws Exception
     */
    private function _order_receive_pay($order_info, $post)
    {
        $order_model = model('order');
        $logic_order = model('order', 'logic');
        $if_allow = $order_model->getOrderOperateState('system_receive_pay', $order_info);
        if (!$if_allow) {
            return ds_callback(false, '无权操作');
        }

        if (!request()->isPost()) {
            View::assign('order_info', $order_info);
            //显示支付接口列表
            $payment_list = model('payment')->getPaymentOpenList();
            //去掉预存款和货到付款
            foreach ($payment_list as $key => $value) {
                if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
                    unset($payment_list[$key]);
                }
            }
            View::assign('payment_list', $payment_list);
            $this->setAdminCurItem('receive_pay');
            echo View::fetch('receive_pay');
            exit;
        } else {
            $order_list = $order_model->getOrderList(array('pay_sn' => $order_info['pay_sn'], 'order_state' => ORDER_STATE_NEW));
            try{
                Db::startTrans();
                $logic_order->changeOrderReceivePay($order_list, 'system', $this->admin_info['admin_name'], $post);
            } catch (\Exception $e) {
                Db::rollback();
                return ds_callback(false, $e->getMessage());
            }
            Db::commit();    
            $this->log('将订单改为已收款状态,' . lang('order_number') . ':' . $order_info['order_sn'], 1);
            return ds_callback(true, lang('ds_common_op_succ'));
        }
    }

    /**
     * 查看订单
     *
     */
    public function show_order()
    {
        $order_id = intval(input('param.order_id'));
        if ($order_id <= 0) {
            $this->error(lang('miss_order_number'));
        }
        $order_model = model('order');
        $order_info = $order_model->getOrderInfo(array('order_id' => $order_id), array('order_goods', 'order_common'));

        //订单变更日志
        $log_list = $order_model->getOrderlogList(array('order_id' => $order_info['order_id']));
        View::assign('order_log', $log_list);

        //退款退货信息
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $condition[] = array('order_id','=',$order_info['order_id']);
        $condition[] = array('admin_time','>', 0);
        $return_list = $refundreturn_model->getReturnList($condition);
        View::assign('return_list', $return_list);

        //退款信息
        $refund_list = $refundreturn_model->getRefundList($condition);
        View::assign('refund_list', $refund_list);

        //卖家发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = model('daddress')->getAddressInfo(array('daddress_id' => $order_info['extend_order_common']['daddress_id']));
            View::assign('daddress_info', $daddress_info);
        }
        View::assign('order_info', $order_info);
        return View::fetch('show_order');
    }

    /**
     * 导出
     *
     */
    public function export_step1()
    {

        $order_model = model('order');
        $condition = array();
        $order_sn = input('param.order_sn');
        if ($order_sn) {
            $condition[] = array('order_sn','=',$order_sn);
        }
        $order_state = input('param.order_state');
        if (in_array($order_state, array('0', '10', '20', '30', '40'))) {
            $condition[] = array('order_state','=',$order_state);
        }
        $payment_code = input('param.payment_code');
        if ($payment_code) {
            $condition[] = array('payment_code','=',$payment_code);
        }
        $buyer_name = input('param.buyer_name');
        if ($buyer_name) {
            $condition[] = array('buyer_name','=',$buyer_name);
        }
        $query_start_time = input('param.query_start_time');
        $query_end_time = input('param.query_end_time');
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_time);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_time);
        $start_unixtime = $if_start_time ? strtotime($query_start_time) : null;
        $end_unixtime = $if_end_time ? strtotime($query_end_time) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition[] = array('add_time','between', array($start_unixtime, $end_unixtime));
        }

        if (!is_numeric(input('param.page'))) {
            $count = $order_model->getOrderCount($condition);
            $export_list = array();
            if ($count > self::EXPORT_SIZE) { //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $export_list[$i] = $limit1 . ' ~ ' . $limit2;
                }
                View::assign('export_list', $export_list);
                return View::fetch('/public/excel');
            } else { //如果数量小，直接下载
                $data = $order_model->getOrderList($condition, 0, '*', 'order_id desc', self::EXPORT_SIZE);
                $this->createExcel($data);
            }
        } else { //下载
            $limit1 = (input('param.page') - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $order_model->getOrderList($condition, $limit2, '*', 'order_id desc');
            $this->createExcel($data);
        }
    }

    /**
     * 发货
     */
    public function send()
    {
        $order_id = input('param.order_id');
        if ($order_id <= 0) {
            $this->error(lang('param_error'));
        }

        $order_model = model('order');
        $condition = array();
        $condition[] = array('order_id','=',$order_id);
        $order_info = $order_model->getOrderInfo($condition, array('order_common', 'order_goods'));
        $if_allow_send = intval($order_info['lock_state']) || !in_array($order_info['order_state'], array(ORDER_STATE_PAY, ORDER_STATE_SEND));
        if ($if_allow_send) {
            $this->error(lang('param_error'));
        }

        if (!request()->isPost()) {
            View::assign('order_info', $order_info);
            //取发货地址
            $daddress_model = model('daddress');
            $daddress_info = array();
            if ($order_info['extend_order_common']['daddress_id'] > 0) {
                $daddress_info = $daddress_model->getAddressInfo(array('daddress_id' => $order_info['extend_order_common']['daddress_id']));
            }
            if (empty($daddress_info)) {
                //取默认地址
                $daddress_info = $daddress_model->getAddressList(array(), '*', 'daddress_isdefault desc', 1);
                if (!empty($daddress_info)) {
                    $daddress_info = $daddress_info[0];
                    //写入发货地址编号
                    $this->_edit_order_daddress($daddress_info['daddress_id'], $order_id);
                } else {
                    //写入发货地址编号
                    $this->_edit_order_daddress(0, $order_id);
                }
            }
            View::assign('daddress_info', $daddress_info);

            //如果是自提订单
            $express_list = rkcache('express', true);


            View::assign('express_list', $express_list);

            return View::fetch('send');
        } else {
            $logic_order = model('order', 'logic');
            $post = input('post.');
            $post['reciver_info'] = $this->_get_reciver_info();
            $result = $logic_order->changeOrderSend($order_info, 'admin', session('admin_name'), $post);
            if (!$result['code']) {
                $this->error($result['msg']);
            } else {
                $this->success($result['msg'], url('order/index'));
            }
        }
    }

    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array())
    {
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/export.lang.php');
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('order_number'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('buyer_name'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('order_time'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('order_price_from'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('order_total_transport'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_paytype'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('order_state'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_buyerid'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_bemail'));
        //data
        foreach ((array)$data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => 'DS' . $v['order_sn']);
            $tmp[] = array('data' => $v['buyer_name']);
            $tmp[] = array('data' => date('Y-m-d H:i:s', $v['add_time']));
            $tmp[] = array('format' => 'Number', 'data' => ds_price_format($v['order_amount']));
            $tmp[] = array('format' => 'Number', 'data' => ds_price_format($v['shipping_fee']));
            $tmp[] = array('data' => get_order_payment_name($v['payment_code']));
            $tmp[] = array('data' => get_order_state($v));
            $tmp[] = array('data' => $v['buyer_id']);
            $tmp[] = array('data' => $v['buyer_email']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('ds_orders'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('ds_orders'), CHARSET) . input('param.page') . '-' . date('Y-m-d-H', TIMESTAMP));
    }

}
