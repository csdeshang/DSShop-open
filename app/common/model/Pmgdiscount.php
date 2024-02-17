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
class Pmgdiscount extends BaseModel {

    public function getPmgdiscountInfoByGoodsInfo($goods_info) {
        //查看此商品是否单独设置了折扣
        if ($goods_info['goods_mgdiscount'] != '') {
            return unserialize($goods_info['goods_mgdiscount']);
        }
        //当店铺设置了店铺会员等级折扣
        if (config('ds_config.store_mgdiscount') != '') {
            return unserialize(config('ds_config.store_mgdiscount'));
        }
        return;
    }

}
