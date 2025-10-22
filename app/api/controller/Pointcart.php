<?php

namespace app\api\controller;

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
 * 积分兑换购物车控制器
 */
class Pointcart extends MobileMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path().'home/lang/'.config('lang.default_lang').'/pointcart.lang.php');
        //判断系统是否开启积分和积分兑换功能
        if (config('ds_config.pointprod_isuse') != 1) {
            ds_json_encode(10001,'未开启积分兑换功能');
        }
    }
    
    /**
     * @api {POST} api/Pointcart/cart_list 购物车礼品列表
     * @apiVersion 3.0.6
     * @apiGroup Pointcart
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Int} result.pgoods_pointall  所需总积分 （返回字段参考pointscart表）
     * @apiSuccess {Object[]} result.cart_array  返回数据
     */
    public function cart_list() {
        $cart_goods	= array();
        $pointcart_model = model('pointcart');
        $data = $pointcart_model->getPCartListAndAmount(array('pmember_id'=>$this->member_info['member_id']));
        ds_json_encode(10000, lang('ds_common_op_succ'),array('pgoods_pointall' => $data['data']['cartgoods_pointall'],'cart_array'=>$data['data']['cartgoods_list']));
        return View::fetch($this->template_dir.'pointcart_list');
    }
    

    /**
     * @api {POST} api/Pointcart/cart_del 购物车删除
     * @apiVersion 3.0.6
     * @apiGroup Pointcart
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {String} pcart_id 购物车主键ID  例3,5,8
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function cart_del() {
        $pcart_id	= intval(input('param.pcart_id'));
        if($pcart_id <= 0) {
            ds_json_encode(10001,lang('ds_common_del_fail'));
        }
        $pointcart_model = model('pointcart');
        $drop_state	= $pointcart_model->delPointcartById($pcart_id,$this->member_info['member_id']);
        if ($drop_state){
            ds_json_encode(10000,'');
        } else {
            ds_json_encode(10001,lang('ds_common_del_fail'));
        }
    }

    /**
     * @api {POST} api/Pointcart/cart_edit_quantity 更新购物车购买数量
     * @apiVersion 3.0.6
     * @apiGroup Pointcart
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {Int} pcart_id 购物车主键ID  
     * @apiParam {Int} quantity 修改数量
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Int} result.subtotal  小计
     * @apiSuccess {Int} result.amount  总积分
     * @apiSuccess {Int} result.quantity  积分商品数量
     */
    public function cart_edit_quantity() {
        $pcart_id	= intval(input('param.pcart_id'));
        $quantity	= intval(input('param.quantity'));
        //兑换失败提示
        $msg = lang('pointcart_cart_modcart_fail');

        if($pcart_id <= 0 || $quantity <= 0) {
            ds_json_encode(10001,$msg);
          
        }
        //验证礼品购物车信息是否存在
        $pointcart_model	= model('pointcart');
        $cart_info	= $pointcart_model->getPointcartInfo(array('pcart_id'=>$pcart_id,'pmember_id'=>$this->member_info['member_id']));
        if (!$cart_info){
            ds_json_encode(10001,$msg); 
        }

        //验证是否能兑换
        $data = $pointcart_model->checkExchange($cart_info['pgoods_id'], $quantity, $this->member_info['member_id']);
        if (!$data['state']){
            ds_json_encode(10001,$data['msg']); 
        }
        $prod_info = $data['data']['prod_info'];
        $quantity = $prod_info['quantity'];

        $cart_state = true;
        //如果数量发生变化则更新礼品购物车内单个礼品数量
        if ($cart_info['pgoods_choosenum'] != $quantity){
            $cart_state = $pointcart_model->editPointcart(array('pcart_id'=>$pcart_id,'pmember_id'=>$this->member_info['member_id']),array('pgoods_choosenum'=>$quantity));
        }
        if ($cart_state) {
            //计算总金额
            $amount= $pointcart_model->getPointcartAmount($this->member_info['member_id']);
            ds_json_encode(10000,'',array('subtotal'=>$prod_info['pointsamount'],'amount'=>$amount,'quantity'=>$quantity));
            
        }
    }

    
    /**
     * @api {POST} api/Pointcart/add 购物车添加礼品
     * @apiVersion 3.0.6
     * @apiGroup Pointcart
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {String} pgid 积分商品id
     * @apiParam {String} quantity 数量
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function add() {
        $pgid = intval(input('post.pgid'));
        $quantity = intval(input('post.quantity'));
        if ($pgid <= 0 || $quantity <= 0) {
            ds_json_encode(10001,'参数错误!!');
        }

        
        $pointcart_model = model('pointcart');
        //$pointcart_model->delPointcart(array('pmember_id' => $this->member_info['member_id']));
        
        //验证是否能兑换
        $data = $pointcart_model->checkExchange($pgid, $quantity, $this->member_info['member_id']);
        if (!$data['state']) {
            ds_json_encode(10001,$data['msg']);
        }
        //验证积分礼品是否存在购物车中
        $check_cart = $pointcart_model->getPointcartInfo(array('pgoods_id' => $pgid, 'pmember_id' => $this->member_info['member_id']));
        if (!empty($check_cart)) {
          $cart_state = $pointcart_model->editPointcart(array('pcart_id'=>$check_cart['pcart_id'],'pmember_id'=>$this->member_info['member_id']),array('pgoods_choosenum'=>$quantity));
            ds_json_encode(10000, lang('ds_common_op_succ'),array('done' => 'ok1'));
        }
        $prod_info = $data['data']['prod_info'];

        $insert_arr = array();
        $insert_arr['pmember_id'] = $this->member_info['member_id'];
        $insert_arr['pgoods_id'] = $prod_info['pgoods_id'];
        $insert_arr['pgoods_name'] = $prod_info['pgoods_name'];
        $insert_arr['pgoods_points'] = $prod_info['pgoods_points'];
        $insert_arr['pgoods_choosenum'] = $prod_info['quantity'];
        $insert_arr['pgoods_image'] = $prod_info['pgoods_image_old'];
        $cart_state = $pointcart_model->addPointcart($insert_arr);
        if($cart_state){
            ds_json_encode(10000, lang('ds_common_op_succ'),array('done' => 'ok'));
        }else{
            ds_json_encode(10001,$data['msg']);
        }
    }


    /**
     * @api {POST} api/Pointcart/step1 兑换订单流程第一步
     * @apiVersion 3.0.6
     * @apiGroup Pointcart
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {String} cart_id 购买信息 商品id|数量
     * @apiParam {String} ifcart 是否从购物车获取商品 0否1是
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object} result.pointprod_arr 积分购物车信息
     * @apiSuccess {Object} result.pointprod_arr.pgoods_pointall  所需总积分
     * @apiSuccess {Object[]} result.pointprod_arr.pointprod_list  积分商品列表 （返回字段参考pointsgoods表）
     * @apiSuccess {Int} result.pointprod_arr.pointprod_list.quantity 购买数量  
     * @apiSuccess {Int} result.pointprod_arr.pointprod_list.onepoints 所需积分
     * @apiSuccess {Int} result.pointprod_arr.pointprod_list.pgoods_limitgradename 所需用户等级
     * @apiSuccess {String} result.pointprod_arr.pointprod_list.ex_state 兑换状态代码 end不可兑换willbe即将开始going进行中
     * @apiSuccess {Object} result.address_info  用户收货地址信息 （返回字段参考address表）
     */
    public function step1() {
        //获取符合条件的兑换礼品和总积分
        $data = model('pointcart')->getCartGoodsList($this->member_info['member_id'],input('post.')); 
        if (!$data['state']) {
            ds_json_encode(10001,$data['msg']);
        }

        //实例化收货地址模型（不显示自提点地址）
        $address_list = model('address')->getAddressList(array('member_id' => $this->member_info['member_id']), 'address_is_default desc,address_id desc');

        //收货地址为空 返回兑换里面信息
        ds_json_encode(10000, '', array('pointprod_arr' => $data['data'], 'address_info' => $address_list?$address_list[0]:false));
    }


    /**
     * @api {POST} api/Pointcart/step2 兑换订单流程第二步
     * @apiVersion 3.0.6
     * @apiGroup Pointcart
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {String} cart_id 购买信息 商品id|数量
     * @apiParam {String} ifcart 是否从购物车获取商品 0否1是
     * @apiParam {String} address_options 地址id
     * @apiParam {String} pcart_message 用户备注
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Int} result.pointprod_arr  订单ID
     */
    public function step2() {
        $pointcart_model = model('pointcart');
        //获取符合条件的兑换礼品和总积分
        $data = $pointcart_model->getCartGoodsList($this->member_info['member_id'],input('post.'));
        if (!$data['state']) {
            ds_json_encode(10001,$data['msg']);
        }
        $pointprod_arr = $data['data'];
        unset($data);

        //验证积分数是否足够
        $data = $pointcart_model->checkPointEnough($pointprod_arr['pgoods_pointall'], $this->member_info['member_id']);
        if (!$data['state']) {
            ds_json_encode(10001,$data['msg']);
        }
        unset($data);

        //创建兑换订单
        $data = model('pointorder')->createOrder(input('post.'), $pointprod_arr, array('member_id' => $this->member_info['member_id'], 'member_name' => $this->member_info['member_name'], 'member_email' => $this->member_info['member_email']));
        if (!$data['state']) {
            ds_json_encode(10001,$data['msg']);
        }
        $order_id = $data['data']['order_id'];
        
        ds_json_encode(10000, '',array('pointprod_arr' => $order_id));
    }

    /**
     * @api {POST} api/Pointcart/cart_count 检查购物车数量
     * @apiVersion 3.0.6
     * @apiGroup Pointcart
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Int} result.cart_count  购物车数量
     */
    public function cart_count() {
        $pointcart_model = model('pointcart');
        $count = $pointcart_model->getPointcartCount($this->member_info['member_id']);
        $data['cart_count'] = $count;
        ds_json_encode(10000,'',$data);
    }
}

?>
