<?php

/*
 * 规格管理
 */

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
class Spec extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/spec.lang.php');
    }

    public function index() {
         /**
         * 查询条件
         */
        $where = array();
        
        $sp_name = trim(input('param.sp_name'));
        if ($sp_name != '') {
            $where[] = array('sp_name','like', '%' . $sp_name . '%');
        }
        $gc_name = trim(input('param.gc_name'));
        if ($gc_name != '') {
            $where[] = array('gc_name','like', '%' . $gc_name . '%');
        }
        
        $spec_model = model('spec');
        $spec_list	= $spec_model->getSpecList($where, 10);
        View::assign('spec_list', $spec_list);
        View::assign('show_page', $spec_model->page_info->render());
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    public function spec_add() {
        if (!(request()->isPost())) {
            $spec = [
                'gc_id' => 0,
            ];
            View::assign('spec', $spec);
            $gc_list = model('goodsclass')->getGoodsclassListByParentId(0);
            View::assign('gc_list', $gc_list);
            return View::fetch('spec_form');
        } else {
            $data = array(
                'sp_name' => input('post.sp_name'),
                'sp_sort' => input('post.sp_sort'),
                'gc_id' => input('post.gc_id'),
                'gc_name' => input('post.gc_name'),
            );

            $spec_validate = ds_validate('spec');
            if (!$spec_validate->scene('spec_add')->check($data)) {
                $this->error($spec_validate->getError());
            }
            //验证数据  END
            $spec_model= model('spec');
            $result=$spec_model->addSpec($data);
            if ($result) {
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
            } else {
                $this->error(lang('error'));
            }
        }
    }

    public function spec_edit() {
        //注：pathinfo地址参数不能通过get方法获取，查看“获取PARAM变量”
        $sp_id = input('param.sp_id');
        if (empty($sp_id)) {
            $this->error(lang('param_error'));
        }
        if (!request()->isPost()) {
            $spec_model= model('spec');
            $spec=$spec_model->getSpecInfo($sp_id);
            View::assign('spec', $spec);
            $gc_list = model('goodsclass')->getGoodsclassListByParentId(0);
            View::assign('gc_list', $gc_list);
            $spec_model=model('spec');
            $specvalue_list = $spec_model->getSpecvalueList(array('sp_id' => $sp_id));
            View::assign('specvalue_list', $specvalue_list);
            return View::fetch('spec_form');
        } else {
            $data = array(
                'sp_name' => input('post.sp_name'),
                'sp_sort' => input('post.sp_sort'),
                'gc_id' => input('post.gc_id'),
                'gc_name' => input('post.gc_name'),
            );
            //验证数据  BEGIN
            $spec_validate = ds_validate('spec');
            if (!$spec_validate->scene('spec_edit')->check($data)) {
                $this->error($spec_validate->getError());
            }
            //验证数据  END
            
            $spec_model=model('spec');
            //更新规格值表
            $spec_value = input('post.spec_value/a');
            $spec_array = array();
            // 要删除的规格值id
            $del_array = array();
            if (!empty(input('post.spec_del/a'))) {
                $del_array = input('post.spec_del/a');
            }
            if (!empty($spec_value) && is_array($spec_value)) {
                foreach ($spec_value as $key => $val) {

                    if (isset($val['form_submit']) && ((is_array($del_array) && !in_array(intval($key), $del_array)) || empty($del_array))) {  // 规格值已修改
                        $update = array();
                        $update['spvalue_name'] = $val['name'];
                        $update['spvalue_sort'] = intval($val['sort']);
                        $spec_model->editSpecvalue($update,array('spvalue_id' => intval($key)));

                        $spec_array[] = $val['name'];
                    } else if (!isset($val['form_submit'])) {

                        $insert = array();
                        $insert['spvalue_name'] = $val['name'];
                        $insert['sp_id'] = $sp_id;
                        $insert['gc_id'] = input('post.gc_id');
                        $insert['spvalue_sort'] = intval($val['sort']);
                        $spec_model->addSpecvalue($insert);

                        $spec_array[] = $val['name'];
                    }
                }
                // 删除规格值
                if ($del_array) {
                    foreach ($del_array as $key => $value) {
                        $spec_model->delSpecvalue(array('spvalue_id' => intval($value)));
                    }
                }
            }
            
            
            $spec_model= model('spec');
            $condition=array();
            $condition[]=array('sp_id','=',$sp_id);
            $result=$spec_model->editSpec($data, $condition);
            if ($result>=0) {
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
            } else {
                $this->error(lang('ds_common_op_fail'));
            }
        }
    }

    public function spec_drop() {
        //注：pathinfo地址参数不能通过get方法获取，查看“获取PARAM变量”
        $sp_id = input('param.sp_id');
        if (empty($sp_id)) {
            $this->error(lang('param_error'));
        }
        $spec_model = model('spec');
        $result=$spec_model->delSpec(array('sp_id' => $sp_id));
        if ($result) {
            ds_json_encode(10000, lang('ds_common_del_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_del_fail'));
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
                'url' => url('Spec/index')
            ),
            array(
                'name' => 'spec_add',
                'text' => '新增规格',
                'url' => "javascript:dsLayerOpen('".url('Spec/spec_add')."','新增规格')"
            ),
        );
        return $menu_array;
    }
}

?>
