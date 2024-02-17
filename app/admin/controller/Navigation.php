<?php

namespace app\admin\controller;
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
class Navigation extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/navigation.lang.php');
    }

    public function index() {
        $navigation_model = model('navigation');
        $condition = array();
        $nav_title = input('param.nav_title');
        if (!empty($nav_title)) {
            $condition[] = array('nav_title','like', "%" . $nav_title . "%");
        }
        $nav_location = input('param.nav_location');
        if (!empty($nav_location)) {
            $condition[] = array('nav_location','=',$nav_location);
        }
        $nav_list = $navigation_model->getNavigationList($condition, 10);
        View::assign('nav_list', $nav_list);
        View::assign('show_page', $navigation_model->page_info->render());
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    public function add() {
        if (!(request()->isPost())) {
            $nav = [
                'nav_location' => 'header',
                'nav_new_open' => 0,
            ];
            View::assign('nav', $nav);
            return View::fetch('form');
        } else {
            $data['nav_title'] = input('post.nav_title');
            $data['nav_location'] = input('post.nav_location');
            $data['nav_url'] = input('post.nav_url');
            $data['nav_new_open'] = intval(input('post.nav_new_open'));
            $data['nav_sort'] = intval(input('post.nav_sort'));
            //定义验证规则

            $navigation_validate = ds_validate('navigation');
            if (!$navigation_validate->scene('add')->check($data)) {
                $this->error($navigation_validate->getError());
            }

            $navigation_model= model('navigation');
            $result=$navigation_model->addNavigation($data);
            if ($result) {
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
//                $this->success(lang('ds_common_op_succ'), 'Navigation/index');
            } else {
                $this->error(lang('error'));
            }
        }
    }

    public function edit() {
        $navigation_model= model('navigation');
        $nav_id = input('param.nav_id');
        if (empty($nav_id)) {
            $this->error(lang('param_error'));
        }
        if (!request()->isPost()) {
            $condition = array();
            $condition[] = array('nav_id','=',$nav_id);
            $nav=$navigation_model->getOneNavigation($condition);
            View::assign('nav', $nav);
            return View::fetch('form');
        } else {
            $data['nav_title'] = input('post.nav_title');
            $data['nav_location'] = input('post.nav_location');
            $data['nav_url'] = input('post.nav_url');
            $data['nav_new_open'] = intval(input('post.nav_new_open'));
            $data['nav_sort'] = intval(input('post.nav_sort'));
            //定义验证规则
            $navigation_validate = ds_validate('navigation');
            if (!$navigation_validate->scene('edit')->check($data)) {
                $this->error($navigation_validate->getError());
            }
            $condition = array();
            $condition[] = array('nav_id','=',$nav_id);
            $result = $navigation_model->eidtNavigation($data,$condition);
            if ($result>=0) {
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
//                $this->success(lang('ds_common_op_succ'), 'Navigation/index');
            } else {
                $this->error(lang('error'));
            }
        }
    }

    public function drop() {
        $navigation_model= model('navigation');
        $nav_id = input('param.nav_id');
        $nav_id_array = ds_delete_param($nav_id);
        if($nav_id_array === FALSE){
            ds_json_encode('10001', lang('param_error'));
        }
        $condition = array(array('nav_id','in', $nav_id_array));
        $result =$navigation_model->delNavigation($condition);
        if ($result) {
            ds_json_encode('10000', lang('ds_common_del_succ'));
        } else {
            ds_json_encode('10001', lang('ds_common_del_fail'));
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Navigation/index')
            ),
            array(
                'name' => 'add',
                'text' => '新增',
                'url' => "javascript:dsLayerOpen('".url('Navigation/add')."','新增导航')"
            ),
        );
        return $menu_array;
    }

}