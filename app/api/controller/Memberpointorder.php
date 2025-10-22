<?php

namespace app\api\controller;
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
 * 积分兑换控制器
 */
class Memberpointorder extends MobileMember {

    public function initialize() {
        parent::initialize();

        //判断系统是否开启积分和积分兑换功能
        if (config('ds_config.points_isuse') != 1 || config('ds_config.pointprod_isuse') != 1) {
            ds_json_encode(10001,'未开启积分兑换功能!');
            die();
        }
    }

    public function index() {
        $this->orderlist();
    }


    /**
     * @api {POST} api/Memberpointorder/orderlist 兑换信息列表
     * @apiVersion 3.0.6
     * @apiGroup Memberpointorder
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {String} state_type 订单状态 state_pay待发货 state_finish已完成 state_send待收货 state_cancel已取消
     * @apiParam {String} order_key 订单号
     * @apiParam {Int} page 当前页数
     * @apiParam {Int} per_page 每页数量
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.order_list  订单列表
     * @apiSuccess {Int} result.order_list.point_addtime  添加时间
     * @apiSuccess {Int} result.order_list.point_allpoint  兑换总积分
     * @apiSuccess {String} result.order_list.point_buyeremail  用户邮箱
     * @apiSuccess {Int} result.order_list.point_buyerid 用户ID
     * @apiSuccess {String} result.order_list.point_buyername  用户名称
     * @apiSuccess {Int} result.order_list.point_finnshedtime  完成时间
     * @apiSuccess {Boolean} result.order_list.point_orderallowcancel  允许取消 true是false否
     * @apiSuccess {Boolean} result.order_list.point_orderallowdelete  允许删除 true是false否
     * @apiSuccess {Boolean} result.order_list.point_orderalloweditship  允许修改发货信息 true是false否
     * @apiSuccess {Boolean} result.order_list.point_orderallowreceiving  允许收货 true是false否
     * @apiSuccess {Boolean} result.order_list.point_orderallowship  允许发货 true是false否
     * @apiSuccess {Int} result.order_list.point_orderid  订单ID
     * @apiSuccess {String} result.order_list.point_ordermessage  订单留言
     * @apiSuccess {String} result.order_list.point_ordersn  订单编号
     * @apiSuccess {Int} result.order_list.point_orderstate  订单状态
     * @apiSuccess {String} result.order_list.point_orderstatesign  订单状态代码
     * @apiSuccess {String} result.order_list.point_orderstatetext  订单状态描述
     * @apiSuccess {String} result.order_list.point_shipping_ecode  物流公司代码
     * @apiSuccess {String} result.order_list.point_shippingcode  物流单号
     * @apiSuccess {Int} result.order_list.point_shippingtime  发货时间
     * @apiSuccess {Object[]} result.order_list.prodlist  订单商品列表
     * @apiSuccess {String} result.order_list.prodlist.point_goodsimage_old  商品图片名称
     * @apiSuccess {String} result.order_list.prodlist.point_goodsimage_small  商品缩略图完整路径
     * @apiSuccess {Int} result.order_list.prodlist.pointog_goodsid  商品ID
     * @apiSuccess {String} result.order_list.prodlist.pointog_goodsimage  商品图片完整路径
     * @apiSuccess {String} result.order_list.prodlist.pointog_goodsname  商品名称
     * @apiSuccess {Int} result.order_list.prodlist.pointog_goodsnum  兑换数量
     * @apiSuccess {Int} result.order_list.prodlist.pointog_goodspoints  所需积分
     * @apiSuccess {Int} result.order_list.prodlist.pointog_orderid  订单ID
     * @apiSuccess {Int} result.order_list.prodlist.pointog_recid  订单商品ID
     * @apiSuccess {String} result.order_list.state_desc  状态描述
     * @apiSuccess {Int} result.page_total  总页数
     * @apiSuccess {Boolean} result.hasmore  是否有更多 true是false否
     */
    public function orderlist() {
        //兑换信息列表
        $where = array();
        $where[] = array('point_buyerid','=',$this->member_info['member_id']);
        $search_point_ordersn = input('param.order_key');
        if ($search_point_ordersn){
            $where[] = array('point_ordersn','like', '%' . $search_point_ordersn . '%');
        }
        $state_type=input('param.state_type');
        switch ($state_type) {
            case 'state_pay':
                $where[] = array('point_orderstate','=','20');
                break;
            case 'state_finish':
                $where[] = array('point_orderstate','=','40');
                break;
            case 'state_send':
                $where[] = array('point_orderstate','=','30');
                break;
            case 'state_cancel':
                $where[] = array('point_orderstate','=','2');
                break;
        }
        $pointorder_model = model('pointorder');
        $order_list = $pointorder_model->getPointorderList($where, '*', 10, 0, 'point_orderid desc');
        $order_idarr = array();
        $order_listnew = array();
        if (is_array($order_list) && count($order_list) > 0) {
            foreach ($order_list as $k => $v) {
                $v['state_desc'] = '';
                switch ($v['point_orderstate']) {
                    case '20';
                        $v['state_desc'] = '待发货';
                        break;
                    case '30';
                        $v['state_desc'] = '已发货';
                        break;
                    case '40';
                        $v['state_desc'] = '已收货';
                        break;
                    case '50';
                        $v['state_desc'] = '已完成';
                        break;
                    case '2';
                        $v['state_desc'] = '已取消';
                        break;
                }
                $order_listnew[$v['point_orderid']] = $v;
                $order_idarr[] = $v['point_orderid'];
            }
        }
        $order_listnew1 = array();
        //查询兑换商品
        if (is_array($order_idarr) && count($order_idarr) > 0) {
            $prod_list = $pointorder_model->getPointordergoodsList(array(array('pointog_orderid','in', $order_idarr)));
            if (is_array($prod_list) && count($prod_list) > 0) {
                foreach ($prod_list as $v) {
                    if (isset($order_listnew[$v['pointog_orderid']])) {
                        $order_listnew[$v['pointog_orderid']]['prodlist'][] = $v;
                    }
                }

                foreach ($order_listnew as $k => $v) {
                    $order_listnew1[] = $v;
                }
            }
        }
        ds_json_encode(10000, '',array('order_list' => $order_listnew1));
    }


    /**
     * @api {POST} api/Memberpointorder/cancel_order 取消兑换
     * @apiVersion 3.0.6
     * @apiGroup Memberpointorder
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {Int} order_id 订单ID
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function cancel_order() {
        $pointorder_model = model('pointorder');
        //取消订单
        $data = $pointorder_model->cancelPointorder(input('post.order_id'), $this->member_info['member_id']);
        if ($data['state']) {
            ds_json_encode(10000, '',1);
        } else {
            ds_json_encode(10001,'取消失败!');
            die();
        }
    }

    /**
     * @api {POST} api/Memberpointorder/receiving_order 确认收货
     * @apiVersion 3.0.6
     * @apiGroup Memberpointorder
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {Int} order_id 订单ID
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function receiving_order() {
        $data = model('pointorder')->receivingPointorder(input('post.order_id'));
        if ($data['state']) {
            ds_json_encode(10000, '',1);
        } else {
            ds_json_encode(10001,'处理失败!');
            die();
        }
    }


    /**
     * @api {POST} api/Memberpointorder/order_info 兑换信息详细
     * @apiVersion 3.0.6
     * @apiGroup Memberpointorder
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {Int} order_id 订单ID
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object} result.express_info  物流公司信息
     * @apiSuccess {Object} result.order_info  订单信息 （返回字段参考pointsorder）
     * @apiSuccess {Object} result.orderaddress_info  收货地址信息 （返回字段参考pointsorderaddress）
     * @apiSuccess {Object} result.pointorderstate_arr  订单状态信息
     * @apiSuccess {Array} result.pointorderstate_arr.canceled 取消信息 值为状态ID以及状态描述 
     * @apiSuccess {Array} result.pointorderstate_arr.finished 完成信息 值为状态ID以及状态描述 
     * @apiSuccess {Array} result.pointorderstate_arr.waitreceiving 待收货信息 值为状态ID以及状态描述 
     * @apiSuccess {Array} result.pointorderstate_arr.waitship 待发货信息 值为状态ID以及状态描述 
     * @apiSuccess {Object[]} result.prod_list  订单状态信息 （返回字段参考pointsordergoods）
     */
    public function order_info() {
        $order_id = intval(input('param.order_id'));
        if ($order_id <= 0) {
            ds_json_encode(10001,'订单不正确!');
            die();
        }
        $pointorder_model = model('pointorder');
        //查询兑换订单信息
        $where = array();
        $where[] = array('point_orderid','=',$order_id);
        $where[] = array('point_buyerid','=',$this->member_info['member_id']);
        $order_info = $pointorder_model->getPointorderInfo($where);
        if (!$order_info) {
            ds_json_encode(10001,'订单不存在!');
            die();
        }
        if ($order_info['point_addtime']) {
            $order_info['point_addtime'] = date('Y-m-d H:i:s', $order_info['point_addtime']);
        } else {
            $order_info['point_addtime'] = '';
        }
        if ($order_info['point_shippingtime']) {
            $order_info['point_shippingtime'] = date('Y-m-d H:i:s', $order_info['point_shippingtime']);
        } else {
            $order_info['point_shippingtime'] = '';
        }
        if ($order_info['point_finnshedtime']) {
            $order_info['point_finnshedtime'] = date('Y-m-d H:i:s', $order_info['point_finnshedtime']);
        } else {
            $order_info['point_finnshedtime'] = '';
        }
        //获取订单状态
        $pointorderstate_arr = $pointorder_model->getPointorderStateBySign();

        //查询兑换订单收货人地址
        $orderaddress_info = $pointorder_model->getPointorderAddressInfo(array('pointoa_orderid' => $order_id));

        //兑换商品信息
        $prod_list = $pointorder_model->getPointordergoodsList(array('pointog_orderid' => $order_id));

        //物流公司信息
        $express_info = "";
        if ($order_info['point_shipping_ecode'] != '') {
            $data = model('express')->getExpressInfoByECode($order_info['point_shipping_ecode']);
            if ($data['state']) {
                $express_info = $data['data']['express_info'];
            }
        }
        ds_json_encode(10000, '',array('order_info' => $order_info, 'express_info' => $express_info, 'prod_list' => $prod_list, 'orderaddress_info' => $orderaddress_info, 'pointorderstate_arr' => $pointorderstate_arr));
    }

}

?>
