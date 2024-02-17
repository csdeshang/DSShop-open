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
class Orderinviter extends BaseModel {
    
    /**
     * 支付给钱
     * @access public
     * @author csdeshang
     * @param type $order_id 订单编号
     */
    public function giveMoney($order_id) {
        $orderinviter_list = Db::name('orderinviter')->where(array('orderinviter_order_id'=>$order_id))->select()->toArray();
        if ($orderinviter_list) {
            $predeposit_model = model('predeposit');
            foreach ($orderinviter_list as $val) {
                try {
                    Db::startTrans();
                    $data = array();
                    $data['member_id'] = $val['orderinviter_member_id'];
                    $data['member_name'] = $val['orderinviter_member_name'];
                    $data['amount'] = $val['orderinviter_money'];
                    $data['order_sn'] = $val['orderinviter_order_sn'];
                    $data['lg_desc'] = $val['orderinviter_remark'];
                    $predeposit_model->changePd('order_inviter', $data);
                    Db::name('orderinviter')->where('orderinviter_id', $val['orderinviter_id'])->update(['orderinviter_valid' => 1]);
                    if($val['orderinviter_level']==1){
                        //更新商品的分销情况
                        Db::name('goodscommon')->where('goods_commonid='.$val['orderinviter_goods_commonid'])->inc('inviter_total_quantity',$val['orderinviter_goods_quantity'])->update();
                        Db::name('goodscommon')->where('goods_commonid='.$val['orderinviter_goods_commonid'])->inc('inviter_total_amount',$val['orderinviter_goods_amount'])->update();
                        }
                        Db::name('goodscommon')->where('goods_commonid='.$val['orderinviter_goods_commonid'])->inc('inviter_amount',$val['orderinviter_money'])->update();
                        //更新分销员的分销情况
                        Db::name('inviter')->where('inviter_id='.$val['orderinviter_member_id'])->inc('inviter_goods_quantity',$val['orderinviter_goods_quantity'])->update();
                        Db::name('inviter')->where('inviter_id='.$val['orderinviter_member_id'])->inc('inviter_goods_amount',$val['orderinviter_goods_amount'])->update();
                        Db::name('inviter')->where('inviter_id='.$val['orderinviter_member_id'])->inc('inviter_total_amount',$val['orderinviter_money'])->update();
                    
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                }
            }
        }
    }

}
