<?php

/*
 * 空间管理
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
class Goodsalbum extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/goodsalbum.lang.php');
    }

    /**
     * 相册列表
     */
    public function index() {
        $condition = array();
        $album_model = model('album');
        $albumclass_list = $album_model->getGoodsalbumList($condition, 10, '*');
        View::assign('show_page', $album_model->page_info->render());

        if (is_array($albumclass_list) && !empty($albumclass_list)) {
            foreach ($albumclass_list as $v) {
                $class[] = $v['aclass_id'];
            }
            $where[] = array('aclass_id', 'in', $class);
        } else {
            $where = '1=1';
        }
        $count = $album_model->getAlbumpicCountlist($where, 'aclass_id,count(*) as pcount', 'aclass_id');

        $pic_count = array();
        if (is_array($count)) {
            foreach ($count as $v) {
                $pic_count[$v['aclass_id']] = $v['pcount'];
            }
        }
        View::assign('pic_count', $pic_count);
        View::assign('albumclass_list', $albumclass_list);

        $aclass_info = $album_model->getAlbumclassList(array());
        View::assign('aclass_info', $aclass_info);

        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件

        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 新增相册分类
     * @return type
     */
    public function album_add() {
        if (!request()->isPost()) {
            View::assign('controller', 'add');
            return View::fetch();
        } else {
            /**
             * 实例化相册模型
             */
            $param = array();
            $param['aclass_name'] = input('post.aclass_name');
            $param['aclass_des'] = input('post.aclass_des');
            $param['aclass_sort'] = input('post.aclass_sort');
            $param['aclass_uploadtime'] = TIMESTAMP;

            $album_validate = ds_validate('album');
            if (!$album_validate->scene('album_add')->check($param)) {
                $this->error($album_validate->getError());
            }

            $album_model = model('album');
            $return = $album_model->addAlbumclass($param);
            if ($return) {
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
            }
        }
    }

    /**
     * 相册分类编辑
     */
    public function album_edit() {

        $aclass_id = intval(input('param.aclass_id'));
        if ($aclass_id <= 0) {
            $this->error(lang('param_error'));
            exit;
        }
        if (!request()->isPost()) {
            $album_model = model('album');
            $condtion['aclass_id'] = $aclass_id;
            $class_info = $album_model->getOneAlbumclass($condtion);
            View::assign('class_info', $class_info);
            View::assign('controller', 'edit');
            return View::fetch('album_add');
        } else {

            $param = array();
            $param['aclass_name'] = input('post.aclass_name');
            $param['aclass_des'] = input('post.aclass_des');
            $param['aclass_sort'] = input('post.aclass_sort');

            $album_validate = ds_validate('album');
            if (!$album_validate->scene('album_add')->check($param)) {
                $this->error($album_validate->getError());
            }

            /**
             * 实例化相册模型
             */
            $album_model = model('album');
            /**
             * 验证
             */
            $return = $album_model->checkAlbum(array('aclass_id' => $aclass_id));
            if ($return) {
                /**
                 * 更新
                 */
                $re = $album_model->editAlbumclass($param, $aclass_id);
                if ($re) {
                    dsLayerOpenSuccess(lang('ds_common_op_succ'));
                } else {
                    $this->error(lang('ds_common_op_fail'));
                }
            } else {
                $this->error(lang('param_error'));
            }
        }
    }

    /**
     * 删除相册分类
     */
    public function aclass_del() {
        $aclass_id = input('param.aclass_id');
        $aclass_id_array = ds_delete_param($aclass_id);
        if ($aclass_id_array == FALSE) {
            ds_json_encode('10001', lang('param_error'));
        }
        $condition = array();
        $condition[] = array('aclass_id', 'in', $aclass_id_array);
        $albumpic_model = model('album');
        //批量删除相册图片
        $albumpic_model->delAlbumpic($condition);
        $albumpic_model->delAlbumclass($condition);
        $this->log(lang('ds_del') . lang('g_album_one') . '[ID:' . intval(input('param.aclass_id')) . ']', 1);
        ds_json_encode('10000', lang('ds_common_del_succ'));
    }

    /**
     * 图片列表
     */
    public function album_pic_list() {
        $condition = array();
        $aclass_id = intval(input('param.aclass_id'));
        if ($aclass_id > 0) {
            $condition[] = array('aclass_id', '=', $aclass_id);
        }
        $albumpic_model = model('album');
        $albumpic_list = $albumpic_model->getAlbumpicList($condition, 34, '', 'apic_id desc');
        $show_page = $albumpic_model->page_info->render();
        View::assign('show_page', $show_page);
        View::assign('albumpic_list', $albumpic_list);
        $this->setAdminCurItem('pic_list');
        return View::fetch();
    }

    /**
     * 图片删除
     */
    public function album_pic_del() {

        $ids = input('param.id/a');
        if (empty($ids)) {
            $this->error(lang('param_error'));
        }
        if (!is_array($ids)) {
            $ids = array(intval($ids));
        }
        $album_model = model('album');

        //删除图片
        $condition = array();
        $condition[] = array('apic_id', 'in', $ids);
        $return = $album_model->delAlbumpic($condition);
        if ($return) {
            $this->success(lang('album_class_pic_del_succeed'));
        } else {
            $this->error(lang('album_class_pic_del_lose'));
        }
    }

    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '相册列表',
                'url' => url('Goodsalbum/index')
            ),
            array(
                'name' => 'album_add',
                'text' => '新增相册分类',
                'url' => "javascript:dsLayerOpen('" . url('Goodsalbum/album_add') . "','新增相册')"
            ),
            array(
                'name' => 'pic_list',
                'text' => '图片列表',
                'url' => url('Goodsalbum/album_pic_list')
            ),
            array(
                'name' => 'watermark',
                'text' => '水印设置',
                'url' => url('Goodsalbum/watermark')
            ),
        );
        return $menu_array;
    }

    /**
     * 替换图片
     */
    public function replace_image_upload() {
        $file = input('param.id');
        $tpl_array = explode('_', $file);
        $id = intval(end($tpl_array));
        $album_model = model('album');
        $condition = array();
        $condition[] = array('apic_id', '=', $id);
        $apic_info = $album_model->getOneAlbumpicById($condition);
        if (substr(strrchr($apic_info['apic_cover'], "."), 1) != substr(strrchr($_FILES[$file]["name"], "."), 1)) {
            // 后缀名必须相同
            $error = lang('album_replace_same_type');
            echo json_encode(array('state' => 'false', 'message' => $error));
            exit();
        }
        $pic_cover = implode(DIRECTORY_SEPARATOR, explode(DIRECTORY_SEPARATOR, $apic_info['apic_cover'], -1)); // 文件路径
        $tmpvar = explode(DIRECTORY_SEPARATOR, $apic_info['apic_cover']);
        $pic_name = end($tmpvar); // 文件名称

        /**
         * 上传图片
         */
        //上传文件保存路径
        $upload_path = ATTACH_GOODS . '/' .date('Ymd',$val['apic_uploadtime']) ;
        $result = upload_albumpic($upload_path, $file, $pic_name);
        if ($result['code'] == '10000') {
            $img_path = $result['result'];
            list($width, $height, $type, $attr) = getimagesize($img_path);
            $img_path = substr(strrchr($img_path, "/"), 1);
        } else {
            $data['state'] = 'false';
            $data['origin_file_name'] = $_FILES[$file]['name'];
            $data['message'] = $result['message'];
            echo json_encode($data);
            exit;
        }

        $update_array = array();
        $update_array['apic_size'] = intval($_FILES[$file]['size']);
        $update_array['apic_spec'] = $width . 'x' . $height;
        $condition = array();
        $condition[] = array('apic_id', '=', $id);
        $result = model('album')->editAlbumpic($update_array, $condition);

        echo json_encode(array('state' => 'true', 'id' => $id));
        exit();
    }

    /**
     * 图片列表，外部调用
     */
    public function pic_list() {
        /**
         * 实例化相册类
         */
        $album_model = model('album');
        /**
         * 图片列表
         */
        $param = array();
        $id = intval(input('param.id'));
        if ($id > 0) {
            $param['aclass_id'] = $id;
            /**
             * 分类列表
             */
            $condition = array();
            $condition[] = array('aclass_id', '=', $id);
            $cinfo = $album_model->getOneAlbumclass($condition);
            View::assign('class_name', $cinfo['aclass_name']);
        }
        $pic_list = $album_model->getAlbumpicList($param, 12);
        foreach ($pic_list as $key => $val) {
            $pic_list[$key]['apic_name'] = ds_get_pic(ATTACH_GOODS . '/' .date('Ymd',$val['apic_uploadtime']), $val['apic_name']);
        }
        View::assign('pic_list', $pic_list);
        View::assign('show_page', $album_model->page_info->render());
        /**
         * 分类列表
         */
        $condition = array();
        $class_info = $album_model->getAlbumclassList($condition);
        View::assign('class_list', $class_info);

        $item = input('param.item');
        switch ($item) {
            case 'goods':
                return View::fetch('pic_list_goods');
                break;
            case 'des':
                echo View::fetch('pic_list_des');
                break;
            case 'groupbuy':
                return View::fetch('pic_list_groupbuy');
                break;
            case 'goods_image':
                View::assign('color_id', input('param.color_id'));
                return View::fetch('pic_list_goods_image');
                break;
            case 'mobile':
                View::assign('type', input('param.type'));
                echo View::fetch('pic_list_mobile');
                break;
        }
    }

    /**
     * 上传图片
     *
     */
    public function image_upload() {
        if (input('param.category_id')) {
            $category_id = intval(input('param.category_id'));
        } else {
            $error = '上传 图片失败';
            $data['state'] = 'false';
            $data['message'] = $error;
            $data['origin_file_name'] = $_FILES["file"]["name"];
            echo json_encode($data);
            exit();
        }



        /**
         * 上传图片
         */
        $time=TIMESTAMP;
        //上传文件保存路径
        $upload_path = ATTACH_GOODS . '/' . date('Ymd',$time);
        $save_name = date('YmdHis',$time) . rand(10000, 99999);
        $name = 'file';
        $result = upload_albumpic($upload_path, $name, $save_name);
        if ($result['code'] == '10000') {
            $img_path = $result['result'];
            list($width, $height, $type, $attr) = getimagesize($img_path);
            $pic = substr(strrchr($img_path, "/"), 1);
        } else {
            exit($result['message']);
        }
        $insert_array = array();
        $insert_array['apic_name'] = $pic;
        $insert_array['apic_tag'] = '';
        $insert_array['aclass_id'] = $category_id;
        $insert_array['apic_cover'] = $pic;
        $insert_array['apic_size'] = intval($_FILES['file']['size']);
        $insert_array['apic_spec'] = $width . 'x' . $height;
        $insert_array['apic_uploadtime'] = $time;
        $result = model('album')->addAlbumpic($insert_array);

        $data = array();
        $data['file_id'] = $result;
        $data['file_name'] = $pic;
        $data['origin_file_name'] = $_FILES["file"]["name"];
        $data['file_path'] = $pic;
        $data['instance'] = input('get.instance');
        $data['state'] = 'true';
        /**
         * 整理为json格式
         */
        $output = json_encode($data);
        echo $output;
        exit;
    }

    /**
     * ajax验证名称时候重复
     */
    public function ajax_check_class_name() {
        if (input('get.type') == 'edit') {
            echo 'true';
            die;
        }
        $ac_name = trim(input('get.ac_name'));
        if ($ac_name == '') {
            echo 'true';
            die;
        }
        $album_model = model('album');
        $condition = array();
        $condition[] = array('aclass_name', '=', $ac_name);

        $class_info = $album_model->getOneAlbumclass($condition);
        if (!empty($class_info)) {
            echo 'false';
            die;
        } else {
            echo 'true';
            die;
        }
    }

    /**
     * ajax修改图名称
     */
    public function change_pic_name() {
        $apic_id = intval(input('post.id'));
        $apic_name = input('post.name');

        if ($apic_id <= 0 && empty($apic_name)) {
            echo 'false';
        }
        /**
         * 实例化相册类
         */
        $album_model = model('album');

        $return = $album_model->editAlbumpic(array('apic_name' => $apic_name), array('apic_id' => $apic_id));
        if ($return) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    /**
     * 水印管理
     */
    public function watermark() {
        /**
         * 保存水印配置信息
         */
        $config_model = model('config');
        $list_config = rkcache('config', true);
        if (!request()->isPost()) {
            /**
             * 获取水印字体
             */
            $fontInfo = array();
            include PUBLIC_PATH . DIRECTORY_SEPARATOR . 'font' . DIRECTORY_SEPARATOR . 'font.info.php';
            foreach ($fontInfo as $key => $value) {
                if (!file_exists(PUBLIC_PATH . DIRECTORY_SEPARATOR . 'font' . DIRECTORY_SEPARATOR . $key . '.ttf')) {
                    unset($fontInfo[$key]);
                }
            }
            View::assign('file_list', $fontInfo);


            View::assign('list_config', $list_config);
            /* 设置卖家当前栏目 */
            $this->setAdminCurItem('watermark');
            return View::fetch();
        } else {

            $param = array();
            $update_array['swm_image_pos'] = input('post.swm_image_pos');
            $update_array['swm_image_transition'] = intval(input('post.swm_image_transition'));
            $update_array['swm_text'] = input('post.swm_text');
            $update_array['swm_text_size'] = input('post.swm_text_size');
            $update_array['swm_text_angle'] = input('post.swm_text_angle');
            $update_array['swm_text_font'] = input('post.swm_text_font');
            $update_array['swm_text_pos'] = input('post.swm_text_pos');
            $update_array['swm_text_color'] = input('post.swm_text_color');
            $update_array['swm_quality'] = intval(input('post.swm_quality'));

            $upload_file = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_WATERMARK;
            if (!empty($_FILES['image']['name'])) {
                $res = ds_upload_pic(ATTACH_WATERMARK, 'image');
                if ($res['code']) {
                    $file_name = $res['data']['file_name'];
                    $update_array['swm_image_name'] = $file_name;
                    //删除旧水印
                    if (!empty($list_config['swm_image_name'])) {
                        @unlink($upload_file . DIRECTORY_SEPARATOR . $list_config['swm_image_name']);
                    }
                } else {
                    $this->error($res['msg']);
                }
            } elseif (input('post.is_del_image') == 'ok') {
                //删除水印
                if (!empty($list_config['swm_image_name'])) {
                    $update_array['swm_image_name'] = '';
                    @unlink($upload_file . DIRECTORY_SEPARATOR . $list_config['swm_image_name']);
                }
            }
            $result = $config_model->editConfig($update_array);
            if ($result) {
                $this->success(lang('watermark_congfig_success'));
            } else {
                $this->error(lang('watermark_congfig_fail'));
            }
        }
    }

    /**
     * 添加水印
     */
    public function album_pic_watermark() {
        $id_array = input('post.id/a');
        if (empty($id_array) && !is_array($id_array)) {
            $this->error(lang('param_error'));
        }

        $id = trim(implode(',', $id_array), ',');

        /**
         * 实例化图片模型
         */
        $album_model = model('album');
        $condition = array();
        $condition[] = array('apic_id', 'in', $id);

        $wm_list = $album_model->getAlbumpicList($condition);

        if (config('ds_config.swm_image_name') == '' && config('ds_config.swm_text') == '') {
            $this->error(lang('album_class_setting_wm'), url('Goodsalbum/watermark')); //"请先设置水印"
        }
        //获取店铺生成缩略图规格
        $ifthumb = FALSE;
        if (defined('GOODS_IMAGES_WIDTH') && defined('GOODS_IMAGES_HEIGHT') && defined('GOODS_IMAGES_EXT')) {
            $thumb_width = explode(',', GOODS_IMAGES_WIDTH);
            $thumb_height = explode(',', GOODS_IMAGES_HEIGHT);
            $thumb_ext = explode(',', GOODS_IMAGES_EXT);
            if (count($thumb_width) == count($thumb_height) && count($thumb_width) == count($thumb_ext)) {
                $ifthumb = TRUE;
            }
        }

        //文件路径
        $upload_path = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_GOODS;
        if ($ifthumb) {
            foreach ($wm_list as $v) {
                //商品的图片路径
                $image_file = $upload_path . DIRECTORY_SEPARATOR . date('Ymd',$v['apic_uploadtime']) . DIRECTORY_SEPARATOR . $v['apic_cover'];
                //原图不做修改,对缩略图做修改
                if (!file_exists($image_file)) {
                    continue;
                }
                //重新生成缩略图，以及水印
                for ($i = 0; $i < count($thumb_width); $i++) {
                    //打开图片
                    $gd_image = \think\Image::open($image_file);
                    //水印图片名称
                    $thumb_image_file = $upload_path . DIRECTORY_SEPARATOR . date('Ymd',$v['apic_uploadtime']) . '/' . str_ireplace('.', $thumb_ext[$i] . '.', $v['apic_cover']);
                    //添加图片水印
                    if (!empty(config('ds_config.swm_image_name'))) {
                        //水印图片的路径
                        $w_image = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_WATERMARK . DIRECTORY_SEPARATOR . config('ds_config.swm_image_name');
                        $gd_image->thumb($thumb_width[$i], $thumb_height[$i], \think\Image::THUMB_CENTER)->water($w_image, config('ds_config.swm_image_pos'), config('ds_config.swm_image_transition'))->save($thumb_image_file, null, config('ds_config.swm_quality'));
                    }
                    //添加文字水印
                    if (!empty(config('ds_config.swm_text'))) {
                        //字体文件路径
                        $font = 'font' . DIRECTORY_SEPARATOR . config('ds_config.swm_text_font') . '.ttf';
                        $gd_image->thumb($thumb_width[$i], $thumb_height[$i], \think\Image::THUMB_CENTER)->text(config('ds_config.swm_text'), $font, config('ds_config.swm_text_size'), config('ds_config.swm_text_color'), config('ds_config.swm_text_pos'), config('ds_config.swm_text_angle'))->save($thumb_image_file, null, config('ds_config.swm_quality'));
                    }
                }
            }
        }
        $this->success(lang('album_pic_plus_wm_succeed'));
    }

}

?>
