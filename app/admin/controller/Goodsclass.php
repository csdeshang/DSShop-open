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
class Goodsclass extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/goodsclass.lang.php');
    }

    /**
     * 分类管理
     */
    public function goods_class() {
        $goodsclass_model = model('goodsclass');
        //父ID
        $parent_id = input('param.gc_parent_id') ? intval(input('param.gc_parent_id')) : 0;

        //列表
        $tmp_list = $goodsclass_model->getTreeClassList(3);
        $class_list = array();
        if (is_array($tmp_list)) {
            foreach ($tmp_list as $k => $v) {
                if ($v['gc_parent_id'] == $parent_id) {
                    //判断是否有子类
                    if (isset($tmp_list[$k + 1]['deep']) && $tmp_list[$k + 1]['deep'] > $v['deep']) {
                        $v['have_child'] = 1;
                    }
                    $class_list[] = $v;
                }
            }
        }

        if (input('param.ajax') == '1') {
            $output = json_encode($class_list);
            echo $output;
            exit;
        } else {
            View::assign('class_list', $class_list);
            $this->setAdminCurItem('goods_class');
            return View::fetch('goods_class');
        }
    }

    /**
     * 商品分类添加
     */
    public function goods_class_add() {
        $goodsclass_model = model('goodsclass');
        if (!request()->isPost()) {
            //父类列表，只取到第二级
            $parent_list = $goodsclass_model->getTreeClassList(2);
            $gc_list = array();
            if (is_array($parent_list)) {
                foreach ($parent_list as $k => $v) {
                    $parent_list[$k]['gc_name'] = str_repeat("&nbsp;", $v['deep'] * 2) . $v['gc_name'];
                    if ($v['deep'] == 1)
                        $gc_list[$k] = $v;
                }
            }
            View::assign('gc_list', $gc_list);
            //类型列表
            $type_model = model('type');
            $type_list = $type_model->getTypeList(array(), '', 'type_id,type_name,class_id,class_name');
            $t_list = array();
            if (is_array($type_list) && !empty($type_list)) {
                foreach ($type_list as $k => $val) {
                    $t_list[$val['class_id']]['type'][$k] = $val;
                    $t_list[$val['class_id']]['name'] = $val['class_name'] == '' ? lang('ds_default') : $val['class_name'];
                }
            }

            ksort($t_list);

            View::assign('type_list', $t_list);
            View::assign('gc_parent_id', input('get.gc_parent_id'));
            View::assign('parent_list', $parent_list);
            $this->setAdminCurItem('goods_class_add');
            return View::fetch('goods_class_add');
        } else {

            $insert_array = array();
            $insert_array['gc_name'] = input('post.gc_name');
            $insert_array['type_id'] = intval(input('post.t_id'));
            $insert_array['type_name'] = trim(input('post.t_name'));
            $insert_array['gc_parent_id'] = intval(input('post.gc_parent_id'));
            $insert_array['gc_sort'] = intval(input('post.gc_sort'));
            $insert_array['gc_virtual'] = intval(input('post.gc_virtual'));

            //验证数据  BEGIN
            if (!empty($_FILES['pic']['name'])) {//上传图片
                $res = ds_upload_pic(ATTACH_COMMON, 'pic');
                if ($res['code']) {
                    $file_name = $res['data']['file_name'];
                    $insert_array['gc_image'] = $file_name;
                } else {
                    $this->error($res['msg']);
                }
            }
            $goods_validate = ds_validate('goods');
            if (!$goods_validate->scene('goods_class_add')->check($insert_array)) {
                $this->error($goods_validate->getError());
            }

            $result = $goodsclass_model->addGoodsclass($insert_array);
            if ($result) {
                $this->log(lang('ds_add') . lang('goods_class_index_class') . '[' . input('post.gc_name') . ']', 1);
                $this->success(lang('ds_common_save_succ'), (string) url('Goodsclass/goods_class'));
            } else {
                $this->log(lang('ds_add') . lang('goods_class_index_class') . '[' . input('post.gc_name') . ']', 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 编辑
     */
    public function goods_class_edit() {
        $goodsclass_model = model('goodsclass');
        $gc_id = intval(input('param.gc_id'));

        if (!request()->isPost()) {
            $class_array = $goodsclass_model->getGoodsclassInfoById($gc_id);

            if (empty($class_array)) {
                $this->error(lang('goods_class_batch_edit_paramerror'));
            }

            //类型列表
            $type_model = model('type');
            $type_list = $type_model->getTypeList(array(), '', 'type_id,type_name,class_id,class_name');
            $t_list = array();
            if (is_array($type_list) && !empty($type_list)) {
                foreach ($type_list as $k => $val) {
                    $t_list[$val['class_id']]['type'][$k] = $val;
                    $t_list[$val['class_id']]['name'] = $val['class_name'] == '' ? lang('ds_default') : $val['class_name'];
                }
            }
            ksort($t_list);
            //父类列表，只取到第二级
            $parent_list = $goodsclass_model->getTreeClassList(2);
            if (is_array($parent_list)) {
                foreach ($parent_list as $k => $v) {
                    $parent_list[$k]['gc_name'] = str_repeat("&nbsp;", $v['deep'] * 2) . $v['gc_name'];
                }
            }
            View::assign('parent_list', $parent_list);
            // 一级分类列表
            $gc_list = model('goodsclass')->getGoodsclassListByParentId(0);
            View::assign('gc_list', $gc_list);


            $class_array['pic'] = ds_get_pic(ATTACH_COMMON, $class_array['gc_image']);


            View::assign('type_list', $t_list);
            View::assign('class_array', $class_array);
            $this->setAdminCurItem('goods_class_edit');
            return View::fetch('goods_class_edit');
        } else {


            $update_array = array();
            $update_array['gc_name'] = input('post.gc_name');
            $update_array['type_id'] = intval(input('post.t_id'));
            $update_array['type_name'] = trim(input('post.t_name'));
            $update_array['gc_sort'] = intval(input('post.gc_sort'));
            $update_array['gc_virtual'] = intval(input('post.gc_virtual'));
            $update_array['gc_parent_id'] = intval(input('post.gc_parent_id'));

            if (!empty($_FILES['pic']['name'])) {//上传图片
                $res = ds_upload_pic(ATTACH_COMMON, 'pic');
                if ($res['code']) {
                    $file_name = $res['data']['file_name'];
                    $update_array['gc_image'] = $file_name;
                } else {
                    $this->error($res['msg']);
                }
            }
            //验证数据  BEGIN
            $goods_validate = ds_validate('goods');
            if (!$goods_validate->scene('goods_class_edit')->check($update_array)) {
                $this->error($goods_validate->getError());
            }
            //验证数据  END
            $parent_class=$goodsclass_model->getGoodsclassInfoById($update_array['gc_parent_id']);
            if($parent_class){
                if($parent_class['gc_parent_id']==$gc_id){
                    $this->error('父分类的父分类不能等于自身');
                }
            }
            if($update_array['gc_parent_id']==$gc_id){
                $this->error('父分类不能等于自身');
            }
            // 更新分类信息
            $where = array('gc_id' => $gc_id);
            $result = $goodsclass_model->editGoodsclass($update_array, $where);
            if ($result<0) {
                $this->log(lang('ds_edit').lang('goods_class_index_class') . '[' . input('post.gc_name') . ']', 0);
                $this->error(lang('goods_class_batch_edit_fail'));
            }



            // 检测是否需要关联自己操作，统一查询子分类
            if (input('post.t_associated') == '1' || input('post.t_gc_virtual') == '1') {
                $gc_id_list = $goodsclass_model->getChildClass($gc_id);
                $gc_ids = array();
                if (is_array($gc_id_list) && !empty($gc_id_list)) {
                    foreach ($gc_id_list as $val) {
                        $gc_ids[] = $val['gc_id'];
                    }
                }
            }


            // 更新该分类下子分类的所有类型
            if (input('post.t_associated') == '1' && !empty($gc_ids)) {
                $where = array();
                $where[] = array('gc_id','in', $gc_ids);
                $update = array();
                $update['type_id'] = intval(input('post.t_id'));
                $update['type_name'] = trim(input('post.t_name'));
                $goodsclass_model->editGoodsclass($update, $where);
            }

            // 虚拟商品
            if (input('post.t_gc_virtual') == '1' && !empty($gc_ids)) {
                $goodsclass_model->editGoodsclass(array('gc_virtual' => $update_array['gc_virtual']), array(array('gc_id','in', $gc_ids)));
            }


            $this->log(lang('ds_edit').lang('goods_class_index_class') . '[' . input('post.gc_name') . ']', 1);
            $this->success(lang('goods_class_batch_edit_ok'), url('Goodsclass/goods_class'));
        }
    }

    /**
     * 删除分类
     */
    public function goods_class_del() {
        $gc_id = input('param.gc_id');
        $gc_id_array = ds_delete_param($gc_id);
        if ($gc_id_array === FALSE) {
            $this->log(lang('ds_del').lang('goods_class_index_class') . '[ID:' . $gc_id . ']', 0);
            ds_json_encode('10001', lang('param_error'));
        }
        $goodsclass_model = model('goodsclass');
        //删除分类
        $goodsclass_model->delGoodsclassByGcIdString($gc_id);
        $this->log(lang('ds_del') . lang('goods_class_index_class') . '[ID:' . $gc_id . ']', 1);
        ds_json_encode('10000', lang('ds_common_del_succ'));
    }

    /**
     * tag列表
     */
    public function tag() {

        /**
         * 处理商品分类
         */
        $choose_gcid = ($t = intval(input('param.choose_gcid'))) > 0 ? $t : 0;
        $gccache_arr = model('goodsclass')->getGoodsclassCache($choose_gcid, 3);
        View::assign('gc_json', json_encode($gccache_arr['showclass']));
        View::assign('gc_choose_json', json_encode($gccache_arr['choose_gcid']));

        $classtag_model = model('goodsclasstag');

        if (!request()->isPost()) {
            $where = array();
            if ($choose_gcid > 0) {
                $where[] = array('gc_id_' . ($gccache_arr['showclass'][$choose_gcid]['depth']),'=',$choose_gcid);
            }
            $tag_list = $classtag_model->getGoodsclasstagList($where, 10);
            View::assign('tag_list', $tag_list);
            View::assign('show_page', $classtag_model->page_info->render());
            $this->setAdminCurItem('tag');
            return View::fetch('goods_class_tag');
        } else {
            //删除
            if (input('post.submit_type') == 'del') {
                $tag_id_array = input('post.tag_id/a');
                if (is_array($tag_id_array) && !empty($tag_id_array)) {
                    //删除TAG
                    $classtag_model->delGoodsclasstagByIds(implode(',', $tag_id_array));
                    $this->log(lang('ds_del') . 'tag[ID:' . implode(',', $tag_id_array) . ']', 1);
                    $this->success(lang('ds_common_del_succ'));
                } else {
                    $this->log(lang('ds_del') . 'tag', 0);
                    $this->error(lang('ds_common_del_fail'));
                }
            }
        }
    }

    /**
     * 重置TAG
     */
    public function tag_reset() {
        //实例化模型
        $goodsclass_model = model('goodsclass');
        $classtag_model = model('goodsclasstag');

        //清空TAG
        $return = $classtag_model->clearGoodsclasstag();
        // if (!$return) {
            // $this->error(lang('goods_class_reset_tag_fail'), url('Goodsclass/tag'));
        // }

        //商品分类
        $goods_class = $goodsclass_model->getTreeClassList(3);
        //格式化分类。组成三维数组
        if (is_array($goods_class) and !empty($goods_class)) {
            $goods_class_array = array();
            foreach ($goods_class as $val) {
                //一级分类
                if ($val['gc_parent_id'] == 0) {
                    $goods_class_array[$val['gc_id']]['gc_name'] = $val['gc_name'];
                    $goods_class_array[$val['gc_id']]['gc_id'] = $val['gc_id'];
                    $goods_class_array[$val['gc_id']]['type_id'] = $val['type_id'];
                } else {
                    //二级分类
                    if (isset($goods_class_array[$val['gc_parent_id']])) {
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_name'] = $val['gc_name'];
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_id'] = $val['gc_id'];
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_parent_id'] = $val['gc_parent_id'];
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['type_id'] = $val['type_id'];
                    } else {
                        foreach ($goods_class_array as $v) {
                            //三级分类
                            if (isset($v['sub_class'][$val['gc_parent_id']])) {
                                $goods_class_array[$v['sub_class'][$val['gc_parent_id']]['gc_parent_id']]['sub_class'][$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_name'] = $val['gc_name'];
                                $goods_class_array[$v['sub_class'][$val['gc_parent_id']]['gc_parent_id']]['sub_class'][$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_id'] = $val['gc_id'];
                                $goods_class_array[$v['sub_class'][$val['gc_parent_id']]['gc_parent_id']]['sub_class'][$val['gc_parent_id']]['sub_class'][$val['gc_id']]['type_id'] = $val['type_id'];
                            }
                        }
                    }
                }
            }

            $return = $classtag_model->addGoodsclasstag($goods_class_array);

            // if ($return) {
                $this->log(lang('ds_reset') . 'tag', 1);
                $this->success(lang('ds_common_op_succ'), url('Goodsclass/tag'));
            // } else {
                // $this->log(lang('ds_reset') . 'tag', 0);
                // $this->error(lang('ds_common_op_fail'), url('Goodsclass/tag'));
            // }
        } else {
            $this->log(lang('ds_reset') . 'tag', 0);
            $this->error(lang('goods_class_reset_tag_fail_no_class'), url('Goodsclass/tag'));
        }
    }

    /**
     * 更新TAG名称
     */
    public function tag_update() {
        $goodsclass_model = model('goodsclass');
        $classtag_model = model('goodsclasstag');

        //需要更新的TAG列表
        $tag_list = $classtag_model->getGoodsclasstagList(array(), '', 'gctag_id,gc_id_1,gc_id_2,gc_id_3');
        if (is_array($tag_list) && !empty($tag_list)) {
            foreach ($tag_list as $val) {
                //查询分类信息
                $in_gc_id = array();
                if ($val['gc_id_1'] != '0') {
                    $in_gc_id[] = $val['gc_id_1'];
                }
                if ($val['gc_id_2'] != '0') {
                    $in_gc_id[] = $val['gc_id_2'];
                }
                if ($val['gc_id_3'] != '0') {
                    $in_gc_id[] = $val['gc_id_3'];
                }
                $gc_list = $goodsclass_model->getGoodsclassListByIds($in_gc_id);

                //更新TAG信息
                $update_tag = array();
                if (isset($gc_list['0']['gc_id']) && $gc_list['0']['gc_id'] != '0') {
                    $update_tag['gc_id_1'] = $gc_list['0']['gc_id'];
                    if(!isset($update_tag['gctag_name'])){
                      $update_tag['gctag_name']='';
                    }
                    $update_tag['gctag_name'] .= $gc_list['0']['gc_name'];
                }
                if (isset($gc_list['1']['gc_id']) && $gc_list['1']['gc_id'] != '0') {
                    $update_tag['gc_id_2'] = $gc_list['1']['gc_id'];
                    if(!isset($update_tag['gctag_name'])){
                      $update_tag['gctag_name']='';
                    }
                    $update_tag['gctag_name'] .= "&nbsp;&gt;&nbsp;" . $gc_list['1']['gc_name'];
                }
                if (isset($gc_list['2']['gc_id']) && $gc_list['2']['gc_id'] != '0') {
                    $update_tag['gc_id_3'] = $gc_list['2']['gc_id'];
                    if(!isset($update_tag['gctag_name'])){
                      $update_tag['gctag_name']='';
                    }
                    $update_tag['gctag_name'] .= "&nbsp;&gt;&nbsp;" . $gc_list['2']['gc_name'];
                }
                unset($gc_list);
                $return = $classtag_model->editGoodsclasstag($update_tag,$val['gctag_id']);
                if (!$return) {
                    $this->log(lang('ds_update') . 'tag', 0);
                    $this->error(lang('ds_common_op_fail'), 'Goodsclass/tag');
                }
            }
            $this->log(lang('ds_update') . 'tag', 1);
            $this->success(lang('ds_common_op_succ'), 'Goodsclass/tag');
        } else {
            $this->log(lang('ds_update') . 'tag', 0);
            $this->error(lang('goods_class_update_tag_fail_no_class'), 'Goodsclass/tag');
        }
    }

    /**
     * 删除TAG
     */
    public function tag_del() {
        $id = intval(input('get.tag_id'));
        $classtag_model = model('goodsclasstag');
        if ($id > 0) {
            //删除TAG
            $classtag_model->delGoodsclasstagByIds($id);
            $this->log(lang('ds_del') . 'tag[ID:' . $id . ']', 1);
            ds_json_encode('10000', lang('ds_common_op_succ'));
        } else {
            $this->log(lang('ds_del') . 'tag[ID:' . $id . ']', 0);
            ds_json_encode('10001', lang('ds_common_op_fail'));
        }
    }

    /**
     * 分类导航
     */
    public function nav_edit() {
        $gc_id = input('param.gc_id');
        $goodsclass_model = model('goodsclass');
        $class_info = $goodsclass_model->getGoodsclassInfoById($gc_id);
        $goodsclassnav_model = model('goodsclassnav');
        $nav_info = $goodsclassnav_model->getGoodsclassnavInfoByGcId($gc_id);

        if (request()->isPost()) {
            $update = array();
            $update['gc_id'] = $gc_id;
            $update['goodscn_alias'] = input('post.goodscn_alias');
            $class_id_array = input('post.class_id/a');
            if(empty($class_id_array)){
            	$update['goodscn_classids'] = '';
            }else if (is_array($class_id_array) && !empty($class_id_array)) {
                $update['goodscn_classids'] = implode(',', $class_id_array);
            }
            $brand_id_array = input('post.brand_id/a');
            if(empty($brand_id_array)){
            	$update['goodscn_brandids'] = '';
            }else if (is_array($brand_id_array) && !empty($brand_id_array)) {
                $update['goodscn_brandids'] = implode(',', $brand_id_array);
            }
            $update['goodscn_adv1_link'] = input('post.goodscn_adv1_link');
            $update['goodscn_adv2_link'] = input('post.goodscn_adv2_link');
            if (!empty($_FILES['pic']['name'])) {//上传图片
                @unlink(BASE_UPLOAD_PATH . '/' . ATTACH_GOODS_CLASS . '/' . $nav_info['goodscn_pic']);
                $file_name = date('YmdHis') . rand(10000, 99999).'.png';

                $res=ds_upload_pic(ATTACH_GOODS_CLASS,'pic',$file_name);
                if($res['code']){
                    $file_name=$res['data']['file_name'];
                    $update['goodscn_pic'] = $file_name;
                }else{
                    $this->error($res['msg']);
                }
            }
            if (!empty($_FILES['adv1']['name'])) {//上传广告图片
                @unlink(BASE_UPLOAD_PATH . '/' . ATTACH_GOODS_CLASS . '/' . $nav_info['goodscn_adv1']);
                $file_name = date('YmdHis') . rand(10000, 99999).'.png';

                $res=ds_upload_pic(ATTACH_GOODS_CLASS,'adv1',$file_name);
                if($res['code']){
                    $file_name=$res['data']['file_name'];
                    $update['goodscn_adv1'] = $file_name;
                }else{
                    $this->error($res['msg']);
                }
            }
            if (!empty($_FILES['adv2']['name'])) {//上传广告图片
                @unlink(BASE_UPLOAD_PATH . '/' . ATTACH_GOODS_CLASS . '/' . $nav_info['goodscn_adv2']);
                $file_name = date('YmdHis') . rand(10000, 99999).'.png';

                $res=ds_upload_pic(ATTACH_GOODS_CLASS,'adv2',$file_name);
                if($res['code']){
                    $file_name=$res['data']['file_name'];
                    $update['goodscn_adv2'] = $file_name;
                }else{
                    $this->error($res['msg']);
                }
            }
            if (empty($nav_info)) {
                $result = $goodsclassnav_model->addGoodsclassnav($update);
            } else {
                $result = $goodsclassnav_model->editGoodsclassnav($update, $gc_id);
            }
            if ($result) {
                $this->log('编辑分类导航，' . $class_info['gc_name'], 1);
                $this->success(lang('ds_common_op_succ'));
            } else {
                $this->log('编辑分类导航，' . $class_info['gc_name'], 0);
                $this->success(lang('ds_common_op_succ'));
            }
        }else {
            if (isset($nav_info)) {
                $nav_info['goodscn_pic'] = ds_get_pic(ATTACH_GOODS_CLASS , $nav_info['goodscn_pic']);
                $nav_info['goodscn_adv1'] = ds_get_pic(ATTACH_GOODS_CLASS , $nav_info['goodscn_adv1']);
                $nav_info['goodscn_adv2'] = ds_get_pic(ATTACH_GOODS_CLASS , $nav_info['goodscn_adv2']);
            }

            $nav_info['goodscn_classids'] = isset($nav_info['goodscn_classids']) ? explode(',', $nav_info['goodscn_classids']) : array();
            $nav_info['goodscn_brandids'] = isset($nav_info['goodscn_brandids']) ? explode(',', $nav_info['goodscn_brandids']) : array();

            View::assign('nav_info', $nav_info);
            View::assign('class_info', $class_info);
            // 一级分类列表
            $gc_list = $goodsclass_model->getGoodsclassListByParentId(0);
            View::assign('gc_list', $gc_list);

            // 全部三级分类
            $third_class = $goodsclass_model->getChildClassByFirstId($gc_id);
            View::assign('third_class', $third_class);

            // 品牌列表
            $brand_model = model('brand');
            $brand_list = $brand_model->getBrandPassedList(array());

            $b_list = array();
            if (is_array($brand_list) && !empty($brand_list)) {
                foreach ($brand_list as $k => $val) {
                    $b_list[$val['gc_id']]['brand'][$k] = $val;
                    $b_list[$val['gc_id']]['name'] = $val['brand_class'] == '' ? lang('ds_default') : $val['brand_class'];
                }
            }
            ksort($b_list);
            View::assign('brand_list', $b_list);

            return View::fetch('nav_edit');
        }
    }

    /**
     * ajax操作
     */
    public function ajax() {
        $branch = input('param.branch');

        switch ($branch) {
            /**
             * 更新分类
             */
            case 'goods_class_name':
                $goodsclass_model = model('goodsclass');
                $class_array = $goodsclass_model->getGoodsclassInfoById(intval(input('param.id')));
                $condition = array();
                $condition[] = array('gc_name','=',trim(input('param.value')));
                $condition[] = array('gc_parent_id','=',$class_array['gc_parent_id']);
                $condition[] = array('gc_id','<>',intval(input('param.id')));
                $class_list = $goodsclass_model->getGoodsclassList($condition);
                
                if (empty($class_list)) {
                    $where = array('gc_id' => intval(input('param.id')));
                    $update_array = array();
                    $update_array['gc_name'] = trim(input('param.value'));
                    $goodsclass_model->editGoodsclass($update_array, $where);
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
            /**
             * 分类 排序 显示 设置
             */
            case 'goods_class_sort':
            case 'goods_class_show':
            case 'goods_class_index_show':
                $goodsclass_model = model('goodsclass');
                $where = array('gc_id' => intval(input('param.id')));
                $update_array = array();
                $update_array[input('param.column')] = input('param.value');
                $goodsclass_model->editGoodsclass($update_array, $where);
                echo 'true';
                exit;
                break;
            /**
             * 添加、修改操作中 检测类别名称是否有重复
             */
            case 'check_class_name':
                $goodsclass_model = model('goodsclass');
                $condition = array();
                $condition[] = array('gc_name','=',trim(input('get.gc_name')));
                $condition[] = array('gc_parent_id','=',intval(input('get.gc_parent_id')));
                $condition[] = array('gc_id','<>', intval(input('get.gc_id')));
                $class_list = $goodsclass_model->getGoodsclassList($condition);
                if (empty($class_list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
            /**
             * TAG值编辑
             */
            case 'goods_class_tag_value':
                $classtag_model = model('goodsclasstag');
                $update_array = array();
                /**
                 * 转码  防止GBK下用中文逗号截取不正确
                 */
                $comma = '，';
                $update_array[input('param.column')] = trim(str_replace($comma, ',', input('param.value')));
                $classtag_model->editGoodsclasstag($update_array,intval(input('param.id')));
                echo 'true';
                exit;
                break;
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'goods_class',
                'text' => '管理',
                'url' => url('Goodsclass/goods_class')
            ),
        );
        if (request()->action() == 'goods_class_add' || request()->action() == 'goods_class') {
            $menu_array[] = array(
                'name' => 'goods_class_add',
                'text' => '新增',
                'url' => url('Goodsclass/goods_class_add')
            );
        }
        if (request()->action() == 'goods_class_edit') {
            $menu_array[] = array(
                'name' => 'goods_class_edit',
                'text' => '编辑',
                'url' => 'javascript:void(0)'
            );
        }
        $menu_array[] = array(
            'name' => 'tag',
            'text' => '标签',
            'url' => url('Goodsclass/tag')
        );
        return $menu_array;
    }

}

?>
