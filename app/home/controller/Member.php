<?php

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
class Member extends BaseMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/member.lang.php');
    }

    public function index() {
        //获取用户账号信息
        $member_info = $this->member_info;
        $member_info['security_level'] = model('member')->getMemberSecurityLevel($member_info);

        //代金券数量
        $member_info['voucher_count'] = model('voucher')->getCurrentAvailableVoucherCount(session('member_id'));
        View::assign('home_member_info', $member_info);
        //获取订单信息
        $order_list=array();
        $order_model = model('order');
        $refundreturn_model = model('refundreturn');
        $order_list['order_nopay_count'] = $order_model->getOrderCountByID(session('member_id'), 'NewCount');
        $order_list['order_noreceipt_count'] = $order_model->getOrderCountByID(session('member_id'), 'SendCount');
        $order_list['order_noeval_count'] = $order_model->getOrderCountByID(session('member_id'), 'EvalCount');
        $order_list['order_noship_count'] = $order_model->getOrderCountByID(session('member_id'), 'PayCount');
        $order_list['order_refund_count'] = $refundreturn_model->getRefundreturnCount(array(array('buyer_id','=',session('member_id')),array('refund_state','<>',3)));
        
        View::assign('home_order_info', $order_list);
        
        
        
        
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu();
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('my_album');
        return View::fetch($this->template_dir . 'index');
    }

    public function ajax_load_order_info() {
        //取出购物车信息
        $cart_model = model('cart');
        $cart_list = $cart_model->getCartList('db', array('buyer_id' => session('member_id')), 12);
        View::assign('cart_list', $cart_list);
        return View::fetch($this->template_dir . 'order_info');
    }
    public function ajax_load_point_info(){
                //开启代金券功能后查询推荐的热门代金券列表
        if (config('ds_config.voucher_allow') == 1){
            $recommend_voucher = model('voucher')->getRecommendTemplate(2);
            View::assign('recommend_voucher',$recommend_voucher);
        }
        //开启积分兑换功能后查询推荐的热门兑换商品列表
        if (config('ds_config.pointprod_isuse') == 1){
            //热门积分兑换商品
            $recommend_pointsprod = model('pointprod')->getRecommendPointProd(2);
            View::assign('recommend_pointsprod',$recommend_pointsprod);
        }
        return View::fetch($this->template_dir . 'point_info');
    }
    public function ajax_load_goods_info() {
        //商品收藏
        $favorites_model = model('favorites');
        $favorites_list = $favorites_model->getGoodsFavoritesList(array('member_id' => session('member_id')), '*', 7);
        if (!empty($favorites_list) && is_array($favorites_list)) {
            $favorites_id = array(); //收藏的商品编号
            foreach ($favorites_list as $key => $favorites) {
                $fav_id = $favorites['fav_id'];
                $favorites_id[] = $favorites['fav_id'];
                $favorites_key[$fav_id] = $key;
            }
            $goods_model = model('goods');
            $field = 'goods_id,goods_name,goods_image,goods_price,evaluation_count,goods_salenum,goods_collect';
            $goods_list = $goods_model->getGoodsList(array(array('goods_id','in', $favorites_id)), $field);
            if (!empty($goods_list) && is_array($goods_list)) {
                foreach ($goods_list as $key => $fav) {
                    $fav_id = $fav['goods_id'];
                    //$fav['goods_member_id'] = $fav['member_id'];
                    $key = $favorites_key[$fav_id];
                    $favorites_list[$key]['goods'] = $fav;
                }
            }
        }
        View::assign('favorites_list', $favorites_list);

        $goods_count_new = array();
        if (!empty($favorites_id)) {
            foreach ($favorites_id as $v) {
                $count = model('goods')->getGoodsCommonOnlineCount(array());
                $goods_count_new[$v] = $count;
            }
        }
        View::assign('goods_count', $goods_count_new);
        return View::fetch($this->template_dir . 'goods_info');
    }
    public function ajax_load_sns_info() {
        //我的足迹
        $goods_list = model('goodsbrowse')->getViewedGoodsList(session('member_id'), 20);
        $viewed_goods = array();
        if (is_array($goods_list) && !empty($goods_list)) {
            foreach ($goods_list as $key => $val) {
                $goods_id = $val['goods_id'];
                $val['url'] = url('Goods/index',['goods_id'=>$goods_id]);
                $val['goods_image'] = goods_thumb($val, 240);
                $viewed_goods[$goods_id] = $val;
            }
        }
        View::assign('viewed_goods', $viewed_goods);
        return View::fetch($this->template_dir . 'sns_info');
    }
}

?>
