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
 * 门店管理 控制器
 */
class Entityshop extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/entityshop.lang.php');
    }

    public function index() {
        $entityshop_model = model('entityshop');
        $condition = array();
        $entityshop_list = $entityshop_model->getEntityshopList($condition, 10);
        View::assign('entityshop_list', $entityshop_list);
        View::assign('show_page', $entityshop_model->page_info->render());
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    public function add() {
        $entityshop_model = model('entityshop');
        if (!request()->isPost()) {
            $entityshop = array(
                'entityshop_state' => 1,
                'entityshop_longitude' => 0,
                'entityshop_latitude' => 0,
            );
            View::assign('entityshop', $entityshop);
            View::assign('baidu_ak', config('ds_config.baidu_ak'));
            $this->setAdminCurItem('add');
            return View::fetch('form');
        } else {
            $data_entityshop = array(
                'entityshop_name' => input('param.entityshop_name'),
                'entityshop_linkname' => input('param.entityshop_linkname'),
                'entityshop_phone' => input('param.entityshop_phone'),
                'entityshop_pic' => '',
                'entityshop_hours' => input('param.entityshop_hours'),
                'region_id' => input('param.region_id'),
                'area_info' => input('param.area_info'),
                'entityshop_address' => input('param.entityshop_address'),
                'entityshop_longitude' => input('param.entityshop_longitude'),
                'entityshop_latitude' => input('param.entityshop_latitude'),
                'entityshop_intro' => input('param.entityshop_intro'),
                'entityshop_sort' => intval(input('param.entityshop_sort')),
                'entityshop_state' => input('param.entityshop_state'),
                'entityshop_addtime' => TIMESTAMP,
            );

            $entityshop_validate = ds_validate('entityshop');
            if (!$entityshop_validate->scene('add')->check($data_entityshop)) {
                $this->error($entityshop_validate->getError());
            }

            if ($_FILES['entityshop_pic']['name'] != '') {
                $file_name = date('YmdHis') . rand(10000, 99999) . '.png';
                $res = ds_upload_pic(DIR_HOME . DIRECTORY_SEPARATOR . 'entityshop', 'entityshop_pic', $file_name);
                if ($res['code']) {
                    $file_name = $res['data']['file_name'];
                    $data_entityshop['entityshop_pic'] = $file_name;
                } else {
                    $this->error($res['msg']);
                }
            }

            $entityshop_id = $entityshop_model->addEntityshop($data_entityshop);

            if ($entityshop_id > 0) {
                $this->log(lang('ds_add') . lang('ds_entityshop') . '[ID' . $entityshop_id . ']', 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    public function edit() {
        $entityshop_id = intval(input('param.entityshop_id'));
        if ($entityshop_id < 0) {
            ds_json_encode(10000, lang('param_error'));
        }
        $entityshop_model = model('entityshop');
        $condition = array();
        $condition[] = array('entityshop_id', '=', $entityshop_id);
        $entityshop = $entityshop_model->getOneEntityshop($condition);
        if (!request()->isPost()) {
            View::assign('entityshop', $entityshop);
            View::assign('baidu_ak', config('ds_config.baidu_ak'));
            $this->setAdminCurItem('edit');
            return View::fetch('form');
        } else {
            $data_entityshop = array(
                'entityshop_name' => input('param.entityshop_name'),
                'entityshop_linkname' => input('param.entityshop_linkname'),
                'entityshop_phone' => input('param.entityshop_phone'),
                'entityshop_hours' => input('param.entityshop_hours'),
                'region_id' => input('param.region_id'),
                'area_info' => input('param.area_info'),
                'entityshop_address' => input('param.entityshop_address'),
                'entityshop_longitude' => input('param.entityshop_longitude'),
                'entityshop_latitude' => input('param.entityshop_latitude'),
                'entityshop_intro' => input('param.entityshop_intro'),
                'entityshop_sort' => intval(input('param.entityshop_sort')),
                'entityshop_state' => input('param.entityshop_state'),
            );

            $entityshop_validate = ds_validate('entityshop');
            if (!$entityshop_validate->scene('edit')->check($data_entityshop)) {
                $this->error($entityshop_validate->getError());
            }

            //上传图片
            if ($_FILES['entityshop_pic']['name'] != '') {
                $file_name = date('YmdHis') . rand(10000, 99999) . '.png';
                $res = ds_upload_pic(DIR_HOME . DIRECTORY_SEPARATOR . 'entityshop', 'entityshop_pic', $file_name);
                if ($res['code']) {
                    $file_name = $res['data']['file_name'];
                    $data_entityshop['entityshop_pic'] = $file_name;
                    //删除原有友情链接图片
                    @unlink($upload_file . DIRECTORY_SEPARATOR . $entityshop['entityshop_pic']);
                } else {
                    $this->error($res['msg']);
                }
            }
            $entityshop_model->editEntityshop($condition, $data_entityshop);
            $this->log(lang('ds_edit') . lang('ds_entityshop') . '[ID' . $entityshop_id . ']', 1);
            $this->success(lang('ds_common_save_succ'));
        }
    }

    public function drop() {
        $entityshop_id = intval(input('param.entityshop_id'));
        if (empty($entityshop_id)) {
            $this->error(lang('param_error'));
        }
        $condition = array();
        $condition[] = array('entityshop_id', '=', $entityshop_id);
        $result = model('entityshop')->delEntityshop($condition);
        if ($result) {
            ds_json_encode(10000, lang('ds_common_op_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
    }

    public function ajax() {
        switch (input('get.branch')) {
            case 'entityshop':
                $entityshop_model = model('entityshop');
                $entityshop_id = intval(input('get.id'));
                $condition = array();
                $condition[] = array('entityshop_id', '=', $entityshop_id);
                $update_array = array();
                $update_array[input('get.column')] = trim(input('get.value'));
                $result = $entityshop_model->editEntityshop($condition, $update_array);
                break;
        }
        if ($result >= 0) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => lang('ds_manage'),
                'url' => url('Entityshop/index')
            ),
        );

        if (request()->action() == 'add' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'add',
                'text' => lang('ds_add'),
                'url' => url('Entityshop/add')
            );
        }
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => lang('ds_edit'),
                'url' => 'javascript:void(0)'
            );
        }
        return $menu_array;
    }

}
