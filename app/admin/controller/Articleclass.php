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
class Articleclass extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/articleclass.lang.php');
    }

    /**
     * 文章管理
     */
    public function index() {
        $articleclass_model = model('articleclass');
        /**
         * 父ID
         */
        $parent_id = input('param.ac_parent_id') ? intval(input('param.ac_parent_id')) : 0;
        /**
         * 列表
         */
        $tmp_list = $articleclass_model->getTreeClassList(2);
        $class_list = array();
        if (is_array($tmp_list)) {
            foreach ($tmp_list as $k => $v) {
                if ($v['ac_parent_id'] == $parent_id) {
                    /**
                     * 判断是否有子类
                     */
                    $v['have_child'] = 0;
                    if (isset($tmp_list[$k + 1]['deep']) && $tmp_list[$k + 1]['deep'] > $v['deep']) {
                        $v['have_child'] = 1;
                    }
                    $class_list[] = $v;
                }
            }
        }
        if (input('param.ajax') == '1') {
            /**
             * 转码
             */
            $output = json_encode($class_list);
            print_r($output);
            exit;
        } else {
            View::assign('class_list', $class_list);
            $this->setAdminCurItem('index');
            return View::fetch('article_class_index');
        }
    }

    /**
     * 文章分类 新增
     */
    public function article_class_add() {
        $articleclass_model = model('articleclass');
        if (request()->isPost()) {
            /**
             * 验证
             */
            $data = [
                'ac_name' => input('param.ac_name'),
                'ac_sort' => input('param.ac_sort')
            ];
            $article_validate = ds_validate('article');
            if (!$article_validate->scene('article_class_add')->check($data)) {
                $this->error($article_validate->getError());
            } else {

                $insert_array = array();
                $insert_array['ac_name'] = trim(input('param.ac_name'));
                $insert_array['ac_parent_id'] = intval(input('param.ac_parent_id'));
                $insert_array['ac_sort'] = trim(input('param.ac_sort'));

                $result = $articleclass_model->addArticleclass($insert_array);
                if ($result) {
                    $this->log(lang('ds_add') . lang('article_class_index_class') . '[' . input('ac_name') . ']', 1);
                    dsLayerOpenSuccess(lang('article_class_add_succ'));
                } else {
                    $this->error(lang('article_class_add_fail'));
                }
            }
        } else {
            /**
             * 父类列表，只取到第三级
             */
            $parent_list = $articleclass_model->getTreeClassList(1);
            if (is_array($parent_list)) {
                foreach ($parent_list as $k => $v) {
                    $parent_list[$k]['ac_name'] = str_repeat("&nbsp;", $v['deep'] * 2) . $v['ac_name'];
                }
            }
            View::assign('ac_parent_id', intval(input('param.ac_parent_id')));
            View::assign('parent_list', $parent_list);
            return View::fetch('article_class_edit');
        }
    }

    /**
     * 文章分类编辑
     */
    public function article_class_edit() {
        $articleclass_model = model('articleclass');
        
        $ac_id = intval(input('param.ac_id'));
        
        if (request()->isPost()) {
            /**
             * 验证
             */
            $data = [
                'ac_name' => input('param.ac_name'),
                'ac_sort' => input('param.ac_sort')
            ];
            $article_validate = ds_validate('article');
            if (!$article_validate->scene('article_class_edit')->check($data)) {
                $this->error($article_validate->getError());
            } else {

                $update_array = array();
                $update_array['ac_name'] = trim(input('post.ac_name'));
                $update_array['ac_sort'] = trim(input('post.ac_sort'));

                $result = $articleclass_model->editArticleclass($update_array,$ac_id);
                if ($result>=0) {
                    $this->log(lang('ds_edit') . lang('article_class_index_class') . '[' . input('post.ac_name') . ']', 1);
                    dsLayerOpenSuccess(lang('ds_common_op_succ'));
                } else {
                    $this->error(lang('ds_common_op_fail'));
                }
            }
        } else {
            $class_array = $articleclass_model->getOneArticleclass($ac_id);
            if (empty($class_array)) {
                $this->error(lang('param_error'));
            }

            View::assign('class_array', $class_array);
            return View::fetch('article_class_edit');
        }
    }

    /**
     * 删除分类
     */
    public function article_class_del() {
        $articleclass_model = model('articleclass');
        
        $ac_id = input('param.ac_id');
        $ac_id_array = ds_delete_param($ac_id);
        if ($ac_id_array === FALSE) {
            ds_json_encode('10001', lang('param_error'));
        }
        

            $del_array = $articleclass_model->getChildClass($ac_id_array);
        if (is_array($del_array)) {
            foreach ($del_array as $k => $v) {
                $articleclass_model->delArticleclass($v['ac_id']);
            }
        }
        $this->log(lang('ds_add') . lang('article_class_index_class') . '[ID:' . $ac_id . ']', 1);
        ds_json_encode(10000, lang('ds_common_del_succ'));
    }

    /**
     * ajax操作
     */
    public function ajax() {
        switch (input('param.branch')) {
            /**
             * 分类：验证是否有重复的名称
             */
            case 'article_class_name':
                $articleclass_model = model('articleclass');
                $class_array = $articleclass_model->getOneArticleclass(intval(input('param.id')));
                $condition = array();
                $condition[] = array('ac_name','=',trim(input('param.value')));
                $condition[] = array('ac_parent_id','=',$class_array['ac_parent_id']);
                $condition[] = array('ac_id','<>',intval(input('param.id')));
                $class_list = $articleclass_model->getArticleclassList($condition);
                if (empty($class_list)) {
                    $update_array = array();
                    $update_array['ac_name'] = trim(input('param.value'));
                    $articleclass_model->editArticleclass($update_array,input('param.id'));
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
            /**
             * 分类： 排序 显示 设置
             */
            case 'article_class_sort':
                $articleclass_model = model('articleclass');
                $update_array = array();
                $update_array[input('param.column')] = trim(input('param.value'));
                $result = $articleclass_model->editArticleclass($update_array,intval(input('param.id')));
                echo 'true';
                exit;
                break;
            /**
             * 分类：添加、修改操作中 检测类别名称是否有重复
             */
            case 'check_class_name':
                $articleclass_model = model('articleclass');
                $condition[] = array('ac_name','=',trim(input('param.ac_name')));
//                $condition[] = array('ac_parent_id','=',intval(input('param.ac_parent_id')));
                $condition[] = array('ac_id','<>',intval(input('param.ac_id')));
                $class_list = $articleclass_model->getArticleclassList($condition);
                if (empty($class_list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }

    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' =>lang('ds_manage'),
                'url' => url('Articleclass/index')
            ),
            array(
                'name' => 'add',
                'text' => lang('ds_new'),
                'url' => "javascript:dsLayerOpen('" . url('Articleclass/article_class_add') ."','".lang('article_class_add')."')"
            )
        );
        return $menu_array;
    }

}
