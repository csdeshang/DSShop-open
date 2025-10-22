<?php

namespace app\common\logic;



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
 * 逻辑层模型
 */
class Queue
{

    /**
     * 添加会员积分
     * @param unknown $member_info
     */
    public function addPoint($member_info)
    {
        $points_model = model('points');
        $points_model->savePointslog('login', array(
            'pl_memberid' => $member_info['member_id'], 'pl_membername' => $member_info['member_name']
        ), true);
        return ds_callback(true);
    }

    /**
     * 添加会员经验值
     * @param unknown $member_info
     */
    public function addExppoint($member_info)
    {
        $exppoints_model = model('exppoints');
        $exppoints_model->saveExppointslog('login', array(
            'explog_memberid' => $member_info['member_id'], 'explog_membername' => $member_info['member_name']
        ), true);
        return ds_callback(true);
    }

    /**
     * 更新抢购信息
     * @param unknown $groupbuy_info
     * @throws Exception
     */
    public function editGroupbuySaleCount($groupbuy_info)
    {
        $groupbuy_model = model('groupbuy');
        $data = array();
        $data['groupbuy_buyer_count'] = Db::raw('groupbuy_buyer_count+1');
        $data['groupbuy_buy_quantity'] = Db::raw('groupbuy_buy_quantity+'.$groupbuy_info['quantity']);
        $update = $groupbuy_model->editGroupbuy($data, array('groupbuy_id' => $groupbuy_info['groupbuy_id']));
        if (!$update) {
            return ds_callback(false, '更新抢购信息失败groupbuy_id:' . $groupbuy_info['groupbuy_id']);
        }
        else {
            return ds_callback(true);
        }
    }


    /**
     * 根据商品id更新促销价格
     *
     * @param int /array $goods_commonid
     * @return boolean
     */
    public function updateGoodsPromotionPriceByGoodsId($goods_id)
    {
        if(!is_array($goods_id)){
            $goods_id=(string)$goods_id;
        }
        $update = model('goods')->editGoodsPromotionPrice(array(array('goods_id','in', $goods_id)));
        if (!$update) {
            return ds_callback(false, '根据商品ID更新促销价格失败');
        }
        else {
            return ds_callback(true);
        }
    }

    /**
     * 根据商品公共id更新促销价格
     *
     * @param int /array $goods_commonid
     * @return boolean
     */
    public function updateGoodsPromotionPriceByGoodsCommonId($goods_commonid)
    {
        if(!is_array($goods_commonid)){
            $goods_commonid=(string)$goods_commonid;
        }
        $update = model('goods')->editGoodsPromotionPrice(array(array('goods_commonid','in', $goods_commonid)));
        if (!$update) {
            return ds_callback(false, '根据商品公共id更新促销价格失败');
        }
        else {
            return ds_callback(true);
        }
    }


    /**
     * 发送会员消息
     */
    public function sendMemberMsg($param)
    {
        $send = new \sendmsg\sendMemberMsg();
        $send->set('code', $param['code']);
        $send->set('member_id', $param['member_id']);
        if (!empty($param['number']['mobile']))
            $send->set('mobile', $param['number']['mobile']);
        if (!empty($param['number']['email']))
            $send->set('email', $param['number']['email']);
        $send->send($param['param'],isset($param['weixin_param'])?$param['weixin_param']:array(),isset($param['ali_param'])?$param['ali_param']:array(),isset($param['ten_param'])?$param['ten_param']:array());
        return ds_callback(true);
    }



    /**
     * 清理特殊商品促销信息
     */
    public function clearSpecialGoodsPromotion($param)
    {
        // 抢购
        model('groupbuy')->delGroupbuy(array('goods_commonid' => $param['goods_commonid']));
        // 显示折扣
        model('pxianshigoods')->delXianshigoods(array(array('goods_id','in', $param['goodsid_array'])));
        // 优惠套装
        model('pbundling')->delBundlingGoods(array(array('goods_id','in', $param['goodsid_array'])));
        // 更新促销价格
        model('goods')->editGoods(array('goods_promotion_price' => Db::raw('goods_price'),'goods_promotion_type' => 0), array('goods_commonid' => $param['goods_commonid']));
        return ds_callback(true);
    }

    /**
     * 删除(买/卖家)订单全部数量缓存
     * @param array $data 订单信息
     * @return boolean
     */
    public function delOrderCountCache($order_info)
    {
        if (empty($order_info))
            return ds_callback(true);
        $order_model = model('order');
        if (isset($order_info['order_id'])) {
            $order_info = $order_model->getOrderInfo(array('order_id' => $order_info['order_id']), array(), 'buyer_id');
        }
        if(isset($order_info['buyer_id'])) {
            $order_model->delOrderCountCache('buyer', $order_info['buyer_id']);
        }
        return ds_callback(true);
    }


    /**
     * 生成卡密代金券
     */
    public function build_pwdvoucher($t_id)
    {
        $t_id = intval($t_id);
        if ($t_id <= 0) {
            return ds_callback(false, '参数错误');
        }
        $voucher_model = model('voucher');
        //查询代金券详情
        $where = array();
        $where[] = array('vouchertemplate_id','=',$t_id);
        $gettype_arr = $voucher_model->getVoucherGettypeArray();
        $where[] = array('vouchertemplate_gettype','=',$gettype_arr['pwd']['sign']);
        $where[] = array('vouchertemplate_isbuild','=',0);
        $where[] = array('vouchertemplate_state','=',1);
        $t_info = $voucher_model->getVouchertemplateInfo($where);
        $t_total = intval($t_info['vouchertemplate_total']);
        if ($t_total <= 0) {
            return ds_callback(false, '代金券模板信息错误');
        }
        while ($t_total > 0) {
            $is_succ = false;
            $insert_arr = array();
            $step = $t_total > 1000 ? 1000 : $t_total;
            for ($t = 0; $t < $step; $t++) {
                $voucher_code = $voucher_model->getVoucherCode(0);
                if (!$voucher_code) {
                    continue;
                }
                $voucher_pwd_arr = $voucher_model->createVoucherPwd($t_info['vouchertemplate_id']);
                if (!$voucher_pwd_arr) {
                    continue;
                }
                $tmp = array();
                $tmp['voucher_code'] = $voucher_code;
                $tmp['vouchertemplate_id'] = $t_info['vouchertemplate_id'];
                $tmp['voucher_title'] = $t_info['vouchertemplate_title'];
                $tmp['voucher_desc'] = $t_info['vouchertemplate_desc'];
                $tmp['voucher_startdate'] = $t_info['vouchertemplate_startdate'];
                $tmp['voucher_enddate'] = $t_info['vouchertemplate_enddate'];
                $tmp['voucher_price'] = $t_info['vouchertemplate_price'];
                $tmp['voucher_limit'] = $t_info['vouchertemplate_limit'];
                $tmp['voucher_state'] = 1;
                $tmp['voucher_activedate'] = TIMESTAMP;
                $tmp['voucher_owner_id'] = 0;
                $tmp['voucher_owner_name'] = '';
                $tmp['voucher_order_id'] = 0;
                $tmp['voucher_pwd'] = $voucher_pwd_arr[0];//md5
                $tmp['voucher_pwd2'] = $voucher_pwd_arr[1];
                $insert_arr[] = $tmp;
                $t_total--;
            }

            $result = $voucher_model->addVoucherBatch($insert_arr);
            if ($result && $is_succ == false) {
                $is_succ = true;
            }
        }
        //更新代金券模板
        if ($is_succ) {
            $voucher_model->editVouchertemplate(array('vouchertemplate_id' => $t_info['vouchertemplate_id']), array('vouchertemplate_isbuild' => 1));
            return ds_callback(true);
        }
        else {
            return ds_callback(false);
        }
    }

    /**
     * 上架
     *
     * @param array $cron
     */
    public function editProducesOnline($cron = array()){
        $condition = array(array('goods_commonid','in',array_keys($cron)));
        $update = model('goods')->editProducesOnline($condition);
        if ($update){
            //返回执行成功的cronid
            $cronid = array();
            foreach ($cron as $v) {
                $cronid[] = $v['id'];
            }
        }else{
            return false;
        }
        return $cronid;
    }


    /**
     * 抢购开始更新商品促销价格
     *
     * @param int $goods_commonid
     */
    public function editGoodsGroupbuyPrice($goods_commonid) {
        $condition = array();
        $condition[] = array('goods_commonid','=', $goods_commonid);
        $condition[] = array('groupbuy_starttime','<', TIMESTAMP);
        $condition[] = array('groupbuy_endtime','>', TIMESTAMP);
        $groupbuy = model('groupbuy')->getGroupbuyList($condition);
        $update=true;
        foreach ($groupbuy as $val) {
            $flag=model('goods')->editGoods(array('goods_promotion_price' => $val['groupbuy_price'], 'goods_promotion_type' => 1), array('goods_commonid' => $val['goods_commonid']));
            if(!$flag){
                $update=false;
            }
        }
        if (!$update) {
            return ds_callback(false);
        }
        return ds_callback(true);
    }

    /**
     * 抢购过期
     *
     * @param array $cron
     */
    public function editExpireGroupbuy($cron = array()) {
        $condition = array(array('goods_commonid','in', array_keys($cron)));
        //抢购活动过期
        $update = model('groupbuy')->editExpireGroupbuy($condition);
        if ($update){
            //返回执行成功的cronid
            $cronid = array();
            foreach ($cron as $v) {
                $cronid[] = $v['id'];
            }
        }else{
            return false;
        }
        return $cronid;
    }

    /**
     * 秒杀过期
     *
     * @param array $cron
     */
    public function editExpireXianshi($xianshi_id) {
        $condition = array(array('xianshi_id','=', $xianshi_id));
        //秒杀过期
        $update = model('pxianshi')->editExpireXianshi($condition);
        if (!$update) {
            return ds_callback(false);
        }
        return ds_callback(true);
    }
}