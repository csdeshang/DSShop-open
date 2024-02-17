<?php

namespace app\common\model;

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
 * 数据层模型
 */
class Vrrefund extends BaseModel {

    public $page_info;

    /**
     * 增加退款
     * @access public
     * @author csdeshang
     * @param type $refund_array 退款数组数据
     * @param type $order 排序
     * @return boolean
     */
    public function addVrrefund($refund_array, $order = array()) {
        if (!empty($order) && is_array($order)) {
            $refund_array['order_id'] = $order['order_id'];
            $refund_array['order_sn'] = $order['order_sn'];
            $refund_array['buyer_id'] = $order['buyer_id'];
            $refund_array['buyer_name'] = $order['buyer_name'];
            $refund_array['goods_id'] = $order['goods_id'];
            $refund_array['goods_name'] = $order['goods_name'];
            $refund_array['goods_image'] = $order['goods_image'];
        }
        $refund_array['refund_sn'] = $this->getVrrefundSn();

        try {
            Db::startTrans();
            $refund_id = Db::name('vrrefund')->insertGetId($refund_array);
            $code_array = explode(',', $refund_array['redeemcode_sn']);
            $vrorder_model = model('vrorder');
            $vrorder_model->editVrorderCode(array('refund_lock' => 1), array(array('vr_code', 'in', $code_array))); //退款锁定
            Db::commit();
            return $refund_id;
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 平台退款处理
     * @access public
     * @author csdeshang
     * @param type $refund 退款
     * @return boolean
     */
    public function editVrorderRefund($refund) {
        $refund_id = $refund['refund_id'];
        $refund_lock = '0'; //退款锁定状态:0为正常,1为锁定,2为同意
        $vrorder_model = model('vrorder');

        $order_id = $refund['order_id']; //订单编号

        try {
            Db::startTrans();
            $order = $vrorder_model->getVrorderInfo(array('order_id' => $order_id));
            $state = $this->editVrrefund(array('refund_id' => $refund_id), $refund); ////更新退款
            if ($state && $refund['admin_state'] == '2') {//审核状态:1为待审核,2为同意,3为不同意
                $refundreturn_model = model('refundreturn');
                $refundreturn_model->refundAmount($order, $order['order_amount']);

                if ($order['order_promotion_type'] == 2) {//如果是拼团
                    $ppintuangroup_info = Db::name('ppintuangroup')->where('pintuangroup_id', $order['promotions_id'])->lock(true)->find();
                    if ($ppintuangroup_info && $ppintuangroup_info['pintuangroup_state'] == 1) {
                        if ($ppintuangroup_info['pintuangroup_joined'] > 0) {
                            Db::name('ppintuangroup')->where('pintuangroup_id', $order['promotions_id'])->dec('pintuangroup_joined')->update();
                            if ($ppintuangroup_info['pintuangroup_joined'] == 1) {
                                //拼团统计开团数量
                                $condition = array();
                                $condition[] = array('pintuan_id', '=', $ppintuangroup_info['pintuan_id']);
                                $condition[] = array('pintuan_count', '>', 0);
                                Db::name('ppintuan')->where($condition)->dec('pintuan_count')->update();
                            }
                        }
                    }
                }

                $refund_lock = '2';

                if ($state) {
                    $order_array = array();
                    $order_amount = $order['order_amount']; //订单金额
                    $refund_amount = $order['refund_amount'] + $refund['refund_amount']; //退款金额
                    $order_array['refund_state'] = ($order_amount - $refund_amount) > 0 ? 1 : 2;
                    $order_array['refund_amount'] = ds_price_format($refund_amount);
                    $state = $vrorder_model->editVrorder($order_array, array('order_id' => $order_id)); //更新订单退款
                    //修改分销佣金
                    $condition=array();
                    $condition[]=array('orderinviter_order_id','=',$order_id);
                    $condition[]=array('orderinviter_valid','=',0);
                    $condition[]=array('orderinviter_order_type','=',1);
                    $orderinviter_list=Db::name('orderinviter')->where($condition)->select()->toArray();
                    foreach($orderinviter_list as $orderinviter_info){
                        $orderinviter_goods_amount=round($order_amount-$refund_amount,2);
                        $orderinviter_money=round($orderinviter_info['orderinviter_ratio']/100*$orderinviter_goods_amount,2);
                        Db::name('orderinviter')->where(array(array('orderinviter_id','=',$orderinviter_info['orderinviter_id'])))->update(['orderinviter_goods_amount' => $orderinviter_goods_amount,'orderinviter_money'=>$orderinviter_money]);
                    }
                    
                }
            }
            if ($state) {
                $code_array = explode(',', $refund['redeemcode_sn']);
                $state = $vrorder_model->editVrorderCode(array('refund_lock' => $refund_lock), array(array('vr_code', 'in', $code_array))); //更新退款的兑换码
                if ($state && $refund['admin_state'] == '2') {
                    model('vrorder', 'logic')->changeOrderStateSuccess($order_id); //更新订单状态
                }
            }
            Db::commit();
            return $state;
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 修改退款
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $data 数据
     * @return boolean
     */
    public function editVrrefund($condition, $data) {
        if (empty($condition)) {
            return false;
        }
        if (is_array($data)) {
            $result = Db::name('vrrefund')->where($condition)->update($data);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 退款编号
     * @access public
     * @author csdeshang
     * @return string
     */
    public function getVrrefundSn() {
        $result = mt_rand(100, 999) . date('ymdHis');
        return $result;
    }

    /**
     * 退款记录
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 分页
     * @param type $limit 限制
     * @param type $fields 字段
     * @return type
     */
    public function getVrrefundList($condition = array(), $pagesize = '', $field = '*', $order = 'refund_id desc', $limit = 0) {
        if ($pagesize) {
            $result = Db::name('vrrefund')->field($field)->where($condition)->order($order)->paginate(['list_rows' => $pagesize, 'query' => request()->param()], false);
            $this->page_info = $result;
            $result = $result->items();
        } else {
            $result = Db::name('vrrefund')->field($field)->where($condition)->order($order)->limit($limit)->select()->toArray();
        }


        return $result;
    }

    /**
     * 取得退款记录的数量
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function getVrrefundCount($condition) {
        $result = Db::name('vrrefund')->where($condition)->count();
        return $result;
    }

    /**
     * 详细页右侧订单信息
     * @access public
     * @author csdeshang
     * @param type $order_condition 条件
     * @return type
     */
    public function getRightVrorderList($order_condition, $order_id) {
        $vrorder_model = model('vrorder');
        $order_info = $vrorder_model->getVrorderInfo($order_condition);

        $order_list = array();
        $order_list[$order_id] = $order_info;
        $order_list = $vrorder_model->getCodeRefundList($order_list); //没有使用的兑换码列表
        $order_info = $order_list[$order_id];

        //显示退款
        $order_info['if_refund'] = $vrorder_model->getVrorderOperateState('refund', $order_info);

        $code_list = array();
        if ($order_info['if_refund']) {
            $code_list = $order_info['code_list'];
        }
        return array('order_info' => $order_info, 'code_list' => $code_list);
    }

    /**
     * 获取一条退款记录
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function getOneVrrefund($condition) {
        $refund = Db::name('vrrefund')->where($condition)->find();
        return $refund;
    }

    /**
     * 向模板页面输出退款状态
     * @access public
     * @author csdeshang
     * @param type $type 类型
     * @return string
     */
    public function getRefundStateArray($type = 'all') {
        $admin_array = array(
            '1' => '待审核',
            '2' => '同意',
            '3' => '不同意'
        ); //退款状态:1为待审核,2为同意,3为不同意

        $state_data = array(
            'admin' => $admin_array
        );
        if ($type == 'all')
            return $state_data; //返回所有
        return $state_data[$type];
    }

}
