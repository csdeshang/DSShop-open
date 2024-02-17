<?php

namespace app\common\model;



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
class Search extends BaseModel {


    public function __get($key) {
        return $this->$key;
    }


    /**
     * 取得商品分类详细信息
     * @access public
     * @author csdeshang
     * @param type $get 需要的参数内容
     * @param type $default_classid 默认分类id
     * @return type
     */
    public function getAttribute($get, $default_classid) {
        if (!empty($get['a_id'])) {
            $attr_ids = explode('_', $get['a_id']);
        }
        $data = array();

        // 获取当前的分类内容
        $class_array = model('goodsclass')->getGoodsclassForCacheModel();
        $data['class'] = isset($get['cate_id'])&&isset($class_array[$get['cate_id']]) ? $class_array[$get['cate_id']]:array();
        if (empty($data['class']['child']) && empty($data['class']['childchild'])) {
            // 根据属性查找商品
            if (isset($attr_ids)) {
                // 商品id数组
                $goodsid_array = array();
                $data['sign'] = false;
                foreach ($attr_ids as $val) {
                    $where = array();
                    $where[] = array('attrvalue_id','=',$val);
                    if ($data['sign']) {
                        $where[] = array('goods_id','in', $goodsid_array);
                    }
                    $goodsattrindex_list = model('goodsattrindex')->getGoodsAttrIndexList($where, 'goods_id');
                    if (!empty($goodsattrindex_list)) {
                        $data['sign'] = true;
                        $tpl_goodsid_array = array();
                        foreach ($goodsattrindex_list as $val) {
                            $tpl_goodsid_array[] = $val['goods_id'];
                        }
                        $goodsid_array = $tpl_goodsid_array;
                    } else {
                        $data['goodsid_array'] = $goodsid_array = array();
                        $data['sign'] = false;
                        break;
                    }
                }
                if ($data['sign']) {
                    $data['goodsid_array'] = $goodsid_array;
                }
            }
        }

        $class = isset($class_array[$default_classid])?$class_array[$default_classid]:"";
        $brand_array = array();
        $initial_array = array();
        $attr_array = array();
        $checked_brand = array();
        $checked_attr = array();
        
        if (empty($class['child']) && empty($class['childchild'])) {
            //获得分类对应的类型ID
            $type_id = isset($class['type_id']) ? $class['type_id'] : '';
            if (!empty($type_id)) {

                //品牌列表
                $typebrand_list = model('type')->getTypebrandList(array('type_id' => $type_id), 'brand_id');
                if (!empty($typebrand_list)) {
                    $brandid_array = array();
                    foreach ($typebrand_list as $val) {
                        $brandid_array[] = $val['brand_id'];
                    }
                    $brand_array = model('brand')->getBrandPassedList(array(array('brand_id','in', $brandid_array)), 'brand_id,brand_name,brand_initial,brand_pic,brand_showtype', 0, 'brand_showtype asc,brand_recommend desc,brand_sort asc');
                    if (!empty($brand_array)) {
                        $brand_list = array();
                        foreach ($brand_array as $val) {
                            $brand_list[$val['brand_id']] = $val;
                            $initial_array[] = $val['brand_initial'];
                        }
                        $brand_array = $brand_list;
                        $initial_array = array_unique($initial_array);
                        sort($initial_array);
                    }
                }
                // 被选中的品牌
                $brand_id = isset($get['b_id']) ? intval($get['b_id']) : "";
                if ($brand_id > 0 && !empty($brand_array)) {
                    if (isset($brand_array[$brand_id])) {
                        $checked_brand[$brand_id]['brand_name'] = $brand_array[$brand_id]['brand_name'];
                    }
                }

                //属性列表
                $attribute_model = model('attribute');
                $attribute_list = $attribute_model->getAttributeShowList(array(array('type_id' ,'=', $type_id)), 'attr_id,attr_name');
                $attributevalue_list = $attribute_model->getAttributeValueList(array('type_id' => $type_id), 'attrvalue_id,attrvalue_name,attr_id');
                $attributevalue_list = array_under_reset($attributevalue_list, 'attr_id', 2);

                if (!empty($attribute_list)) {
                    foreach ($attribute_list as $val) {
                        $attr_array[$val['attr_id']]['name'] = $val['attr_name'];
                        if(isset($attributevalue_list[$val['attr_id']])){
                            $tpl_array = array_under_reset($attributevalue_list[$val['attr_id']], 'attrvalue_id');
                            $attr_array[$val['attr_id']]['value'] = $tpl_array;
                        }else{
                            $attr_array[$val['attr_id']]['value'] = array();
                        }
                        
                    }
                }
                // 被选中的属性
                if (isset($attr_ids) && is_array($attr_ids) && !empty($attr_array)) {
                    foreach ($attr_ids as $s) {
                        foreach ($attr_array as $k => $d) {
                            if (isset($d['value'][$s])) {
                                $checked_attr[$k]['attr_name'] = $d['name'];
                                $checked_attr[$k]['attrvalue_id'] = $s;
                                $checked_attr[$k]['attrvalue_name'] = $d['value'][$s]['attrvalue_name'];
                            }
                        }
                    }
                }
    
            }
        }

        return array($data, $brand_array, $initial_array, $attr_array, $checked_brand, $checked_attr);
    }

    /**
     * 从TAG中查找分类
     * @access public
     * @author csdeshang
     * @param type $keyword 
     * @return type
     */
    public function getTagCategory($keyword = '') {
        $data = array();
        if ($keyword != '') {
            // 跟据class_tag缓存搜索出与keyword相关的分类
            $tag_list = rkcache('classtag', true);
            if (!empty($tag_list) && is_array($tag_list)) {
                foreach ($tag_list as $key => $val) {
                    $tag_value = $val['gctag_value'];
                    if (strpos($tag_value, $keyword)!==false) {
                        $data[] = $val['gc_id'];
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 获取父级分类，递归调用
     * @access public
     * @author csdeshang
     * @param type $gc_id 分类id
     * @param type $goods_class 商品分类
     * @param type $data 数据
     * @return type
     */
    private function _getParentCategory($gc_id, $goods_class, $data) {
        array_unshift($data, $gc_id);
        if (isset($goods_class[$gc_id]['gc_parent_id']) && $goods_class[$gc_id]['gc_parent_id'] != 0) {
            return $this->_getParentCategory($goods_class[$gc_id]['gc_parent_id'], $goods_class, $data);
        } else {
            return $data;
        }
    }

    /**
     * 显示左侧商品分类
     * @access public
     * @author csdeshang
     * @param type $param 分类id
     * @param type $sign 0为取得最后一级的同级分类，1为不取得
     * @return type
     */
    public function getLeftCategory($param, $sign = 0) {
        $data = array();
        if (!empty($param)) {
            $goods_class = model('goodsclass')->getGoodsclassForCacheModel();
            foreach ($param as $val) {
                $data[] = $this->_getParentCategory($val, $goods_class, array());
            }
        }
        $tpl_data = array();
        $gc_list = model('goodsclass')->get_all_category();
        foreach ($data as $value) {
            //$tpl_data[$val[0]][$val[1]][$val[2]] = $val[2];
            if (!empty($gc_list[$value[0]])) {   // 一级
                $tpl_data[$value[0]]['gc_id'] = $gc_list[$value[0]]['gc_id'];
                $tpl_data[$value[0]]['gc_name'] = $gc_list[$value[0]]['gc_name'];
                if (isset($value[0]) && isset($value[1]) && isset($gc_list[$value[0]]['class2'][$value[1]])) { // 二级
                    $tpl_data[$value[0]]['class2'][$value[1]]['gc_id'] = $gc_list[$value[0]]['class2'][$value[1]]['gc_id'];
                    $tpl_data[$value[0]]['class2'][$value[1]]['gc_name'] = $gc_list[$value[0]]['class2'][$value[1]]['gc_name'];
                    if (isset($value[0]) && isset($value[1]) && isset($value[2]) && isset($gc_list[$value[0]]['class2'][$value[1]]['class3'][$value[2]])) {    // 三级
                        $tpl_data[$value[0]]['class2'][$value[1]]['class3'][$value[2]]['gc_id'] = $gc_list[$value[0]]['class2'][$value[1]]['class3'][$value[2]]['gc_id'];
                        $tpl_data[$value[0]]['class2'][$value[1]]['class3'][$value[2]]['gc_name'] = $gc_list[$value[0]]['class2'][$value[1]]['class3'][$value[2]]['gc_name'];
                        if (!$sign) {   // 取得全部三级分类
                            foreach ($gc_list[$value[0]]['class2'][$value[1]]['class3'] as $val) {
                                $tpl_data[$value[0]]['class2'][$value[1]]['class3'][$val['gc_id']]['gc_id'] = $val['gc_id'];
                                $tpl_data[$value[0]]['class2'][$value[1]]['class3'][$val['gc_id']]['gc_name'] = $val['gc_name'];
                                if ($value[2] == $val['gc_id']) {
                                    $tpl_data[$value[0]]['class2'][$value[1]]['class3'][$val['gc_id']]['default'] = 1;
                                }
                            }
                        }
                    } else {    // 取得全部二级分类
                        if (!$sign) {   // 取得同级分类
                            if (!empty($gc_list[$value[0]]['class2'])) {
                                foreach ($gc_list[$value[0]]['class2'] as $gc2) {
                                    $tpl_data[$value[0]]['class2'][$gc2['gc_id']]['gc_id'] = $gc2['gc_id'];
                                    $tpl_data[$value[0]]['class2'][$gc2['gc_id']]['gc_name'] = $gc2['gc_name'];
                                    if (!empty($gc2['class3'])) {
                                        foreach ($gc2['class3'] as $gc3) {
                                            $tpl_data[$value[0]]['class2'][$gc2['gc_id']]['class3'][$gc3['gc_id']]['gc_id'] = $gc3['gc_id'];
                                            $tpl_data[$value[0]]['class2'][$gc2['gc_id']]['class3'][$gc3['gc_id']]['gc_name'] = $gc3['gc_name'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {    // 取得全部一级分类
                    if (!$sign) {   // 取得同级分类
                        if (!empty($gc_list)) {
                            foreach ($gc_list as $gc1) {
                                $tpl_data[$gc1['gc_id']]['gc_id'] = $gc1['gc_id'];
                                $tpl_data[$gc1['gc_id']]['gc_name'] = $gc1['gc_name'];
                                if (!empty($gc1['class2'])) {
                                    foreach ($gc1['class2'] as $gc2) {
                                        $tpl_data[$gc1['gc_id']]['class2'][$gc2['gc_id']]['gc_id'] = $gc2['gc_id'];
                                        $tpl_data[$gc1['gc_id']]['class2'][$gc2['gc_id']]['gc_name'] = $gc2['gc_name'];
                                        if (!empty($gc2['class3'])) {
                                            foreach ($gc2['class3'] as $gc3) {
                                                $tpl_data[$gc1['gc_id']]['class2'][$gc2['gc_id']]['class3'][$gc3['gc_id']]['gc_id'] = $gc3['gc_id'];
                                                $tpl_data[$gc1['gc_id']]['class2'][$gc2['gc_id']]['class3'][$gc3['gc_id']]['gc_name'] = $gc3['gc_name'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $tpl_data;
    }


}

?>
