<?php

namespace app\api\controller;

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
 * 订单控制器
 */
class Memberorder extends MobileMember {

    public function initialize() {
        parent::initialize();
    }

    /**
     * @api {POST} api/Memberorder/order_list 订单列表
     * @apiVersion 3.0.6
     * @apiGroup MemberOrder
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {Int} page 当前页数
     * @apiParam {Int} state_type 订单状态
     * @apiParam {String} order_key 订单编号
     * @apiParam {Int} per_page 每页数量
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.order_group_list  订单组列表
     * @apiSuccess {Int} result.order_group_list.add_time  添加时间
     * @apiSuccess {Object[]} result.order_group_list.order_list  订单列表 （返回字段参考order表）
     * @apiSuccess {Object} result.order_group_list.order_list.extend_order_common  订单公共信息 （返回字段参考ordercommon）
     * @apiSuccess {Int} result.order_group_list.order_list.if_cancel  是否可取消 true是false否
     * @apiSuccess {Int} result.order_group_list.order_list.if_delete  是否可删除 true是false否
     * @apiSuccess {Int} result.order_group_list.order_list.if_deliver  是否可发货 true是false否
     * @apiSuccess {Int} result.order_group_list.order_list.if_evaluation  是否可评价 true是false否
     * @apiSuccess {Int} result.order_group_list.order_list.if_lock  是否被锁定 true是false否
     * @apiSuccess {Int} result.order_group_list.order_list.if_receive  是否可收货 true是false否
     * @apiSuccess {Int} result.order_group_list.order_list.if_refund_cancel  是否可全部退款 true是false否
     * @apiSuccess {Int} result.order_group_list.pay_amount  支付时间
     * @apiSuccess {String} result.order_group_list.pay_sn  支付单号
     * @apiSuccess {Int} result.page_total  总页数
     * @apiSuccess {Boolean} result.hasmore  是否有更多 true是false否
     */
    public function order_list() {
        $order_model = model('order');
        $condition = array();
        $condition = $this->order_type_no(input('post.state_type'));
        $condition[] = array('buyer_id', '=', $this->member_info['member_id']);
        $condition[] = array('delete_state', '=', 0); #订单未被删除
        $order_sn = input('post.order_key');
        if ($order_sn != '') {
            $condition[] = array('order_sn', 'like', '%' . $order_sn . '%');
        }
        $keyword = input('post.keyword');
        if ($keyword != '') {
            $goodscondition = array();
            $goodscondition[] = array('goods_name', 'like', '%' . trim($keyword) . '%');
            $orderidarray = $order_model->getOrdergoodsList($goodscondition, 'order_id');
            $orderidarray = array_column($orderidarray, 'order_id');
            $orderstr = implode(',', $orderidarray);
            $condition[] = array('order_id', 'in', $orderstr);
        }
        $refundreturn_model = model('refundreturn');
        $order_list_array = $order_model->getOrderList($condition, 5, '*', 'order_id desc', '', array('order_common', 'order_goods'));
        $order_list_array = $refundreturn_model->getGoodsRefundList($order_list_array, 1); //订单商品的退款退货显示

        $order_group_list = $order_pay_sn_array = array();
        foreach ($order_list_array as $value) {
            //$value['zengpin_list'] = false;
            //显示取消订单
            $value['if_cancel'] = $order_model->getOrderOperateState('buyer_cancel', $value);
            //显示退款取消订单
            $value['if_refund_cancel'] = $order_model->getOrderOperateState('refund_cancel', $value);
            //显示收货
            $value['if_receive'] = $order_model->getOrderOperateState('receive', $value);

            //显示锁定中
            $value['if_lock'] = $order_model->getOrderOperateState('lock', $value);
            //显示物流跟踪
            $value['if_deliver'] = $order_model->getOrderOperateState('deliver', $value);

            $value['if_evaluation'] = $order_model->getOrderOperateState('evaluation', $value);
            $value['if_delete'] = $order_model->getOrderOperateState('delete', $value);
            $value['ownshop'] = true;

            $value['zengpin_list'] = false;
            if (isset($value['extend_order_goods'])) {
                foreach ($value['extend_order_goods'] as $val) {
                    $val['image_240_url'] = goods_cthumb($val['goods_image'], 240);
                    $val['image_url'] = goods_cthumb($val['goods_image'], 240);
                    $val['goods_type_cn'] = get_order_goodstype($val['goods_type']);
                    if ($val['goods_type'] == 5) {
                        $value['zengpin_list'][] = $val;
                    }
                }
            }

            //商品图
            if (isset($value['extend_order_goods'])) {
                foreach ($value['extend_order_goods'] as $k => $goods_info) {

                    if ($goods_info['goods_type'] == 5) {
                        unset($value['extend_order_goods'][$k]);
                    } else {
                        $value['extend_order_goods'][$k] = $goods_info;
                        $value['extend_order_goods'][$k]['goods_image_url'] = goods_cthumb($goods_info['goods_image'], 240);
                    }
                }
            }
            $order_group_list[$value['pay_sn']]['order_list'][] = $value;
            //如果有在线支付且未付款的订单则显示合并付款链接
            if ($value['order_state'] == ORDER_STATE_NEW) {
                if (!isset($order_group_list[$value['pay_sn']]['pay_amount'])) {
                    $order_group_list[$value['pay_sn']]['pay_amount'] = 0;
                }
                $order_group_list[$value['pay_sn']]['pay_amount'] += $value['order_amount'] - $value['rcb_amount'] - $value['pd_amount'];
            }
            $order_group_list[$value['pay_sn']]['add_time'] = $value['add_time'];

            //记录一下pay_sn，后面需要查询支付单表
            $order_pay_sn_array[] = $value['pay_sn'];
        }

        $new_order_group_list = array();
        foreach ($order_group_list as $key => $value) {
            $value['pay_sn'] = strval($key);
            $new_order_group_list[] = $value;
        }
        $result = array_merge(array('order_group_list' => $new_order_group_list), mobile_page($order_model->page_info));
        ds_json_encode(10000, '', $result);
    }

    private function order_type_no($stage) {
        $condition = array();
        switch ($stage) {
            case 'state_new':
                $condition[] = array('order_state', '=', '10');
                break;
            case 'state_pay':
                $condition[] = array('order_state', '=', '20');
                break;
            case 'state_send':
                $condition[] = array('order_state', '=', '30');
                break;
            case 'state_noeval':
                $condition[] = array('order_state', '=', '40');
                $condition[] = array('refund_state', '=', '0');
                $condition[] = array('evaluation_state', '=', '0');
                break;
        }
        return $condition;
    }

    /**
     * @api {POST} api/Memberorder/order_cancel 取消订单
     * @apiVersion 3.0.6
     * @apiGroup MemberOrder
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {Int} order_id 订单号
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function order_cancel() {
        $order_model = model('order');
        $logic_order = model('order', 'logic');
        $order_id = intval(input('post.order_id'));

        $condition = array();
        $condition[] = array('order_id', '=', $order_id);
        $condition[] = array('buyer_id', '=', $this->member_info['member_id']);
        //$condition[] = array('order_type','=',1);
        $order_info = $order_model->getOrderInfo($condition);
        $if_allow = $order_model->getOrderOperateState('buyer_cancel', $order_info);
        if (!$if_allow) {
            ds_json_encode(10001, '无权操作');
        }
        Db::startTrans();
        try {

            $logic_order->changeOrderStateCancel($order_info, 'buyer', $this->member_info['member_name'], '其它原因');
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            ds_json_encode(10001, $e->getMessage());
        }

        ds_json_encode(10000, lang('ds_common_op_succ'));
    }

    /**
     * @api {POST} api/Memberorder/order_receive 订单确认收货
     * @apiVersion 3.0.6
     * @apiGroup MemberOrder
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {Int} order_id 订单号
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function order_receive() {
        $order_model = model('order');
        $logic_order = model('order', 'logic');
        $order_id = intval(input('post.order_id'));

        $condition = array();
        $condition[] = array('order_id', '=', $order_id);
        $condition[] = array('buyer_id', '=', $this->member_info['member_id']);
        $order_info = $order_model->getOrderInfo($condition);
        $if_allow = $order_model->getOrderOperateState('receive', $order_info);
        if (!$if_allow) {
            ds_json_encode(10001, '无权操作');
        }

        $result = $logic_order->changeOrderStateReceive($order_info, 'buyer', $this->member_info['member_name'], '签收了货物');
        if (!$result['code']) {
            ds_json_encode(10001, $result['msg']);
        } else {
            ds_json_encode(10000, '', 1);
        }
    }

    /**
     * 回收站
     */
    public function order_delete() {
        $order_model = model('order');
        $logic_order = model('order', 'logic');
        $order_id = intval(input('post.order_id'));

        $condition = array();
        $condition[] = array('order_id', '=', $order_id);
        $condition[] = array('buyer_id', '=', $this->member_info['member_id']);
        $order_info = $order_model->getOrderInfo($condition);
        $if_allow = $order_model->getOrderOperateState('delete', $order_info);
        if (!$if_allow) {
            ds_json_encode(10001, '无权操作');
        }

        $result = $logic_order->changeOrderStateRecycle($order_info, 'buyer', 'delete');
        if (!$result['code']) {
            ds_json_encode(10001, $result['msg']);
        } else {
            ds_json_encode(10000, '', 1);
        }
    }

    /**
     * @api {POST} api/Memberorder/search_deliver 物流跟踪
     * @apiVersion 3.0.6
     * @apiGroup Memberorder
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {String} order_id 订单id
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {String} result.express_name  物流公司名称
     * @apiSuccess {String} result.shipping_code  物流单号
     * @apiSuccess {Object[]} result.deliver_info  物流数据
     * @apiSuccess {String} result.deliver_info.context  内容
     * @apiSuccess {String} result.deliver_info.time  时间
     */
    public function search_deliver() {
        $order_id = intval(input('post.order_id'));
        if ($order_id <= 0) {
            ds_json_encode(10001, '订单不存在');
        }

        $order_model = model('order');
        $condition[] = array('order_id', '=', $order_id);
        $condition[] = array('buyer_id', '=', $this->member_info['member_id']);
        $order_info = $order_model->getOrderInfo($condition, array('order_common', 'order_goods'));
        if (empty($order_info) || !in_array($order_info['order_state'], array(ORDER_STATE_SEND, ORDER_STATE_SUCCESS))) {
            ds_json_encode(10001, '订单不存在');
        }

        $express = rkcache('express', true);
        if (isset($express[$order_info['extend_order_common']['shipping_express_id']])) {
            $express_code = $express[$order_info['extend_order_common']['shipping_express_id']]['express_code'];
            $express_name = $express[$order_info['extend_order_common']['shipping_express_id']]['express_name'];
            $deliver_info = model('express')->queryExpress($express_code, $order_info['shipping_code'], $order_info['extend_order_common']['reciver_info']['phone']);
        } else {
            $express_name = '';
            $deliver_info = array();
        }
        ds_json_encode(10000, '', array('express_name' => $express_name, 'shipping_code' => $order_info['shipping_code'], 'deliver_info' => $deliver_info));
    }

    /**
     * @api {POST} api/Memberorder/order_info 订单详情
     * @apiVersion 3.0.6
     * @apiGroup MemberOrder
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {Int} order_id 订单ID
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object} result.order_info  订单信息 （返回字段参考order）
     * @apiSuccess {Object} result.order_info.extend_order_common  订单公共信息 （返回字段参考ordercommon）
     * @apiSuccess {Int} result.order_info.goods_count  商品数量
     * @apiSuccess {Object[]} result.order_info.goods_list  商品列表 （返回字段参考ordergoods）
     * @apiSuccess {Int} result.order_info.if_cancel  是否可取消 true是false否
     * @apiSuccess {Int} result.order_info.if_delete  是否可删除 true是false否
     * @apiSuccess {Int} result.order_info.if_deliver  是否显示物流跟踪 true是false否
     * @apiSuccess {Int} result.order_info.if_evaluation  是否可评价 true是false否
     * @apiSuccess {Int} result.order_info.if_lock  是否被锁定 true是false否
     * @apiSuccess {Int} result.order_info.if_receive  是否可收货 true是false否
     * @apiSuccess {Int} result.order_info.if_refund_cancel  是否可全部退款 true是false否
     * @apiSuccess {String} result.order_info.real_pay_amount  实际支付金额
     * @apiSuccess {String} result.order_info.reciver_addr  收货地址
     * @apiSuccess {String} result.order_info.reciver_name  收货人姓名
     * @apiSuccess {String} result.order_info.reciver_phone  收货人手机
     * @apiSuccess {object} result.order_info.zengpin_list  赠品列表
     */
    public function order_info() {
        $order_id = intval(input('order_id'));
        if ($order_id <= 0) {
            ds_json_encode(10001, '订单不存在');
        }
        $order_model = model('order');
        $condition = array();
        $condition[] = array('order_id', '=', $order_id);
        $condition[] = array('buyer_id', '=', $this->member_info['member_id']);
        $order_info = $order_model->getOrderInfo($condition, array('order_goods', 'order_common'));

        if (empty($order_info) || $order_info['delete_state'] == ORDER_DEL_STATE_DROP) {
            ds_json_encode(10001, '订单不存在');
        }

        $refundreturn_model = model('refundreturn');
        $order_list = array();
        $order_list[$order_id] = $order_info;
        $order_list = $refundreturn_model->getGoodsRefundList($order_list, 1); //订单商品的退款退货显示
        $order_info = $order_list[$order_id];
        $refund_all = isset($order_info['refund_list'][0]) ? $order_info['refund_list'][0] : '';
        if (!empty($refund_all)) {//订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
            $result['refund_all'] = $refund_all;
        }

        if ($order_info['payment_time']) {
            $order_info['payment_time'] = date('Y-m-d H:i:s', $order_info['payment_time']);
        } else {
            $order_info['payment_time'] = '';
        }
        if ($order_info['finnshed_time']) {
            $order_info['finnshed_time'] = date('Y-m-d H:i:s', $order_info['finnshed_time']);
        } else {
            $order_info['finnshed_time'] = '';
        }
        if ($order_info['add_time']) {
            $order_info['add_time'] = date('Y-m-d H:i:s', $order_info['add_time']);
        } else {
            $order_info['add_time'] = '';
        }

        if ($order_info['extend_order_common']['order_message']) {
            $order_info['order_message'] = $order_info['extend_order_common']['order_message'];
        }
//        if(!empty($order_info['extend_order_common']['invoice_info'])) {
//            $order_info['invoice'] = $order_info['extend_order_common']['invoice_info']['类型'] . $order_info['extend_order_common']['invoice_info']['抬头'] . $order_info['extend_order_common']['invoice_info']['内容'];
//        }

        $order_info['reciver_phone'] = $order_info['extend_order_common']['reciver_info']['phone'];
        $order_info['reciver_name'] = $order_info['extend_order_common']['reciver_name'];
        $order_info['reciver_addr'] = $order_info['extend_order_common']['reciver_info']['address'];

        $order_info['promotion'] = stripslashes(strip_tags($order_info['extend_order_common']['promotion_info']));
        $order_info['voucher_code'] = $order_info['extend_order_common']['voucher_code'];
        $order_info['voucher_price'] = $order_info['extend_order_common']['voucher_price'];
        //显示锁定中
        $order_info['if_lock'] = $order_model->getOrderOperateState('lock', $order_info);

        //显示取消订单
        $order_info['if_buyer_cancel'] = $order_model->getOrderOperateState('buyer_cancel', $order_info);

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
            $order_info['order_cancel_day'] = date('Y-m-d H:i:s', strtotime($order_info['add_time']) + config('ds_config.order_auto_cancel_day') * 24 * 3600);
        }

        $order_info['if_deliver'] = false;
        //显示快递信息
        if ($order_info['shipping_code'] != '') {
            $order_info['if_deliver'] = true;
            $express = rkcache('express', true);
            if (isset($express[$order_info['extend_order_common']['shipping_express_id']])) {
                $order_info['express_info']['express_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['express_code'];
                $order_info['express_info']['express_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['express_name'];
                $order_info['express_info']['express_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['express_url'];
            } else {
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
            $close_info = model('orderlog')->getOrderlogInfo(array('order_id' => $order_info['order_id']), 'log_id desc');
            $order_info['close_info'] = $close_info;
            $order_info['order_tips'] = $close_info['log_msg'];
        }
        foreach ($order_info['extend_order_goods'] as $value) {
            $value['image_240_url'] = goods_cthumb($value['goods_image'], 240);
            $value['image_url'] = goods_cthumb($value['goods_image'], 240);
            $value['goods_type_cn'] = get_order_goodstype($value['goods_type']);
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

        $order_info['real_pay_amount'] = $order_info['order_amount'];
        //取得其它订单类型的信息000--------------------------------
        //$order_model->getOrderExtendInfo($order_info);

        $result['order_info'] = $order_info;
        ds_json_encode(10000, '', $result);
    }
}

?>
