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
class Inviterpro extends BaseMall {

    //每页显示商品数
    const PAGESIZE = 12;

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/inviterpro.lang.php');
    }

    public function index() {
        $condition = array();
        $condition[]=array('gc_parent_id','=',0);
        $goods_class_array = model('goodsclass')->getGoodsclassList($condition);
        View::assign('goods_class_array', $goods_class_array);
        
        $goods_model = model('goods');
        if (!config('ds_config.inviter_open')) {
            $goods_list=array();
        } else {
            
            $condition = array();
            $condition[]=array('inviter_open','=',1);

            if (input('param.keyword')) {
                $condition[] = array('goods_name','like', '%' . input('param.keyword') . '%');
            }
            if(input('param.cate_id')){
                $condition[]=array('gc_id_1|gc_id_2|gc_id_3','=',intval(input('param.cate_id')));
            }

            $goods_list = $goods_model->getGoodsCommonList($condition, '*', self::PAGESIZE);
            foreach ($goods_list as $key => $goods) {
                $goods_info=$goods_model->getGoodsInfo(array('goods_commonid'=>$goods['goods_commonid']),'goods_id');
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

        }
        View::assign('goods_list', $goods_list);
        View::assign('show_page', is_object($goods_model->page_info)?$goods_model->page_info->render():"");

        // 当前位置导航
        View::assign('nav_link_list', array(array('title' => lang('homepage'), 'link' => url('home/Index/index')),array('title'=>lang('inviterpro_inviter_market'))));
        //SEO 设置
        $seo = array(
            'html_title'=>config('ds_config.site_name').'-'.lang('inviterpro_inviter_market'),
            'seo_keywords'=>lang('inviterpro_inviter_market'),
            'seo_description'=>lang('inviterpro_inviter_market'),
        );
        $this->_assign_seo($seo);

        return View::fetch($this->template_dir . 'index');
    }


}

?>
