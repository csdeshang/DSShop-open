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
class Brand extends AdminControl {

    const EXPORT_SIZE = 1000;

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/brand.lang.php');
    }

    /**
     * 品牌列表
     */
    public function index() {
        $brand_model = model('brand');
        /**
         * 检索条件
         */
        $condition = array();
        if (!empty(input('param.search_brand_name'))) {
            $condition[] = array('brand_name','like', "%" . input('param.search_brand_name') . "%");
        }
        if (!empty(input('param.search_brand_class'))) {
            $condition[] = array('brand_class','like', "%" . input('param.search_brand_class') . "%");
        }
        $brand_list = $brand_model->getBrandList($condition, "*", 10);
        View::assign('showpage', $brand_model->page_info->render());
        View::assign('brand_list', $brand_list);
        View::assign('search_brand_name', trim(input('param.search_brand_name')));
        View::assign('search_brand_class', trim(input('param.search_brand_class')));
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 增加品牌
     */
    public function brand_add() {

        $brand_model = model('brand');
        if (request()->isPost()) {
            $data = [
                'brand_name' => input('post.brand_name'), 'brand_initial' => input('post.brand_initial'),
                'brand_sort' => input('post.brand_sort')
            ];

            $brand_validate = ds_validate('brand');

            if (!$brand_validate->scene('brand_add')->check($data)) {
                $this->error($brand_validate->getError());
            } else {
                $insert_array = array();
                if (!empty($_FILES['_pic']['name'])) {
                    $res=ds_upload_pic(ATTACH_BRAND,'_pic');
                    if($res['code']){
                        $brand_pic=$res['data']['file_name'];
                    }else{
                        $this->error($res['msg']);
                    }
                }
                $insert_array['brand_name'] = trim(input('post.brand_name'));
                $insert_array['brand_initial'] = strtoupper(input('post.brand_initial'));
                $insert_array['gc_id'] = input('post.class_id');
                $insert_array['brand_class'] = trim(input('post.brand_class'));
                if (!empty($brand_pic)) {
                    $insert_array['brand_pic'] = $brand_pic;
                }
                $insert_array['brand_recommend'] = trim(input('post.brand_recommend'));
                $insert_array['brand_sort'] = intval(input('post.brand_sort'));
                $insert_array['brand_showtype'] = intval(input('post.brand_showtype')) == 1 ? 1 : 0;
                $result = $brand_model->addBrand($insert_array);
                if ($result) {
                    $this->log(lang('ds_add') . lang('brand_index_brand') . '[' . input('post.brand_name') . ']', 1);
                    dsLayerOpenSuccess(lang('ds_common_save_succ'));
                } else {
                    $this->error(lang('ds_common_save_fail'));
                }
            }
        } else {
            $brand_array = [
                'brand_id' => '',
                'brand_name' => '',
                'brand_initial' => '',
                'gc_id' => '',
                'brand_class' => '',
                'brand_pic' => '',
                'brand_showtype' => '0',
                'brand_recommend' => '1',
                'brand_sort' => '0',
            ];
            View::assign('brand_array', $brand_array);

            // 一级商品分类
            $gc_list = model('goodsclass')->getGoodsclassListByParentId(0);
            View::assign('gc_list', $gc_list);
            return View::fetch('form');
        }
    }

    /**
     * 品牌编辑
     */
    public function brand_edit() {
        $brand_model = model('brand');

        if (request()->isPost()) {
            $data = [
                'brand_name' => input('post.brand_name'), 'brand_initial' => input('post.brand_initial'),
                'brand_sort' => input('post.brand_sort')
            ];

            $brand_validate = ds_validate('brand');
            if (!$brand_validate->scene('brand_edit')->check($data)) {
                $this->error($brand_validate->getError());
            } else {
                if (!empty($_FILES['_pic']['name'])) {
                    $res=ds_upload_pic(ATTACH_BRAND,'_pic');
                    if($res['code']){
                        $brand_pic=$res['data']['file_name'];
                    }else{
                        $this->error($res['msg']);
                    }
                }
                $brand_info = $brand_model->getBrandInfo(array('brand_id' => intval(input('post.brand_id'))));
                $where = array();
                $where[] = array('brand_id','=',intval(input('post.brand_id')));
                $update_array = array();
                $update_array['brand_name'] = trim(input('post.brand_name'));
                $update_array['brand_initial'] = strtoupper(input('post.brand_initial'));
                $update_array['gc_id'] = input('post.class_id');
                $update_array['brand_class'] = trim(input('post.brand_class'));
                if (!empty($brand_pic)) {
                    $update_array['brand_pic'] = $brand_pic;
                }
                $update_array['brand_recommend'] = intval(input('post.brand_recommend'));
                $update_array['brand_sort'] = intval(input('post.brand_sort'));
                $update_array['brand_showtype'] = intval(input('post.brand_showtype')) == 1 ? 1 : 0;
                $result = $brand_model->editBrand($where, $update_array);
                if ($result>=0) {
                    if (!empty(input('post.brand_pic')) && !empty($brand_info['brand_pic'])) {
                        @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_BRAND . DIRECTORY_SEPARATOR . $brand_info['brand_pic']);
                    }
                    $this->log(lang('ds_edit') . lang('brand_index_brand') . '[' . input('post.brand_name') . ']', 1);
                    dsLayerOpenSuccess(lang('ds_common_save_succ'));
                } else {
                    $this->log(lang('ds_edit') . lang('brand_index_brand') . '[' . input('post.brand_name') . ']', 0);
                    $this->error(lang('ds_common_save_fail'));
                }
            }
        } else {
            $brand_info = $brand_model->getBrandInfo(array('brand_id' => intval(input('param.brand_id'))));
            if (empty($brand_info)) {
                $this->error(lang('param_error'));
            }
            View::assign('brand_array', $brand_info);
            // 一级商品分类
            $gc_list = model('goodsclass')->getGoodsclassListByParentId(0);
            View::assign('gc_list', $gc_list);
            return View::fetch('form');
        }
    }

    /**
     * 删除品牌
     */
    public function brand_del() {
        $brand_id = input('param.brand_id');
        $brand_id_array = ds_delete_param($brand_id);
        if ($brand_id_array == FALSE) {
            $this->log(lang('ds_del') . lang('brand_index_brand') . '[ID:' . $brand_id . ']', 0);
            ds_json_encode(10001, lang('param_error'));
        }
        $brand_mod = model('brand');
        $brand_mod->delBrand(array(array('brand_id','in', implode(',', $brand_id_array))));
        $this->log(lang('ds_del') . lang('brand_index_brand') . '[ID:' . $brand_id . ']', 1);
        ds_json_encode(10000, lang('ds_common_del_succ'));
    }



    /**
     * ajax操作
     */
    public function ajax() {
        $brand_model = model('brand');
        switch (input('param.branch')) {
            /**
             * 品牌名称
             */
            case 'brand_name':
                /**
                 * 判断是否有重复
                 */
                $condition = array();
                $condition[] = array('brand_name','=',trim(input('param.value')));
                $condition[] = array('brand_id','<>', intval(input('param.id')));
                $result = $brand_model->getBrandList($condition);
                if (empty($result)) {
                    $brand_model->editBrand(array('brand_id' => intval(input('param.id'))), array('brand_name' => trim(input('param.value'))));
                    $this->log(lang('ds_edit') . lang('brand_index_name') . '[' . input('param.value') . ']', 1);
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
            /**
             * 品牌类别，品牌排序，推荐
             */
            case 'brand_class':
            case 'brand_sort':
            case 'brand_recommend':
                $brand_model->editBrand(array('brand_id' => intval(input('param.id'))), array(input('param.column') => trim(input('param.value'))));
                $detail_log = str_replace(array(
                    'brand_class', 'brand_sort', 'brand_recommend'
                        ), array(
                    lang('brand_index_class'), lang('ds_sort'), lang('ds_recommend')
                        ), input('param.branch'));
                $this->log(lang('ds_edit') . lang('brand_index_brand') . $detail_log . '[ID:' . intval(input('param.id')) . ')', 1);
                echo 'true';
                exit;
                break;
            /**
             * 验证品牌名称是否有重复
             */
            case 'check_brand_name':
                $condition = array();
                $condition[] = array('brand_name','=',trim(input('param.brand_name')));
                $condition[] = array('brand_id','<>', intval(input('param.id')));
                $result = $brand_model->getBrandList($condition);
                if (empty($result)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }

    /**
     * 品牌导出第一步
     */
    public function export_step1() {
        $brand_model = model('brand');
        $condition = array();
        if ((input('param.search_brand_name'))) {
            $condition[] = array('brand_name','like', "%{input('param.search_brand_name')}%");
        }
        if ((input('param.search_brand_class'))) {
            $condition[] = array('brand_class','like', "%{input('param.search_brand_class')}%");
        }

        if (!is_numeric(input('param.page'))) {
            $count = $brand_model->getBrandCount($condition);
            $export_list = array();
            if ($count > self::EXPORT_SIZE) {    //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $export_list[$i] = $limit1 . ' ~ ' . $limit2;
                }
                View::assign('export_list', $export_list);
                return View::fetch('export_excel');
            } else {    //如果数量小，直接下载
                $data = $brand_model->getBrandList($condition, '*', self::EXPORT_SIZE, 'brand_id desc');
                $this->createExcel($data);
            }
        } else {    //下载
            $limit1 = (input('param.page') - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $brand_model->getBrandList($condition, '*', $limit2, 'brand_id desc');
            $this->createExcel($data);
        }
    }

    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array()) {
        Lang::load(base_path() .'admin/lang/'.config('lang.default_lang').'/export.lang.php');
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array(
            'id' => 's_title', 'Font' => array('FontName' => lang('ds_song_typeface'), 'Size' => '12', 'Bold' => '1')
        ));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_brandid'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_brand'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_brand_cate'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_brand_img'));
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $v['brand_id']);
            $tmp[] = array('data' => $v['brand_name']);
            $tmp[] = array('data' => $v['brand_class']);
            $tmp[] = array('data' => $v['brand_pic']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_brand'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_brand'), CHARSET) . input('param.page') . '-' . date('Y-m-d-H', TIMESTAMP));
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index', 
                'text' => lang('ds_manage'),
                'url' => url('Brand/index'),
            ),
            array(
                'name' => 'brand_add',
                'text' => lang('ds_add'), 
                'url' => "javascript:dsLayerOpen('".url('Brand/brand_add')."','添加')"
            ),
        );
        return $menu_array;
    }

}