<?php

namespace app\api\controller;
use think\facade\Db;
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
 * 商品控制器
 */
class Goods extends MobileMall {

    private $PI = 3.14159265358979324;
    private $x_pi = 0;

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/goods.lang.php');
        $this->x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    }

    /**
     * @api {POST} api/Goods/goods_list 商品列表
     * @apiVersion 3.0.6
     * @apiGroup Goods
     *
     * @apiParam {Int} cate_id 分类ID
     * @apiParam {String} keyword 关键词
     * @apiParam {String} b_id 品牌id
     * @apiParam {Float} price_from 价格从
     * @apiParam {Float} price_to 价格到
     * @apiParam {Int} sort_key 排序键 goods_salenum销量 goods_click浏览量 goods_price价格
     * @apiParam {Int} sort_order 排序值 1升序 2降序
     * @apiParam {Int} gift 是否有赠品 1有
     * @apiParam {Int} own_shop 自营 1是
     * @apiParam {Int} area_id 地区id
     * @apiParam {Int} xianshi 是否秒杀 1是
     * @apiParam {Int} page 页码
     * @apiParam {Int} per_page 每页显示数量
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.goods_list  商品列表
     * @apiSuccess {Int} result.goods_list.evaluation_count  评论数
     * @apiSuccess {Float} result.goods_list.evaluation_good_star  评分
     * @apiSuccess {String} result.goods_list.goods_advword  广告词
     * @apiSuccess {Int} result.goods_list.goods_id  商品ID
     * @apiSuccess {String} result.goods_list.goods_image  商品图片名称
     * @apiSuccess {String} result.goods_list.goods_image_url  商品图片完整路径
     * @apiSuccess {Float} result.goods_list.goods_marketprice  商品市场价
     * @apiSuccess {String} result.goods_list.goods_name  商品名称
     * @apiSuccess {Float} result.goods_list.goods_price  商品价格
     * @apiSuccess {Float} result.goods_list.goods_promotion_price  商品促销价
     * @apiSuccess {String} result.goods_list.goods_promotion_type  促销类型
     * @apiSuccess {Int} result.goods_list.goods_salenum  商品销售量
     * @apiSuccess {Boolean} result.goods_list.group_flag  是否抢购 true是false否
     * @apiSuccess {Int} result.goods_list.is_goodsfcode  是否F码 1是0否
     * @apiSuccess {Int} result.goods_list.is_have_gift  是否含赠品 1是0否
     * @apiSuccess {Int} result.goods_list.is_presell  是否预售 1是0否
     * @apiSuccess {Int} result.goods_list.is_virtual  是否虚拟商品 1是0否
     * @apiSuccess {Boolean} result.goods_list.xianshi_flag  是否秒杀 true是false否
     * @apiSuccess {Int} result.page_total  总页数
     * @apiSuccess {Boolean} result.hasmore  是否有更多 true是false否
     */
    public function goods_list() {
        $goods_model = model('goods');
        $search_model = model('search');

        //查询条件
        $condition = array();
        $cate_id = $default_classid = intval(input('param.cate_id'));
        $keyword = input('param.keyword');
        $b_id = intval(input('param.b_id'));
        
        //获得经过属性过滤的商品信息 
        $this->_model_search = model('search');
        list($goods_param, $brand_array, $initial_array, $attr_array, $checked_brand, $checked_attr) = $this->_model_search->getAttribute(input('param.'), $default_classid);
        if (isset($goods_param['class']['depth'])) {
            $condition[] = array('goodscommon.gc_id_' . $goods_param['class']['depth'], '=', $goods_param['class']['gc_id']);
        }
        if (isset($goods_param['goodsid_array'])) {
            $condition[] = array('goods.goods_id', 'in', $goods_param['goodsid_array']);
        }
        if ($cate_id > 0) {
            $condition=$goods_model->_getRecursiveClass($condition,$cate_id,'goodscommon');
        }
        if (!empty($keyword)) {
            $condition[] = array('goodscommon.goods_name|goodscommon.goods_advword', 'like', '%' . $keyword . '%');
            if (cookie('hisSearch') == '') {
                $his_sh_list = array();
            } else {
                $his_sh_list = explode('~', cookie('hisSearch'));
            }
            if (strlen($keyword) <= 20 && !in_array($keyword, $his_sh_list)) {
                if (array_unshift($his_sh_list, $keyword) > 8) {
                    array_pop($his_sh_list);
                }
            }
            cookie('hisSearch', implode('~', $his_sh_list), 2592000);
        }
        if($b_id > 0){
            $condition[] = array('goodscommon.brand_id', '=', $b_id);
        }
        
        $price_from = input('param.price_from');
        $price_to = input('param.price_to');
        $price_from = preg_match('/^[\d.]{1,20}$/', $price_from) ? $price_from : null;
        $price_to = preg_match('/^[\d.]{1,20}$/', $price_to) ? $price_to : null;

        //所需字段
        $fieldstr = "goods.goods_id,goods.goods_storage,goodscommon.goods_commonid,goodscommon.goods_name,goodscommon.goods_advword,goodscommon.goods_price,goods.goods_promotion_price,goods.goods_promotion_type,goodscommon.goods_marketprice,goodscommon.goods_image,goods.goods_salenum,goods.evaluation_good_star,goods.evaluation_count";

        $fieldstr .= ',goodscommon.is_virtual,goodscommon.is_presell,goodscommon.is_goodsfcode,goods.is_have_gift,goodscommon.goods_advword';

        //排序方式
        $order = $this->_goods_list_order(input('param.sort_key'), input('param.sort_order'));


            if ($price_from && $price_to) {
                $condition[] = array('goods.goods_promotion_price', 'between', "{$price_from},{$price_to}");
            } elseif ($price_from) {
                $condition[] = array('goods.goods_promotion_price', '>=', $price_from);
            } elseif ($price_to) {
                $condition[] = array('goods.goods_promotion_price', '<=', $price_to);
            }
            if (input('param.gift') == 1) {
                $condition[] = array('goods.is_have_gift', '=', 1);
            }
     
            if (intval(input('param.area_id')) > 0) {
                $condition[] = array('goodscommon.areaid_1', '=', intval(input('param.area_id')));
            }

            //抢购和秒杀搜索
            $_tmp = array();
            if (input('param.groupbuy') == 1) {
                $_tmp[] = 1;
            }
            if (input('param.xianshi') == 1) {
                $_tmp[] = 2;
                if(input('param.goods_price')=='goods_price'){
                    $order='goods_promotion_price';
                    if (input('param.sort_order') == 'asc') {
                        $order .= ' asc';
                    }else{
                        $order .= ' desc';
                    }
                }
            }
            if ($_tmp) {
                $condition[] = array('goods.goods_promotion_type', 'in', $_tmp);
            }
            unset($_tmp);

            //虚拟商品
            if (input('param.virtual') == 1) {
                $condition[] = array('goodscommon.is_virtual', '=', 1);
            }

            $goods_list = $goods_model->getGoodsUnionList($condition, $fieldstr, $order,'goodscommon.goods_commonid', $this->pagesize);
//        }
        //处理商品列表(抢购、秒杀、商品图片)
        $goods_list = $this->_goods_list_extend($goods_list);
        $result = array_merge(array('goods_list' => $goods_list), mobile_page(is_object($goods_model->page_info)?$goods_model->page_info:''));
        ds_json_encode(10000, '',$result);
    }
    
    /**
     * @api {POST} api/Goods/get_attribute 获取分类下的属性
     * @apiVersion 3.0.6
     * @apiGroup Goods
     *
     * @apiParam {Int} cate_id 分类ID
     * @apiParam {String} a_id 已选择的属性
     * @apiParam {Int} b_id 已选择的品牌
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object} result.goods_param 商品数据集
     * @apiSuccess {Object} result.brand_array 品牌列表
     * @apiSuccess {Object} result.attr_array 属性列表
     * @apiSuccess {Object} result.checked_brand 已选择的品牌
     * @apiSuccess {Object} result.checked_attr 已选择的属性
     */
    public function get_attribute()
    {
        $this->_model_search = model('search');
        $default_classid = intval(input('param.cate_id'));
        //获得经过属性过滤的商品信息 
        list($goods_param, $brand_array, $initial_array, $attr_array, $checked_brand, $checked_attr) = $this->_model_search->getAttribute(input('param.'), $default_classid);
        $result = array(
            'goods_param'=>$goods_param,
            'brand_array'=>$brand_array,
            'initial_array'=>$initial_array,
            'attr_array'=>$attr_array,
            'checked_brand'=>$checked_brand,
            'checked_attr'=>$checked_attr,
        );
        ds_json_encode(10000, '',$result);
    }

    /**
     * @api {POST} api/Goods/get_bundling 优惠套装
     * @apiVersion 3.0.6
     * @apiGroup Goods
     *
     * @apiParam {Int} goods_id 商品ID
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object} result.bundling_array 优惠套餐分类列表，键为分类ID
     * @apiSuccess {Float} result.bundling_array.freight 邮费
     * @apiSuccess {Int} result.bundling_array.id 优惠套餐分类ID
     * @apiSuccess {String} result.bundling_array.name 优惠套餐分类名称
     * @apiSuccess {Float} result.bundling_array.price 优惠套餐价
     * @apiSuccess {Object} result.b_goods_array  优惠套餐商品列表，键为分类ID
     * @apiSuccess {Int} result.b_goods_array.id 优惠套餐分类ID
     * @apiSuccess {String} result.b_goods_array.image 商品图片
     * @apiSuccess {String} result.b_goods_array.name 商品名称
     * @apiSuccess {Float} result.b_goods_array.price 优惠后价格
     * @apiSuccess {Float} result.b_goods_array.shop_price 原价
     */
    public function get_bundling() {
        $goods_id = intval(input('param.goods_id'));
        if ($goods_id <= 0) {
            ds_json_encode(10001,lang('param_error'));
        }


        // 优惠套装
        $array = model('pbundling')->getBundlingCacheByGoodsId($goods_id);
        if (!empty($array)) {
            $bundling_array=unserialize($array['bundling_array']);
            $b_goods_array=unserialize($array['b_goods_array']);
            ds_json_encode(10000, '',array('bundling_array'=>!empty($bundling_array)?$bundling_array:false,'b_goods_array'=>!empty($b_goods_array)?$b_goods_array:false));
        }else{
            ds_json_encode(10001,'没有优惠套装');
        }

    }
    /**
     * 商品列表排序方式
     */
    private function _goods_list_order($sort_key, $sort_order) {
        $result = 'goodscommon.goods_commend desc,goodscommon.goods_sort asc';
        if (!empty($sort_key)) {

            $sequence = 'desc';
            if ($sort_order == 'asc') {
                $sequence = 'asc';
            }

            switch ($sort_key) {
                //销量
                case 'goods_salenum' :
                    $result = 'goods.goods_salenum' . ' ' . $sequence;
                    break;
                //浏览量
                case 'goods_click' :
                    $result = 'goods.goods_click' . ' ' . $sequence;
                    break;
                //价格
                case 'goods_price' :
                    $result = 'goodscommon.goods_price' . ' ' . $sequence;
                    break;
                //新品
                case 'goods_addtime' :
                    $result = 'goodscommon.goods_addtime' . ' ' . $sequence;
                    break;
            }
        }
        return $result;
    }

    private function _goods_list_extend($goods_list) {
        //获取商品列表编号数组
        $commonid_array = array();
        $goodsid_array = array();
        $goods_model=model('goods');
        foreach ($goods_list as $key => $value) {
            if(!$value['goods_storage']){
                    $goods_info=$goods_model->getGoodsStorageByCommonId($value['goods_commonid']);
                    if($goods_info){
                        $goods_list[$key]['goods_id']=$value['goods_id']=$goods_info['goods_id'];
                        $goods_list[$key]['goods_promotion_price']=$goods_info['goods_promotion_price'];
                    }
                }
            $commonid_array[] = $value['goods_commonid'];
            $goodsid_array[] = $value['goods_id'];
        }

        //促销
        $groupbuy_list = model('groupbuy')->getGroupbuyListByGoodsCommonIDString(implode(',', $commonid_array));
        $xianshi_list = model('pxianshigoods')->getXianshigoodsListByGoodsString(implode(',', $goodsid_array));
        foreach ($goods_list as $key => $value) {
            //抢购
            if (isset($groupbuy_list[$value['goods_commonid']])) {
                $goods_list[$key]['goods_price'] = $groupbuy_list[$value['goods_commonid']]['groupbuy_price'];
                $goods_list[$key]['group_flag'] = true;
            } else {
                $goods_list[$key]['group_flag'] = false;
            }

            //秒杀
            if (isset($xianshi_list[$value['goods_id']]) && !$goods_list[$key]['group_flag']) {
                $goods_list[$key]['goods_price'] = $xianshi_list[$value['goods_id']]['xianshigoods_price'];
                $goods_list[$key]['xianshi_flag'] = true;
            } else {
                $goods_list[$key]['xianshi_flag'] = false;
            }

            //商品图片url
            $goods_list[$key]['goods_image_url'] = goods_cthumb($value['goods_image'], 480);


            unset($goods_list[$key]['goods_commonid']);
            unset($goods_list[$key]['nc_distinct']);
        }

        return $goods_list;
    }


    /**
     * @api {POST} api/Goods/goods_detail 商品详细页
     * @apiVersion 3.0.6
     * @apiGroup Goods
     *
     * @apiParam {Int} goods_id 商品ID
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object} result.consult_type 咨询类型列表，键为咨询类型ID
     * @apiSuccess {Int} result.consult_type.consulttype_id 咨询类型ID
     * @apiSuccess {Object} result.consult_type.consulttype_introduce 咨询介绍
     * @apiSuccess {Object} result.consult_type.consulttype_name 咨询类型标题
     * @apiSuccess {Int} result.consult_type.consulttype_sort 咨询类型排序
     * @apiSuccess {Object[]} result.gift_array 赠品列表
     * @apiSuccess {Int} result.gift_array.gift_id 赠品ID
     * @apiSuccess {Int} result.gift_array.gift_goodsid 赠品商品ID
     * @apiSuccess {Object} result.gift_array.gift_goodsname 主商品名称
     * @apiSuccess {Object} result.gift_array.gift_goodsimage 主商品图片名称
     * @apiSuccess {Object} result.gift_array.gift_goodsimage_url  主商品图片完整路径
     * @apiSuccess {Object} result.gift_array.gift_amount 赠品数量
     * @apiSuccess {Int} result.gift_array.goods_id 主商品ID
     * @apiSuccess {Int} result.gift_array.goods_commonid 主商品公共ID
     * @apiSuccess {Object[]} result.goods_commend_list 推荐商品列表
     * @apiSuccess {Int} result.goods_commend_list.goods_id 商品ID
     * @apiSuccess {Object} result.goods_commend_list.goods_image_url 商品图片
     * @apiSuccess {Object} result.goods_commend_list.goods_name 商品名称
     * @apiSuccess {Float} result.goods_commend_list.goods_price 商品价格
     * @apiSuccess {Float} result.goods_commend_list.goods_promotion_price 商品促销价
     * @apiSuccess {Object[]} result.goods_eval_list 商品评论列表（返回字段参考evaluategoods）
     * @apiSuccess {Object} result.goods_evaluate_info 商品评论综合信息
     * @apiSuccess {Int} result.goods_evaluate_info.all 商品评论总数
     * @apiSuccess {Int} result.goods_evaluate_info.bad 差评数
     * @apiSuccess {Int} result.goods_evaluate_info.bad_percent 差评率
     * @apiSuccess {Int} result.goods_evaluate_info.good 好评数
     * @apiSuccess {Int} result.goods_evaluate_info.good_percent 好评率
     * @apiSuccess {Int} result.goods_evaluate_info.good_star 好评评分
     * @apiSuccess {Int} result.goods_evaluate_info.normal 中评数
     * @apiSuccess {Int} result.goods_evaluate_info.normal_percent 中评率
     * @apiSuccess {Int} result.goods_evaluate_info.star_average 平均评分
     * @apiSuccess {String} result.goods_image 商品图片，用逗号分隔
     * @apiSuccess {Object} result.goods_info 商品信息（返回字段参考goods）
     * @apiSuccess {Object[]} result.mb_body 商品详情列表
     * @apiSuccess {String} result.mb_body.type 详情类型 text文字image图片
     * @apiSuccess {String} result.mb_body.value 详情值
     * @apiSuccess {Float} result.inviter_amount 分销佣金
     * @apiSuccess {Boolean} result.is_favorate 是否已收藏 true是false否
     * @apiSuccess {Object} result.spec_image 规格图片列表，键为规格ID，值为规格图片完整路径
     * @apiSuccess {Object} result.spec_list 规格商品ID列表，键为规格ID，值为商品ID
     * @apiSuccess {Object[]} result.voucher 优惠券列表
     * @apiSuccess {String} result.voucher.vouchertemplate_enddate 优惠券过期时间描述
     * @apiSuccess {Int} result.voucher.vouchertemplate_id 优惠券模板ID
     * @apiSuccess {Float} result.voucher.vouchertemplate_limit 优惠券最低消费金额
     * @apiSuccess {Int} result.voucher.vouchertemplate_price 优惠金额
     */
    public function goods_detail() {
        $goods_id = intval(input('param.goods_id'));
        $area_id = intval(input('param.area_id'));
        // 商品详细信息
        $goods_model = model('goods');
        $goods_detail = $goods_model->getGoodsDetail($goods_id);
        //halt($goods_detail);
        if (empty($goods_detail)) {
            ds_json_encode(10001,'商品不存在');
        }
        foreach($goods_detail['gift_array'] as $k => $v){
            $goods_detail['gift_array'][$k]['gift_goodsimage_url']=goods_cthumb($v['gift_goodsimage'], '240');
        }
        //$goods_list = $goods_model->getGoodsContract(array(0=>$goods_detail['goods_info']));
        //$goods_detail['goods_info'] = $goods_list[0];
        //推荐商品
        $hot_sales = $goods_model->getGoodsCommendList(6);
        $goodsid_array = array();
        foreach ($hot_sales as $value) {
            $goodsid_array[] = $value['goods_id'];
        }
        $goods_commend_list = array();
        foreach ($hot_sales as $value) {
            $goods_commend = array();
            $goods_commend['goods_id'] = $value['goods_id'];
            $goods_commend['goods_name'] = $value['goods_name'];
            $goods_commend['goods_price'] = $value['goods_price'];
            $goods_commend['goods_promotion_price'] = $value['goods_promotion_price'];
            $goods_commend['goods_img_480'] = goods_cthumb($value['goods_image'], 240);
            $goods_commend['goods_salenum'] = $value['goods_salenum'];
            $goods_commend_list[] = $goods_commend;
        }

        $goods_detail['goods_commend_list'] = $goods_commend_list;
        

        //商品详细信息处理
        $goods_detail = $this->_goods_detail_extend($goods_detail);
        
        $goods_common_info = $goods_model->getGoodsCommonInfoByID($goods_detail['goods_info']['goods_commonid']);
        $goods_detail['mb_body']=array();
        if ($goods_common_info['mobile_body'] != '') {
            $goods_detail['mb_body'] = unserialize($goods_common_info['mobile_body']);
        }
        // 如果已登录 判断该商品是否已被收藏&&添加浏览记录
        if ($member_id = $this->getMemberIdIfExists()) {
            $c = (int) model('favorites')->getGoodsFavoritesCountByGoodsId($goods_id, $member_id);
            $goods_detail['is_favorate'] = $c > 0;
            model('goodsbrowse')->addViewedGoods($goods_id, $member_id);

            if(isset($goods_detail['goods_info']['pintuan_type']) && $goods_detail['goods_info']['pintuan_type']){
              //不可以重复参加
            $order_id_list=Db::name('ppintuanorder')->where(array(array('pintuan_id','=',$goods_detail['goods_info']['pintuan_id']),array('pintuanorder_state','<>',0)))->column('order_id');
            if ($order_id_list) {
                    if (!$goods_detail['goods_info']['is_virtual']) {
                        if ($order_id=Db::name('order')->where('buyer_id',$member_id)->where('order_id','in',$order_id_list)->value('order_id')) {
                            $condition=array();
                            $condition[]=array('pintuan_id' ,'=', $goods_detail['goods_info']['pintuan_id']);
                            $condition[]=array('order_id' ,'=', $order_id);
                            $condition[]=array('pintuanorder_state','<>', 0);
                            $goods_detail['goods_info']['pintuanorder_state']=Db::name('ppintuanorder')->where($condition)->value('pintuanorder_state');
                        }
                    } else {
                        if ($order_id=Db::name('vrorder')->where('buyer_id',$member_id)->where('order_id','in',$order_id_list)->value('order_id')) {
                            $condition=array();
                            $condition[]=array('pintuan_id' ,'=', $goods_detail['goods_info']['pintuan_id']);
                            $condition[]=array('order_id' ,'=', $order_id);
                            $condition[]=array('pintuanorder_state','<>', 0);
                            $goods_detail['goods_info']['pintuanorder_state']=Db::name('ppintuanorder')->where($condition)->value('pintuanorder_state');
                        }
                    }
                }
            }
        }

            // 优惠券
            $condition = array();
            $condition[] = array('vouchertemplate_state', '=', 1);
            $condition[] = array('vouchertemplate_enddate', '>', TIMESTAMP);
            $voucher_template = model('voucher')->getVouchertemplateList($condition);
            if (!empty($voucher_template)) {
                foreach ($voucher_template as $val) {
                    $param = array();
                    $param['vouchertemplate_id'] = $val['vouchertemplate_id'];
                    $param['vouchertemplate_price'] = $val['vouchertemplate_price'];
                    $param['vouchertemplate_points'] = $val['vouchertemplate_points'];
                    $param['vouchertemplate_limit'] = $val['vouchertemplate_limit'];
                    $param['vouchertemplate_enddate'] = $val['vouchertemplate_enddate'];
                    $goods_detail['voucher'][] = $param;
                }
            }
        // 评价列表
        $goods_eval_list = model('evaluategoods')->getEvaluategoodsList(array('geval_goodsid' => $goods_id),'3');
        //$goods_eval_list = model('memberevaluate','logic')->evaluateListDity($goods_eval_list);
        $goods_detail['goods_eval_list'] = $goods_eval_list;

        //评价信息
        $goods_evaluate_info = model('evaluategoods')->getEvaluategoodsInfoByGoodsID($goods_id);
        $goods_detail['goods_evaluate_info'] = $goods_evaluate_info;

        $goods_detail['goods_hair_info'] = $this->_calc(0, $goods_id);
        
        $goods_detail['goods_info']['pintuangroup_share_id'] = intval(input('param.pintuangroup_share_id'));#获取分享拼团的用户ID
        $inviter_model=model('inviter');
        $goods_detail['inviter_money']=0;
        if(config('ds_config.inviter_show') && config('ds_config.inviter_open') && $goods_detail['goods_info']['inviter_open'] && $member_id && $inviter_model->getInviterInfo('i.inviter_id='.$member_id.' AND i.inviter_state=1')){
            $inviter_money=round($goods_detail['goods_info']['inviter_ratio'] / 100 * $goods_detail['goods_info']['goods_price'] * floatval(config('ds_config.inviter_ratio_1')) / 100, 2);
            if($inviter_money>0){
                $goods_detail['goods_info']['inviter_money']=$inviter_money;
            }
        }
        if(empty($goods_detail['mansong_info'])){
            $goods_detail['mansong_info']=false;
        }
        
        // 咨询类型
        $consult_type = rkcache('consulttype', true);
        
        $goods_detail['consult_type']=$consult_type;
        ds_json_encode(10000, '',$goods_detail);
    }


    /**
     * @api {POST} api/Goods/consulting_list 产品咨询列表
     * @apiVersion 3.0.6
     * @apiGroup Goods
     *
     * @apiParam {Int} goods_id 商品ID
     * @apiParam {Int} page 页码
     * @apiParam {Int} per_page 每页显示数量
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.consult_list  产品咨询列表
     * @apiSuccess {Int} result.consult_list.consult_id  产品咨询ID
     * @apiSuccess {Int} result.consult_list.goods_id  商品ID
     * @apiSuccess {String} result.consult_list.goods_name  商品名称
     * @apiSuccess {Int} result.consult_list.member_id  用户ID
     * @apiSuccess {String} result.consult_list.member_name  用户名称
     * @apiSuccess {Int} result.consult_list.consulttype_id  咨询类型ID
     * @apiSuccess {String} result.consult_list.consult_content  咨询内容
     * @apiSuccess {Int} result.consult_list.consult_addtime  咨询时间，Unix时间戳
     * @apiSuccess {String} result.consult_list.consult_reply  回复内容
     * @apiSuccess {Int} result.consult_list.consult_replytime  回复时间，Unix时间戳
     * @apiSuccess {Int} result.consult_list.consult_isanonymous  是否匿名
     * @apiSuccess {Int} result.page_total  总页数
     * @apiSuccess {Boolean} result.hasmore  是否有更多 true是false否
     */
    public function consulting_list() {

        $goods_id = intval(input('param.goods_id'));
        if ($goods_id <= 0) {
            ds_json_encode(10001,lang('param_error'));
        }

        //得到商品咨询信息
        $consult_model = model('consult');
        $where = array();
        $where[] = array('goods_id','=',$goods_id);
        if (intval(input('param.ctid')) > 0) {
            $where[] = array('consulttype_id','=',intval(input('param.ctid')));
        }
        $consult_list = $consult_model->getConsultList($where, '*');

        $result = array_merge(array('consult_list'=> $consult_list), mobile_page($consult_model->page_info));
        ds_json_encode(10000, '',$result);
    }


    /**
     * @api {POST} api/Goods/save_consult 商品咨询添加
     * @apiVersion 3.0.6
     * @apiGroup Goods
     *
     * @apiParam {Int} goods_id 商品ID
     * @apiParam {String} goods_content 咨询内容
     * @apiParam {Int} consult_type_id 咨询类型ID
     * @apiParam {String} key 用户授权token
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function save_consult() {
        $member_id = $this->getMemberIdIfExists();
        //检查是否可以评论
        if (!config('ds_config.guest_comment') && !$member_id) {
            ds_json_encode(10001,lang('goods_index_goods_noallow'));
        }
        $goods_id = intval(input('post.goods_id'));
        if ($goods_id <= 0) {
            ds_json_encode(10001,lang('param_error'));
        }
        //咨询内容的非空验证
        if (trim(input('post.goods_content')) == "") {
            ds_json_encode(10001,lang('goods_index_input_consult'));
        }
        //表单验证
        $data = [
            'goods_content' => input('post.goods_content')
        ];
        $res=word_filter($data['goods_content']);
        if(!$res['code']){
            ds_json_encode(10001,$res['msg']);
        }
        $data['goods_content']=$res['data']['text'];
        $goods_validate = ds_validate('goods');
        if (!$goods_validate->scene('save_consult')->check($data)) {
            ds_json_encode(10001,$goods_validate->getError());
        }


        //判断商品编号的存在性和合法性
        $goods = model('goods');
        $goods_info = $goods->getGoodsInfoByID($goods_id);
        if (empty($goods_info)) {
            ds_json_encode(10001,lang('goods_index_goods_not_exists'));
        }
        
        if ($member_id) {
            //查询会员信息
            $member_model = model('member');
            $member_info = $member_model->getMemberInfo(array('member_id' => $member_id));
            if (empty($member_info) || $member_info['is_allowtalk'] == 0) {
                ds_json_encode(10001,lang('goods_index_goods_noallow'));
            }
 
        }
        

        //接收数据并保存
        $input = array();
        $input['goods_id'] = $goods_id;
        $input['goods_name'] = $goods_info['goods_name'];
        $input['member_id'] = intval($member_id) > 0 ? $member_id : 0;
        $input['member_name'] = isset($member_info) ? $member_info['member_name'] : '';
        $input['consulttype_id'] = intval(input('post.consult_type_id',1));
        $input['consult_addtime'] = TIMESTAMP;
        $input['consult_content'] = $data['goods_content'];
        $input['consult_isanonymous'] = input('post.hide_name')=='hide'?1:0;
        $consult_model = model('consult');
        if ($consult_model->addConsult($input)) {
            ds_json_encode(10000,lang('ds_common_op_succ'));
        } else {
            ds_json_encode(10001,lang('ds_common_op_fail'));
        }
    }
    
    /**
     * 记录浏览历史
     */
    public function addbrowse() {
    	$goods_id = intval(input('param.gid'));
    	model('goodsbrowse')->addViewedGoods($goods_id, $this->member_info['member_id']);
    	exit();
    }
    /**
     * 商品详细信息处理
     */
    private function _goods_detail_extend($goods_detail) {
        //整理商品规格
        $goods_detail['spec_detail'] = json_decode($goods_detail['spec_list'],true);
        unset($goods_detail['spec_list']);
        $goods_detail['spec_list'] = $goods_detail['spec_list_mobile'];
        unset($goods_detail['spec_list_mobile']);

        //整理商品图片
        unset($goods_detail['goods_image']);
        $goods_detail['goods_image'] = $goods_detail['goods_image_mobile'];
        unset($goods_detail['goods_image_mobile']);
        
        //商品PC端详情信息
        $goods_detail['goods_info']['goods_body'] = htmlspecialchars_decode($goods_detail['goods_info']['goods_body']);
        
        //整理数据
//        unset($goods_detail['goods_info']['goods_commonid']);
        unset($goods_detail['goods_info']['gc_id']);
        unset($goods_detail['goods_info']['gc_name']);
        unset($goods_detail['goods_info']['brand_id']);
        unset($goods_detail['goods_info']['brand_name']);
        unset($goods_detail['goods_info']['type_id']);
        unset($goods_detail['goods_info']['goods_image']);
        unset($goods_detail['goods_info']['goods_stateremark']);
        unset($goods_detail['goods_info']['goods_lock']);
        unset($goods_detail['goods_info']['goods_addtime']);
        unset($goods_detail['goods_info']['goods_edittime']);
        unset($goods_detail['goods_info']['goods_shelftime']);
        unset($goods_detail['goods_info']['goods_show']);
        unset($goods_detail['goods_info']['goods_commend']);
        unset($goods_detail['goods_info']['explain']);
        unset($goods_detail['goods_info']['buynow_text']);
        unset($goods_detail['groupbuy_info']);
        unset($goods_detail['xianshi_info']);

        return $goods_detail;
    }

    /**
     * @api {POST} api/Goods/goods_evaluate 商品评论
     * @apiVersion 3.0.6
     * @apiGroup Goods
     *
     * @apiParam {Int} goods_id 商品ID
     * @apiParam {Int} type 类型 1好评 2中评 3差评
     * @apiParam {Int} page 页码
     * @apiParam {Int} per_page 每页显示数量
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.goods_eval_list  评论列表 （返回字段参考evaluategoods）
     * @apiSuccess {Int} result.page_total  总页数
     * @apiSuccess {Boolean} result.hasmore  是否有更多 true是false否
     */
    public function goods_evaluate() {
        $goods_id = intval(input('param.goods_id'));
        $type = intval(input('param.type'));

        $condition = array();
        $condition[] = array('geval_goodsid', '=', $goods_id);
        switch ($type) {
            case '1':
                $condition[] = array('geval_scores', 'in', '5,4');
                break;
            case '2':
                $condition[] = array('geval_scores', 'in', '3,2');
                break;
            case '3':
                $condition[] = array('geval_scores', 'in', '1');
                break;
            case '4':
                //$condition[] = array('geval_image|geval_image_again','<>', '');  //追加评价带后续处理
                $condition[] = array('geval_image', '<>', '');
                break;
            case '5':
                $condition[] = array('geval_content_again', '<>', '');
                break;
        }

        //查询商品评分信息
        $evaluategoods_model = model('evaluategoods');
        $goods_eval_list = $evaluategoods_model->getEvaluategoodsList($condition, $this->pagesize);
        foreach ($goods_eval_list as $k=>$val){
			if($val['geval_isanonymous']){
                $goods_eval_list[$k]['member_avatar']=get_member_avatar_for_id(0);
                $goods_eval_list[$k]['geval_frommembername']=str_cut($val['geval_frommembername'],2).'***';
            }
            if(!empty($goods_eval_list[$k]['geval_image'])) {
            $goods_eval_list[$k]['geval_image']=explode(',',$goods_eval_list[$k]['geval_image']);
                foreach ($goods_eval_list[$k]['geval_image'] as $kk => $vv) {
                    $goods_eval_list[$k]['geval_image'][$kk] = ds_get_pic(ATTACH_MALBUM , $vv);
                }
            }
        }
        $goods_eval_list = model('memberevaluate','logic')->evaluateListDity($goods_eval_list);
        $result = array_merge(array('goods_eval_list' => $goods_eval_list), mobile_page( is_object($evaluategoods_model->page_info)?$evaluategoods_model->page_info:0));
        ds_json_encode(10000, '',$result);
    }

    /**
     * 商品详细页运费显示
     *
     * @return unknown
     */
    public function calc() {
        $area_id = intval(input('param.area_id'));
        $goods_id = intval(input('param.goods_id'));
        ds_json_encode(10000, '',$this->_calc($area_id, $goods_id));
    }

    public function _calc($area_id, $goods_id) {
        $goods_info = model('goods')->getGoodsInfo(array('goods_id' => $goods_id), 'transport_id,goods_freight');
        $config['deliver_region'] = config('ds_config.deliver_region');
        $config['free_price'] = config('ds_config.free_price');
		$if_deliver=true;
        $area_name='';
        if ($area_id <= 0) {
            if (strpos($config['deliver_region'], '|')) {
                $config['deliver_region'] = explode('|', $config['deliver_region']);
                $config['deliver_region_ids'] = explode(' ', $config['deliver_region'][0]);
            }
            if(isset($config['deliver_region_ids'])){
                $area_id = intval($config['deliver_region_ids'][0]);
                $area_name = $config['deliver_region'][1];
            }
        }
        if ($goods_info['transport_id']) {
            $freight_total = model('transport')->calcTransport(intval($goods_info['transport_id']), $area_id);
            if ($freight_total > 0) {
                if ($config['free_price'] > 0) {
                    if ($freight_total >= $config['free_price']) {
                        $freight_total = '免运费';
                    } else {
                        $freight_total = '运费：' . $freight_total . ' 元，店铺满 ' . $config['free_price'] . ' 元 免运费';
                    }
                } else {
                    $freight_total = '运费：' . $freight_total . ' 元';
                }
            } else {
                if ($freight_total === false) {
                    $if_deliver = false;
                }
                $freight_total = '免运费';
            }
        } else {
            $freight_total = $goods_info['goods_freight'] > 0 ? '运费：' . $goods_info['goods_freight'] . ' 元' : '免运费';
        }
        return array('content' => $freight_total, 'if_deliver_cn' => $if_deliver === false ? '无货' : '有货', 'if_deliver' => $if_deliver === false ? false : true, 'area_name' => $area_name ? $area_name : '全国');
    }



    /**
     * 经纬度转换
     * @param unknown $bdLat
     * @param unknown $bdLon
     * @return multitype:number
     */
    public function bd_decrypt($bdLat, $bdLon) {
        $x = $bdLon - 0.0065;
        $y = $bdLat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $this->x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $this->x_pi);
        $gcjLon = $z * cos($theta);
        $gcjLat = $z * sin($theta);
        return array('lat' => $gcjLat, 'lon' => $gcjLon);
    }

    /**
     *  @desc 根据两点间的经纬度计算距离
     *  @param float $lat 纬度值
     *  @param float $lng 经度值
     */
    private function getDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6367000; //approximate radius of earth in meters

        /*
          Convert these degrees to radians
          to work with the formula
         */

        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;

        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;

        /*
          Using the
          Haversine formula

          http://en.wikipedia.org/wiki/Haversine_formula

          calculate the distance
         */

        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;

        return round($calculatedDistance);
    }

    private function parseDistance($num = 0) {
        $num = floatval($num);
        if ($num >= 1000) {
            $num = $num / 1000;
            return str_replace('.0', '', number_format($num, 1, '.', '')) . 'km';
        } else {
            return $num . 'm';
        }
    }

}
