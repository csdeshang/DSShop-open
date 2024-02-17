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
class Search extends BaseMall {

    //每页显示商品数
    const PAGESIZE = 12;

    //模型对象
    private $_model_search;

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/search.lang.php');
    }

    public function index() {

        $this->_model_search = model('search');
        //显示左侧分类
        //默认分类，从而显示相应的属性和品牌
        $cate_id = $default_classid = intval(input('param.cate_id'));
        $keyword = input('param.keyword');
        $goods_class_array = array();
        if ($default_classid > 0) {
            $goods_class_array = $this->_model_search->getLeftCategory(array($default_classid));
        } elseif ($keyword != '') {
            //从TAG中查找分类
            $goods_class_array = $this->_model_search->getTagCategory($keyword);
            //取出第一个分类作为默认分类，从而显示相应的属性和品牌
            $default_classid = isset($goods_class_array[0]) ? $goods_class_array[0] : "";
            $goods_class_array = $this->_model_search->getLeftCategory($goods_class_array, 1);
        }

        View::assign('goods_class_array', $goods_class_array);
        View::assign('default_classid', $default_classid);


        //获得经过属性过滤的商品信息 
        list($goods_param, $brand_array, $initial_array, $attr_array, $checked_brand, $checked_attr) = $this->_model_search->getAttribute(input('param.'), $default_classid);
        View::assign('brand_array', $brand_array);
        View::assign('initial_array', $initial_array);
        View::assign('attr_array', $attr_array);
        View::assign('checked_brand', $checked_brand);
        View::assign('checked_attr', $checked_attr);

        //处理排序
        $order = 'goodscommon.goods_commend desc,goodscommon.goods_sort asc';
        $key = input('param.key');
        $sequence = input('param.order');
        if (in_array($key, array('1', '2', '3'))) {
            $sequence = $sequence == '1' ? 'asc' : 'desc';
            $order = str_replace(array('1', '2', '3'), array('goods_salenum', 'goods_click', 'goods_promotion_price'), $key);
            $order .= ' ' . $sequence;
        }
        $goods_model = model('goods');
        // 字段
        $fields = "goods.goods_id,goodscommon.goods_commonid,goodscommon.goods_name,goodscommon.goods_advword,goodscommon.gc_id,goodscommon.goods_price,goods.goods_promotion_price,goods.goods_promotion_type,goodscommon.goods_marketprice,goods.goods_storage,goodscommon.goods_image,goodscommon.goods_freight,goods.goods_salenum,goods.color_id,goods.evaluation_good_star,goods.evaluation_count,goodscommon.is_virtual,goodscommon.is_goodsfcode,goodscommon.is_appoint,goodscommon.is_presell,goods.is_have_gift";

        $condition = array();

            //执行正常搜索
            if (isset($goods_param['class']['depth'])) {
                $condition[] = array('goodscommon.gc_id_' . $goods_param['class']['depth'],'=',$goods_param['class']['gc_id']);
            }
            $b_id = intval(input('param.b_id'));
            if ($b_id > 0) {
                $condition[]=array('goodscommon.brand_id','=',$b_id);
            }
            if ($keyword != '') {
                $condition[]=array('goodscommon.goods_name|goodscommon.goods_advword','like', '%' . $keyword . '%');
            }
            $area_id = intval(input('param.area_id'));
            if ($area_id > 0) {
                $condition[]=array('goodscommon.areaid_1','=',$area_id);
            }

            $gift = intval(input('param.gift'));
            if ($gift == 1) {
                $condition[]=array('goods.is_have_gift','=',1);
            }
            if (isset($goods_param['goodsid_array'])) {
                $condition[]=array('goods.goods_id','in', $goods_param['goodsid_array']);
            }
            $priceMin = intval(input('param.priceMin'));
            if ($priceMin > 0) {
                $condition[]=array('goodscommon.goods_price','>=', $priceMin);
            }
            $priceMax = intval(input('param.priceMax'));
            if ($priceMax > 0) {
                $condition[]=array('goodscommon.goods_price','<=', $priceMax);
            }

            if ($priceMin > 0 && $priceMax > 0) {
                $condition[] = array('goodscommon.goods_price','between', array($priceMin, $priceMax));
            }
            $goods_list = $goods_model->getGoodsUnionList($condition, $fields, $order,'goodscommon.goods_commonid', self::PAGESIZE);
//        }
        View::assign('show_page', is_object($goods_model->page_info)?$goods_model->page_info->render():"");

        // 商品多图
        if (!empty($goods_list)) {
            $commonid_array = array(); // 商品公共id数组
            foreach ($goods_list as $value) {
                $commonid_array[] = $value['goods_commonid'];
            }
            $commonid_array = array_unique($commonid_array);

            // 商品多图
            $goodsimage_more = model('goods')->getGoodsImageList(array(array('goods_commonid','in', $commonid_array)));

            //搜索的关键字
            $search_keyword = $keyword;
            foreach ($goods_list as $key => $value) {
                if(!$value['goods_storage']){
                    $goods_info=$goods_model->getGoodsStorageByCommonId($value['goods_commonid']);
                    if($goods_info){
                        $value['goods_id']=$goods_info['goods_id'];
                    }
                }
                // 商品多图
                //商品列表主图限制不越过5个
                $n = 0;
                foreach ($goodsimage_more as $v) {
                    if ($value['goods_commonid'] == $v['goods_commonid'] &&  $value['color_id'] == $v['color_id']) {
                        $n++;
                        $goods_list[$key]['image'][] = $v;
                        if ($n >= 5)
                            break;
                    }
                }
                //将关键字置红
                if ($search_keyword) {
                    $goods_list[$key]['goods_name_highlight'] = str_replace($search_keyword, '<font style="color:#f00;">' . $search_keyword . '</font>', $value['goods_name']);
                } else {
                    $goods_list[$key]['goods_name_highlight'] = $value['goods_name'];
                }
            }
        }
        View::assign('goods_list', $goods_list);
        if ($keyword != '') {
            View::assign('show_keyword', $keyword);
        } else {
            View::assign('show_keyword', isset($goods_param['class']['gc_name']) ? $goods_param['class']['gc_name'] : '');
        }

        $goodsclass_model = model('goodsclass');

        // SEO
        if ($keyword == '') {
            $seo_class_name = isset($goods_param['class']['gc_name'])?$goods_param['class']['gc_name']:'';
            if (is_numeric($cate_id) && empty($keyword)) {
                $seo_info = $goodsclass_model->getKeyWords($cate_id);
                if (empty($seo_info[1])) {
                    $seo_info[1] = config('ds_config.site_name') . ' - ' . $seo_class_name;
                }
                $seo = model('seo')->type($seo_info)->param(array('name' => $seo_class_name))->show();
                $this->_assign_seo($seo);
            }
        } elseif ($keyword != '') {
            $keyword=urldecode($keyword);
            View::assign('html_title', (empty($keyword) ? '' : $keyword . ' - ') . config('ds_config.site_name') . lang('ds_search'));
        }

        // 当前位置导航
        $nav_link_list = $goodsclass_model->getGoodsclassnav($cate_id);
        View::assign('nav_link_list', $nav_link_list);

        // 得到自定义导航信息
        $nav_id = intval(input('param.nav_id'));
        View::assign('index_sign', $nav_id);

        // 地区
        $province_array = model('area')->getTopLevelAreas();
        View::assign('province_array', $province_array);

        /* 引用搜索相关函数 */
        require_once(base_path() . '/home/common_search.php');

        // 浏览过的商品
        $viewed_goods = model('goodsbrowse')->getViewedGoodsList(session('member_id'), 20);
        View::assign('viewed_goods', $viewed_goods);

        return View::fetch($this->template_dir . 'search');
    }

    /**
     * 获得推荐商品
     */
    public function get_hot_goods() {
        $gc_id = input('param.cate_id');
        if ($gc_id <= 0) {
            exit;
        }
        // 获取分类id及其所有子集分类id
        $goods_class = model('goodsclass')->getGoodsclassForCacheModel();
        if (empty($goods_class[$gc_id])) {
            exit;
        }
        $child = (!empty($goods_class[$gc_id]['child'])) ? explode(',', $goods_class[$gc_id]['child']) : array();
        $childchild = (!empty($goods_class[$gc_id]['childchild'])) ? explode(',', $goods_class[$gc_id]['childchild']) : array();
        $gcid_array = array_merge(array($gc_id), $child, $childchild);
        // 查询添加到推荐展位中的商品id
        $boothgoods_list = model('goods')->getGoodsOnlineList(array(array('gc_id','in', $gcid_array)), 'goods_id', 4, '');
        if (empty($boothgoods_list)) {
            exit;
        }

        $goodsid_array = array();
        foreach ($boothgoods_list as $val) {
            $goodsid_array[] = $val['goods_id'];
        }

        $fieldstr = "goods_id,goods_commonid,goods_name,goods_advword,goods_price,goods_promotion_price,goods_promotion_type,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,color_id,evaluation_count";
        $goods_list = model('goods')->getGoodsOnlineList(array(array('goods_id','in', $goodsid_array)), $fieldstr);
        if (empty($goods_list)) {
            exit;
        }

        View::assign('goods_list', $goods_list);
        echo View::fetch($this->template_dir.'goods_hot');
    }

    /**
     * 获得同类商品排行
     */
    public function get_listhot_goods() {
        $gc_id = input('param.cate_id');
        if ($gc_id <= 0) {
            return false;
        }
        // 获取分类id及其所有子集分类id
        $goods_class = model('goodsclass')->getGoodsclassForCacheModel();
        if (empty($goods_class[$gc_id])) {
            return false;
        }
        $child = (!empty($goods_class[$gc_id]['child'])) ? explode(',', $goods_class[$gc_id]['child']) : array();
        $childchild = (!empty($goods_class[$gc_id]['childchild'])) ? explode(',', $goods_class[$gc_id]['childchild']) : array();
        $gcid_array = array_merge(array($gc_id), $child, $childchild);
        // 查询添加到推荐展位中的商品id
        $boothgoods_list = model('goods')->getGoodsOnlineList(array(array('gc_id','in', $gcid_array)));
        if (empty($boothgoods_list)) {
            return false;
        }

        $goodsid_array = array();
        foreach ($boothgoods_list as $val) {
            $goodsid_array[] = $val['goods_id'];
        }

        $fieldstr = "goods_id,goods_commonid,goods_name,goods_advword,goods_price,goods_promotion_price,goods_promotion_type,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,color_id,evaluation_count";
        $goods_list = model('goods')->getGoodsOnlineList(array(array('goods_id','in', $goodsid_array)), $fieldstr, 5, 'goods_salenum desc');
        if (empty($goods_list)) {
            return false;
        }

        View::assign('goods_list', $goods_list);
    }


    /**
     * 获得猜你喜欢
     */
    public function get_guesslike() {
        $goodslist = model('goodsbrowse')->getGuessLikeGoods(session('member_id'), 20);
        if (!empty($goodslist)) {
            View::assign('goodslist', $goodslist);
            echo View::fetch($this->template_dir.'goods_guesslike');
        }
    }

}

?>
