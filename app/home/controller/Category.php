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
class Category extends BaseMall {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/category.lang.php');
    }

    /*
     * 显示商品分类列表
     */

    public function goods() {
        //获取全部的商品分类
        //导航
        $nav_link = array(
            '0' => array('title' => lang('ds_index'), 'link' => HOME_SITE_URL),
            '1' => array('title' => lang('category_all_categories'))
        );
        View::assign('nav_link_list', $nav_link);

        View::assign('html_title', config('ds_config.site_name') . ' - ' . lang('category_all_categories'));
        return View::fetch($this->template_dir . 'goods_category');
    }


}
