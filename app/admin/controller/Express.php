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
class Express extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/express.lang.php');
    }

    public function index() {
        $express_letter = input('get.express_letter');
        $condition = array();
        if (preg_match('/^[A-Z]$/', $express_letter)) {
            $condition[] = array('express_letter','=',$express_letter);
        }
        
        $express_name = input('get.express_name');
        if(!empty($express_name)){
            $condition[] = array('express_name','like', "%" . $express_name . "%");
        }
        
        $express_model = model('express');
        $express_list = $express_model->getAllExpresslist($condition, 10);
        View::assign('show_page', $express_model->page_info->render());
        View::assign('express_list', $express_list);
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 添加品牌
     */
    public function add() {
        $express_mod = model('express');
        if (request()->isPost()) {
            $insert_array['express_name'] = trim(input('post.express_name'));
            $insert_array['express_code'] = input('post.express_code');
            $insert_array['express_state'] = intval(input('post.express_state'));
            $insert_array['express_letter'] = strtoupper(input('post.express_letter'));
            $insert_array['express_order'] = intval(input('post.express_order'));
            $insert_array['express_url'] = input('post.express_url');
            $insert_array['express_zt_state'] = intval(input('post.express_zt_state'));

            $result = $express_mod->addExpress($insert_array);
            if ($result) {
                $this->log(lang('ds_add') . lang('express') . '[' . input('post.express_name') . ']', 1);
                dsLayerOpenSuccess(lang('ds_common_save_succ'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        } else {
            $express = [
                'express_zt_state' => 1,
                'express_order' => 1,
                'express_state' => 1,
            ];
            View::assign('express', $express);
            return View::fetch('form');
        }
    }

    public function edit() {
        $express_model = model('express');
        $express_id = input('param.express_id');
        if (request()->isPost()) {
            $condition = array();
            $condition[] = array('express_id','=',$express_id);

            $data['express_name'] = trim(input('post.express_name'));
            $data['express_code'] = input('post.express_code');
            $data['express_state'] = intval(input('post.express_state'));
            $data['express_letter'] = strtoupper(input('post.express_letter'));
            $data['express_order'] = intval(input('post.express_order'));
            $data['express_url'] = input('post.express_url');
            $data['express_zt_state'] = intval(input('post.express_zt_state'));
            $result = $express_model->editExpress($condition, $data);

            if ($result) {
                $this->log(lang('ds_edit') . lang('express_name') . lang('ds_state') . '[ID:' . $express_id . ']', 1);
                dsLayerOpenSuccess(lang('ds_common_save_succ'));
            } else {
                $this->log(lang('ds_edit') . lang('express_name') . lang('ds_state') . '[ID:' . $express_id . ']', 0);
                $this->error(lang('ds_common_save_fail'));
            }
        } else {
            $condition = array();
            $condition[] = array('express_id','=',$express_id);
            $express = $express_model->getOneExpress($condition);
            if (empty($express)) {
                $this->error(lang('param_error'));
            }
            View::assign('express', $express);
            return View::fetch('form');
        }
    }

    /**
     * 删除品牌
     */
    public function del() {
        $express_id = input('param.express_id');
        $express_id_array = ds_delete_param($express_id);
        if ($express_id_array == FALSE) {
            $this->log(lang('ds_del') . lang('express') . '[ID:' . $express_id . ']', 0);
            ds_json_encode(10001, lang('param_error'));
        }
        $express_mod = model('express');
        $express_mod->delExpress(array(array('express_id','in', implode(',', $express_id_array))));
        $this->log(lang('ds_del') . lang('express') . '[ID:' . $express_id . ']', 1);
        ds_json_encode(10000, lang('ds_common_del_succ'));
    }

    /**
     * ajax操作
     */
    public function ajax() {
        $branch = input('get.branch');
        $column = input('get.column');
        $value = trim(input('get.value'));
        $id = intval(input('get.id'));
        switch ($branch) {
            case 'state':
                $express_model = model('express');
                $update_array = array();
                $condition = array();
                $condition[] = array('express_id','=',$id);
                $update_array[$column] = $value;
                $express_model->editExpress($condition, $update_array);
                $this->log(lang('ds_edit') . lang('express_name') . lang('ds_state') . '[ID:' . $id . ']', 1);
                echo 'true';
                exit;
                break;
            case 'order':
                $express_model = model('express');
                $update_array = array();
                $condition = array();
                $condition[] = array('express_id','=',$id);
                $update_array[$column] = $value;
                $express_model->editExpress($condition, $update_array);
                $this->log(lang('ds_edit') . lang('express_name') . lang('ds_state') . '[ID:' . $id . ']', 1);
                echo 'true';
                exit;
                break;
            case 'express_zt_state':
                $express_model = model('express');
                $update_array = array();
                $condition = array();
                $condition[] = array('express_id','=',$id);
                $update_array[$column] = $value;
                $express_model->editExpress($condition, $update_array);
                $this->log(lang('ds_edit') . lang('express_name') . lang('ds_state') . '[ID:' . $id . ']', 1);
                echo 'true';
                exit;
                break;
        }
    }

    public function config(){
        $config_model = model('config');
        if (!request()->isPost()) {
            $list_config = rkcache('config', true);
            View::assign('list_config', $list_config);
            /* 设置卖家当前栏目 */
            $this->setAdminCurItem('express_config');
            return View::fetch();
        } else {
            $update_array = array();
            $update_array['expresscf_kdn_type'] = input('post.expresscf_kdn_type');
            $update_array['expresscf_kdn_id'] = input('post.expresscf_kdn_id');
            $update_array['expresscf_kdn_key'] = input('post.expresscf_kdn_key');
            $result = $config_model->editConfig($update_array);
            if ($result) {
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => lang('ds_manage'),
                'url' => url('Express/index'),
            ),
            array(
                'name' => 'express_config',
                'text' => '快递查询设置',
                'url' => url('Express/config')
            ),
            array(
                'name' => 'express_add',
                'text' => lang('ds_add'),
                'url' => "javascript:dsLayerOpen('" . url('Express/add') . "','添加')"
            ),
        );
        return $menu_array;
    }

}

?>
