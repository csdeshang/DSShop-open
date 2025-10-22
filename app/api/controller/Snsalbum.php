<?php

namespace app\api\controller;

use think\facade\Db;
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
class Snsalbum extends MobileMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . '');
    }

    /**
     * 上传图片
     *
     * @param
     * @return
     */
    public function file_upload() {
        $member_id = $this->member_info['member_id'];
        // 验证图片数量
        $count = Db::name('snsalbumpic')->where(array('member_id' => $member_id))->count();
        if (config('ds_config.malbum_max_sum') != 0 && $count >= config('ds_config.malbum_max_sum')) {
            ds_json_encode(10001, '已经超出允许上传图片数量，不能在上传图片！');
        }

        /**
         * 上传图片
         */
        $file_name = $member_id . '_' . date('YmdHis') . rand(10000, 99999) . '.png';
        $res = ds_upload_pic(ATTACH_MALBUM . '/' . $member_id, 'file', $file_name);
        if ($res['code']) {
            $file_name = $res['data']['file_name'];
            $input = $file_name;
        } else {
            ds_json_encode(10001, $res['msg']);
        }

        $img_path = $result->getFilename();
        list($width, $height, $type, $attr) = getimagesize($upload_dir . $img_path);

        $image = explode('.', $_FILES["file"]["name"]);

        $snsalumb_model = model('snsalbum');
        $ac_id = $snsalumb_model->getSnsAlbumClassDefault($member_id);
        $insert = array();
        $insert['ap_name'] = $image['0'];
        $insert['ac_id'] = $ac_id;
        $insert['ap_cover'] = $img_path;
        $insert['ap_size'] = intval($_FILES['file']['size']);
        $insert['ap_spec'] = $width . 'x' . $height;
        $insert['ap_uploadtime'] = TIMESTAMP;
        $insert['member_id'] = $member_id;
        $result = Db::name('snsalbumpic')->insertGetId($insert);

        $data = array();
        $data['file_id'] = $result;
        $data['file_name'] = $img_path;
        $data['origin_file_name'] = $_FILES["file"]["name"];
        $data['file_url'] = sns_thumb($img_path, 240);
        ds_json_encode(10000, '', $data);
    }

}

?>
