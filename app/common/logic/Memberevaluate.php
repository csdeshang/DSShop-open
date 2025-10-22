<?php

namespace app\common\logic;


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
class Memberevaluate {

    public function evaluateListDity($goods_eval_list) {
        foreach ($goods_eval_list as $key => $value) {
            $goods_eval_list[$key]['member_avatar'] = get_member_avatar_for_id($value['geval_frommemberid']);
        }
        return $goods_eval_list;
    }

    /* 查询订单信息 */

    public function validation($order_id, $member_id) {
        $condition[] = array('order_id','=',$order_id);
        $condition[] = array('buyer_id','=',$member_id);
        //获取订单信息
        $order_info = model('order')->getOrderInfo($condition);
        if (empty($order_info)) {
            $info = array('state' => '0', 'msg' => '订单出错');
        }
        $order_info['evaluate_able'] = model('order')->getOrderOperateState('evaluation', $order_info);
        if (empty($order_info) || !$order_info['evaluate_able']) {
            $info = array('state' => '0', 'msg' => '评价订单不存在');
        }
        
        //获取订单商品
        $order_goods = model('order')->getOrdergoodsList($condition);
        if (empty($order_goods)) {
            $info = array('state' => '0', 'msg' => '订单商品不存在');
        }
        foreach ($order_goods as $key => $value) {
            $order_goods[$key]['goods_image_url'] = goods_cthumb($value['goods_image']);
        }


        $info['data'] = array('order_goods' => $order_goods, 'order_info' => $order_info);
        return $info;
    }

    /* 保存订单评价信息 */
    public function saveorderevaluate($order_info, $order_goods, $member_id, $member_name) {
        $evaluate_goods_array = array();
        $goodsid_array = array();
        
        $goods_array = input('post.goods/a');#获取评价数组
        
        foreach ($order_goods as $value) {
            //如果未评分，默认为5分
            $evaluate_score = intval($goods_array[$value['goods_id']]['score']);
            if ($evaluate_score <= 0 || $evaluate_score > 5) {
                $evaluate_score = 5;
            }
            //默认评语
            $evaluate_comment = $goods_array[$value['goods_id']]['comment'];
            if (empty($evaluate_comment)) {
                $evaluate_comment = '不错哦';
            }else{
                $res=word_filter($evaluate_comment);
                if($res['code']){
                    $evaluate_comment=$res['data']['text'];
                }
            }

            $evaluate_goods_info = array();
            $evaluate_goods_info['geval_orderid'] = $order_info['order_id'];
            $evaluate_goods_info['geval_orderno'] = $order_info['order_sn'];
            $evaluate_goods_info['geval_ordergoodsid'] = $value['rec_id'];
            $evaluate_goods_info['geval_goodsid'] = $value['goods_id'];
            $evaluate_goods_info['geval_goodsname'] = $value['goods_name'];
            $evaluate_goods_info['geval_goodsprice'] = $value['goods_price'];
            $evaluate_goods_info['geval_goodsimage'] = $value['goods_image'];
            $evaluate_goods_info['geval_scores'] = $evaluate_score;
            $evaluate_goods_info['geval_content'] = $evaluate_comment;
            $evaluate_goods_info['geval_isanonymous'] = input('post.anony') ? 1 : 0;
            $evaluate_goods_info['geval_addtime'] = TIMESTAMP;
            $evaluate_goods_info['geval_frommemberid'] = $member_id;
            $evaluate_goods_info['geval_frommembername'] = $member_name;

            $evaluate_goods_array[] = $evaluate_goods_info;

            $goodsid_array[] = $value['goods_id'];
        }
        
        
        model('evaluategoods')->addEvaluategoodsArray($evaluate_goods_array, $goodsid_array);


        
        //更新订单信息并记录订单日志
        $state = model('order')->editOrder(array('evaluation_state' => 1), array('order_id' => $order_info['order_id']));
        model('order')->editOrdercommon(array('evaluation_time' => TIMESTAMP), array('order_id' => $order_info['order_id']));
        if ($state) {
            $data = array();
            $data['order_id'] = $order_info['order_id'];
            $data['log_role'] = 'buyer';
            $data['log_user'] = '';
            $data['log_msg'] = lang('order_log_eval');
            model('order')->addOrderlog($data);
            $res = true;
        } else {
            $res = false;
        }

        //添加会员积分
        if (config('ds_config.points_isuse') == 1) {
            $points_model = model('points');
            $points_model->savePointslog('comments', array('pl_memberid' => $member_id, 'pl_membername' => $member_name));
        }
        //添加会员经验值
        model('exppoints')->saveExppointslog('comments', array('explog_memberid' => $member_id, 'explog_membername' => $member_name));
        return $res;
    }

    public function validationVr($order_id, $member_id) {
        $condition[] = array('order_id','=',$order_id);
        $condition[] = array('buyer_id','=',$member_id);
        //获取订单信息
        $order_info = model('vrorder')->getVrorderInfo($condition);
        if (empty($order_info)) {
            $info = array(
                'state' => '0', 'msg' => '没有权限'
            );
        }
        //订单为'已收货'状态，并且未评论
        $order_info['evaluate_able'] = model('vrorder')->getVrorderOperateState('evaluation', $order_info);
        if (!$order_info['evaluate_able']) {
            $info = array(
                'state' => '0', 'msg' => '订单已评价'
            );
        }
        //单个商品
        $order_info['goods_image_url'] = goods_cthumb($order_info['goods_image']);
        $info['data'] = array('order_info' => $order_info);
        return $info;
    }

    public function saveVr($order_info,$order_goods, $member_id, $member_name) {
        $evaluate_goods_array = array();
        $goodsid_array = array();
        $vrorder_model = model('vrorder');
        $evaluategoods_model = model('evaluategoods');
        $goods_array = input('post.goods/a'); #获取数组
        foreach ($order_goods as $value) {
            //如果未评分，默认为5分
            $evaluate_score = intval($goods_array[$value['goods_id']]['score']);
            if ($evaluate_score <= 0 || $evaluate_score > 5) {
                $evaluate_score = 5;
            }
            //默认评语
            $evaluate_comment = $goods_array[$value['goods_id']]['comment'];
            if (empty($evaluate_comment)) {
                $evaluate_comment = '不错哦';
            }

            $evaluate_goods_info = array();
            $evaluate_goods_info['geval_orderid'] = $order_info['order_id'];
            $evaluate_goods_info['geval_orderno'] = $order_info['order_sn'];
            $evaluate_goods_info['geval_ordergoodsid'] = $order_info['order_id'];
            $evaluate_goods_info['geval_goodsid'] = $value['goods_id'];
            $evaluate_goods_info['geval_goodsname'] = $value['goods_name'];
            $evaluate_goods_info['geval_goodsprice'] = $value['goods_price'];
            $evaluate_goods_info['geval_goodsimage'] = $value['goods_image'];
            $evaluate_goods_info['geval_scores'] = $evaluate_score;
            $evaluate_goods_info['geval_content'] = $evaluate_comment;
            $evaluate_goods_info['geval_isanonymous'] = input('post.anony') ? 1 : 0;
            $evaluate_goods_info['geval_addtime'] = TIMESTAMP;
            $evaluate_goods_info['geval_frommemberid'] = $member_id;
            $evaluate_goods_info['geval_frommembername'] = $member_name;

            $evaluate_goods_array[] = $evaluate_goods_info;

            $goodsid_array[] = $value['goods_id'];
        }
        $evaluategoods_model->addEvaluategoodsArray($evaluate_goods_array, $goodsid_array);
        
        //更新订单信息并记录订单日志
        $state = $vrorder_model->editVrorder(array('evaluation_state' => 1, 'evaluation_time' => TIMESTAMP), array('order_id' => $order_info['order_id']));
        //添加会员积分
        if (config('ds_config.points_isuse') == 1) {
            $points_model = model('points');
            $points_model->savePointslog('comments', array('pl_memberid' => $member_id, 'pl_membername' => $member_name));
        }
        //添加会员经验值
        model('exppoints')->saveExppointslog('comments', array('explog_memberid' => $member_id, 'explog_membername' => $member_name));
        return $state;
    }

}
