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
 * 品牌控制器
 */
class Brand extends MobileMall {
    
    public function initialize() {
        parent::initialize();
    }
    
    /**
     * @api {POST} api/Brand/get_list 品牌列表
     * @apiVersion 3.0.6
     * @apiGroup Brand
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object} result.brand_class 品牌分类列表，键为品牌分类ID
     * @apiSuccess {Object} result.brand_class.brand_class 品牌分类名称
     * @apiSuccess {Object[]} result.brand_r 推荐品牌列表
     * @apiSuccess {String} result.brand_r.brand_class 品牌分类名称
     * @apiSuccess {Int} result.brand_r.brand_id 品牌ID
     * @apiSuccess {String} result.brand_r.brand_initial 品牌首字母
     * @apiSuccess {String} result.brand_r.brand_name 品牌名称
     * @apiSuccess {String} result.brand_r.brand_pic 品牌图标
     * @apiSuccess {Int} result.brand_r.brand_recommend 品牌推荐，0为否，1为是
     * @apiSuccess {Int} result.brand_r.brand_showtype 品牌展示类型 0表示图片 1表示文字
     * @apiSuccess {Int} result.brand_r.brand_sort 品牌排序
     * @apiSuccess {Int} result.brand_r.gc_id 品牌分类ID
     * @apiSuccess {Int} result.brand_r.store_id 品牌申请店铺ID
     * @apiSuccess {Object} result.brand_c 品牌列表，按品牌分类ID分组，键为品牌分类ID
     * @apiSuccess {String} result.brand_c.brand_class 品牌分类名称
     * @apiSuccess {Int} result.brand_c.brand_id 品牌ID
     * @apiSuccess {String} result.brand_c.brand_initial 品牌首字母
     * @apiSuccess {String} result.brand_c.brand_name 品牌名称
     * @apiSuccess {String} result.brand_c.brand_pic 品牌图标
     * @apiSuccess {Int} result.brand_c.brand_recommend 品牌推荐，0为否，1为是
     * @apiSuccess {Int} result.brand_c.brand_showtype 品牌展示类型 0表示图片 1表示文字
     * @apiSuccess {Int} result.brand_c.brand_sort 品牌排序
     * @apiSuccess {Int} result.brand_c.gc_id 品牌分类ID
     * @apiSuccess {Int} result.brand_c.store_id 品牌申请店铺ID
     */
    public function get_list() {
        $brand_mod=model('brand');
        $brand_c_list = $brand_mod->getBrandList(array());
        $brands = $this->_tidyBrand($brand_c_list);
        extract($brands);
        ds_json_encode(10000, '',array('brand_c' => $brand_listnew,'brand_class' => $brand_class,'brand_r' => $brand_r_list));
    }
    
    /**
     * 所有品牌全部显示在一级类目下，不显示二三级类目
     * @param type $brand_c_list
     * @return type
     */
    private function _tidyBrand($brand_c_list) {
        $brand_listnew = array();#品怕分类下对应的品牌
        $brand_class = array();#品牌分类
        $brand_r_list = array();#推荐品牌
        if (!empty($brand_c_list) && is_array($brand_c_list)) {
            $goods_class = model('goodsclass')->getGoodsclassForCacheModel();
            foreach ($brand_c_list as $key => $brand_c) {
                $brand_c['brand_pic']=brand_image($brand_c['brand_pic']);
                $gc_array = $this->_getTopClass($goods_class, $brand_c['gc_id']);
                if (empty($gc_array)) {
                    $brand_listnew[0][] = $brand_c;
                    $brand_class[0]['brand_class'] = '其他';
                } else {
                    $brand_listnew[$gc_array['gc_id']][] = $brand_c;
                    $brand_class[$gc_array['gc_id']]['brand_class'] = $gc_array['gc_name'];
                }
                //推荐品牌
                if ($brand_c['brand_recommend'] == 1) {
                    $brand_r_list[] = $brand_c;
                }
            }
        }
        krsort($brand_class);
        krsort($brand_listnew);
        return array('brand_listnew' => $brand_listnew, 'brand_class' => $brand_class, 'brand_r_list' => $brand_r_list);
    }
    
    /**
     * 获取顶级商品分类\递归调用
     * @param type $goods_class
     * @param type $gc_id
     * @return type
     */
    private function _getTopClass($goods_class, $gc_id) {
        if (!isset($goods_class[$gc_id])) {
            return null;
        }
        if($goods_class[$gc_id]['gc_parent_id']==$gc_id){//自身ID等于父ID
            return null;
        }
        if(isset($goods_class[$goods_class[$gc_id]['gc_parent_id']]['gc_parent_id']) && $goods_class[$goods_class[$gc_id]['gc_parent_id']]['gc_parent_id']==$gc_id){//父分类的父ID等于自身ID
            return null;
        }
        return $goods_class[$gc_id]['gc_parent_id'] == 0 ? $goods_class[$gc_id] : $this->_getTopClass($goods_class, $goods_class[$gc_id]['gc_parent_id']);
    }

}
