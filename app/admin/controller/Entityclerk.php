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
 * 门店店员 控制器
 */
/*
  CREATE TABLE IF NOT EXISTS `ds_entityclerk` (
  `entityclerk_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `member_id` int(11) NOT NULL COMMENT '绑定会员名',
  `entityshop_id` int(11) NOT NULL COMMENT '门店ID',
  `entityclerk_name` varchar(20) DEFAULT '' COMMENT '店员姓名',
  `entityclerk_phone` varchar(20) DEFAULT '' COMMENT '店员联系电话',
  `entityclerk_state` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '1正常  2禁用',
  `entityclerk_addtime` int(11) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`entityclerk_id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='门店店员表' AUTO_INCREMENT=1 ;
 */

class Entityclerk extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/entityclerk.lang.php');
    }

    public function index() {
        $entityclerk_model = model('entityclerk');
        $condition = array();
        $entityclerk_like = input('param.entityclerk_like');
        if(!empty($entityclerk_like)){
            $condition[]  = array('entityclerk_name|entityclerk_phone','like', "%" . $entityclerk_like . "%");
        }
        $entityshop_id = input('param.entityshop_id');
        if(!empty($entityshop_id)){
            $condition[]  = array('entityshop_id','=',$entityshop_id);
        }
        $entityclerk_list = $entityclerk_model->getEntityclerkList($condition, 10);
        View::assign('entityclerk_list', $entityclerk_list);
        View::assign('show_page', $entityclerk_model->page_info->render());
        //获取门店列表
        $entityshop_model = model('entityshop');
        $entityshop_list = $entityshop_model->getEntityshopList(array());
        View::assign('entityshop_list', $entityshop_list);
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    public function add() {
        $entityclerk_model = model('entityclerk');
        if (!request()->isPost()) {
            $entityclerk = array(
                'entityclerk_state' => 1,
            );
            View::assign('entityclerk', $entityclerk);
            //获取门店列表
            $entityshop_model = model('entityshop');
            $entityshop_list = $entityshop_model->getEntityshopList(array());
            View::assign('entityshop_list', $entityshop_list);
            $this->setAdminCurItem('add');
            return View::fetch('form');
        } else {
            $member_id = intval(input('param.member_id'));
            $member_info = $entityclerk_model->getOneEntityclerk(array('member_id'=>$member_id));
            if(!empty($member_info)){
                $this->error(lang('param_error'));
            }
            
            $data_entityclerk = array(
                'member_id' => $member_id,
                'member_name' => input('param.member_name'),
                'entityshop_id' => input('param.entityshop_id'),
                'entityclerk_name' => input('param.entityclerk_name'),
                'entityclerk_phone' => input('param.entityclerk_phone'),
                'entityclerk_state' => input('param.entityclerk_state'),
                'entityclerk_addtime' => TIMESTAMP,
            );

            $entityclerk_validate = ds_validate('entityclerk');
            if (!$entityclerk_validate->scene('add')->check($data_entityclerk)) {
                $this->error($entityclerk_validate->getError());
            }


            $entityclerk_id = $entityclerk_model->addEntityclerk($data_entityclerk);

            if ($entityclerk_id > 0) {
                $this->log(lang('ds_add') . lang('ds_entityclerk') . '[ID' . $entityclerk_id . ']', 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    public function edit() {
        $entityclerk_id = intval(input('param.entityclerk_id'));
        if ($entityclerk_id < 0) {
            ds_json_encode(10000, lang('param_error'));
        }
        $entityclerk_model = model('entityclerk');
        $condition = array();
        $condition[] = array('entityclerk_id','=',$entityclerk_id);
        $entityclerk = $entityclerk_model->getOneEntityclerk($condition);
        if (!request()->isPost()) {
            View::assign('entityclerk', $entityclerk);
            //获取门店列表
            $entityshop_model = model('entityshop');
            $entityshop_list = $entityshop_model->getEntityshopList(array());
            View::assign('entityshop_list', $entityshop_list);
            $this->setAdminCurItem('edit');
            return View::fetch('form');
        } else {
            $data_entityclerk = array(
                'entityshop_id' => input('param.entityshop_id'),
                'entityclerk_name' => input('param.entityclerk_name'),
                'entityclerk_phone' => input('param.entityclerk_phone'),
                'entityclerk_state' => input('param.entityclerk_state'),
            );

            $entityclerk_validate = ds_validate('entityclerk');
            if (!$entityclerk_validate->scene('edit')->check($data_entityclerk)) {
                $this->error($entityclerk_validate->getError());
            }

            $entityclerk_model->editEntityclerk($condition, $data_entityclerk);
            $this->log(lang('ds_edit') . lang('ds_entityclerk') . '[ID' . $entityclerk_id . ']', 1);
            $this->success(lang('ds_common_save_succ'));
        }
    }
    public function drop() {
        $entityclerk_id = intval(input('param.entityclerk_id'));
        if (empty($entityclerk_id)) {
            $this->error(lang('param_error'));
        }
        $condition = array();
        $condition[] = array('entityclerk_id','=',$entityclerk_id);
        $result = model('entityclerk')->delEntityclerk($condition);
        if ($result) {
            ds_json_encode(10000, lang('ds_common_op_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
    }
    //取得会员信息
    public function checkmember() {
        $name = input('post.name');
        if (!$name) {
            exit(json_encode(array('id' => 0)));
            die;
        }
        $obj_member = model('member');
        $member_info = $obj_member->getMemberInfo(array('member_name' => $name));
        if (is_array($member_info) && count($member_info) > 0) {
//            $entityclerk_model = model('entityclerk');
//            $member_info = $entityclerk_model->getOneEntityclerk(array('member_id'=>$member_info['member_id']));
            exit(json_encode(array('id' => $member_info['member_id'], 'name' => $member_info['member_name'])));
        } else {
            exit(json_encode(array('id' => 0)));
        }
    }
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => lang('ds_manage'),
                'url' => url('Entityclerk/index')
            ),
        );

        if (request()->action() == 'add' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'add',
                'text' => lang('ds_add'),
                'url' => url('Entityclerk/add')
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
