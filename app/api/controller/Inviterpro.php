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
 * 分销商品控制器
 */
class Inviterpro extends MobileMall {

    public function initialize() {
        parent::initialize();
    }

    /*
     * 获取分销商品列表
     */

    public function index() {
        if (!config('ds_config.inviter_open')) {
            $result = array_merge(array('goods_list' => array(),), mobile_page(''));
        } else {
            $goods_model = model('goods');
            $condition = array();
            $condition[] = array('inviter_open', '=', 1);

            if (input('param.keyword')) {
                $condition[] = array('goods_name', 'like', '%' . input('param.keyword') . '%');
            }
            if (input('param.gc_id')) {
                $condition[] = array('gc_id_1', '=', intval(input('param.gc_id')));
            }
            $cache_key = 'api-inviterpro' . md5(serialize($condition)) . '-' . intval(input('param.page'));
            $result = rcache($cache_key);
            if (empty($result)) {
                $goods_list = $goods_model->getGoodsCommonList($condition, '*', 10);
                foreach ($goods_list as $key => $goods) {
                    $goods_info = $goods_model->getGoodsInfo(array('goods_commonid' => $goods['goods_commonid']), 'goods_id');
                    $goods_list[$key]['goods_id'] = $goods_info['goods_id'];
                    $goods_list[$key]['goods_image_url'] = goods_cthumb($goods['goods_image'], 240);
                    $goods_list[$key]['inviter_amount'] = 0;
                    if (config('ds_config.inviter_show')) {
                        $inviter_amount = round($goods['inviter_ratio'] / 100 * $goods['goods_price'] * floatval(config('ds_config.inviter_ratio_1')) / 100, 2);
                        if ($inviter_amount > 0) {
                            $goods_list[$key]['inviter_amount'] = $inviter_amount;
                        }
                    }
                }
                $page_count = $goods_model->page_info;
                $result = array_merge(array('goods_list' => $goods_list,), mobile_page($page_count));
                wcache($cache_key, $result);
            }
        }
        ds_json_encode(10000, '', $result);
    }

}
