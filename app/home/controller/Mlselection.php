<?php

/*
 * 多级选择：地区选择，分类选择
 */

namespace app\home\controller;

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
class Mlselection extends BaseHome {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/mlselection.lang.php');
    }

    function index() {
        $type = input('param.type');
        $pid = intval(input('param.pid'));
        $result=array();
        in_array($type, array('region', 'goodsclass')) or json_encode('invalid type');
        switch ($type) {
            case 'region':
                $area_mod=model('area');
                $regions = $area_mod->getAreaList(array('area_parent_id'=>$pid));
                foreach ($regions as $key => $region) {
                    $result[$key]['area_name'] = htmlspecialchars($region['area_name']);
                    $result[$key]['area_id'] = $region['area_id'];
                }
                ds_json_encode(10000,'',$result);
                break;
            case 'goodsclass':
                $goodsclass_model = model('goodsclass');
                $goods_class = $goodsclass_model->getGoodsclassListByParentId($pid);
                $array = array();
                if (is_array($goods_class) and count($goods_class) > 0) {
                    foreach ($goods_class as $val) {
                        $array[$val['gc_id']] = array('gc_id' => $val['gc_id'], 'gc_name' => htmlspecialchars($val['gc_name']), 'gc_parent_id' => $val['gc_parent_id'], 'gc_sort' => $val['gc_sort']);
                    }
                }
                ds_json_encode(10000,'',array_values($array));
                break;
        }
    }

}

?>
