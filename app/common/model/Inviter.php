<?php

namespace app\common\model;
use think\facade\Db;

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
 * 数据层模型
 */
class Inviter extends BaseModel {

    public $page_info;


    /**
     * 增加帮助
     * @access public
     * @author csdeshang
     * @param type $inviter_array 帮助内容
     * @param type $upload_ids 更新ID
     * @return type
     */
    public function addInviter($inviter_array) {
        $inviter_id = Db::name('inviter')->insertGetId($inviter_array);

        return $inviter_id;
    }


    /**
     * 修改帮助记录
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $data 数据
     * @return boolean
     */
    public function editInviter($condition, $data) {
        if (empty($condition)) {
            return false;
        }
        if (is_array($data)) {
            $result = Db::name('inviter')->where($condition)->update($data);
            return $result;
        } else {
            return false;
        }
    }

    public function getInviterInfo($condition,$fields = 'm.member_name,i.*'){
        if (empty($condition)) {
            return false;
        }
        $result = Db::name('inviter')->alias('i')->join('member m', 'i.inviter_id=m.member_id')->field($fields)->where($condition)->find();
        return $result;
    }

    /**
     * 帮助记录
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 分页
     * @param type $limit 限制
     * @param type $fields 字段
     * @return array
     */
    public function getInviterList($condition = array(), $pagesize = '', $limit = 0, $fields = '*') {
        if($pagesize) {
            $res=Db::name('inviter')->alias('i')->join('member m', 'i.inviter_id=m.member_id')->field($fields)->where($condition)->order('inviter_applytime desc')->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info=$res;
            $result=$res->items();
        }else{
            $result = Db::name('inviter')->alias('i')->join('member m', 'i.inviter_id=m.member_id')->field($fields)->where($condition)->limit($limit)->order('inviter_applytime desc')->select()->toArray();
        }
        return $result;
    }
    
    /**
     * 生成微信推荐二维码
     * @param type $member_info
     * @return type
     */
    public function qrcode_weixin($member_info){
        $wx_error_msg = '';
        if(!file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_INVITER . '/' . $member_info['member_id'] . '_weixin.png')){
            $config = model('wechat')->getOneWxconfig();
            $wechat=new \app\api\controller\WechatApi($config);
            $expire_time = $config['expires_in'];
            if($expire_time > TIMESTAMP){
                //有效期内
                $wechat->access_token_= $config['access_token'];
            }else{
                $access_token=$wechat->checkAuth();
                $web_expires = TIMESTAMP + 7000; // 提前200秒过期
                Db::name('wxconfig')->where(array('id'=>$config['id']))->update(array('access_token'=>$access_token,'expires_in'=>$web_expires));
            }
            $return=$wechat->getQRCode($member_info['member_id'], 1);
            if($return){
                $refer_qrcode_weixin=$wechat->getQRUrl($return['ticket']);
                if (!is_dir(BASE_UPLOAD_PATH . '/' . ATTACH_INVITER)) {
                    mkdir(BASE_UPLOAD_PATH . '/' . ATTACH_INVITER, 0755, true);
                }
                copy($refer_qrcode_weixin,BASE_UPLOAD_PATH . '/' . ATTACH_INVITER . '/' . $member_info['member_id'] . '_weixin.png');
            }else{
                $refer_qrcode_weixin = '';
                $wx_error_msg = $wechat->errMsg;
            }
        }else{
            $refer_qrcode_weixin=UPLOAD_SITE_URL. '/' . ATTACH_INVITER . '/' . $member_info['member_id'] . '_weixin.png';
        }
        
        return array(
            'refer_qrcode_weixin' =>$refer_qrcode_weixin,
            'wx_error_msg'=>$wx_error_msg,
        );
    }
    
    /**
     * 生成URL推荐二维码
     * @param type $member_info
     * @return type
     */
    public function qrcode_logo($member_info){
        !is_dir(BASE_UPLOAD_PATH . '/' . ATTACH_INVITER) && mkdir(BASE_UPLOAD_PATH . '/' . ATTACH_INVITER, 0755, true);
        $qrcode_path = BASE_UPLOAD_PATH . '/' . ATTACH_INVITER . '/' . $member_info['member_id'] . '.png';
        $refer_qrcode_logo = BASE_UPLOAD_PATH . '/' . ATTACH_INVITER . '/' . $member_info['member_id'] . '_poster.png';
        if (!file_exists($qrcode_path)) {
            include_once root_path(). 'extend/qrcode/phpqrcode.php';
            \QRcode::png(config('ds_config.h5_site_url') . '/pages/home/memberregister/Register?inviter_id=' . $member_info['member_id'], $qrcode_path);
        }
        $qrcode = imagecreatefromstring(file_get_contents($qrcode_path));
        //背景图片
        $inviter_back = imagecreatefromstring(file_get_contents(ds_get_pic(ATTACH_COMMON,config('ds_config.inviter_back'))));


        $QR_width = imagesx($qrcode);
        $QR_height = imagesy($qrcode);
        imagecopyresampled($inviter_back, $qrcode, 100, 170, 0, 0, 160, 160, $QR_width, $QR_height);
        
        $portrait = imagecreatefromstring(file_get_contents(get_member_avatar_for_id($member_info['member_id'])));

        $QR_width2 = imagesx($portrait);
        $QR_height2 = imagesy($portrait);
        imagecopyresampled($inviter_back, $portrait, 170, 240, 0, 0, 20, 20, $QR_width2, $QR_height2);

        //此处是给图片载入文字
        /*
        $text = $member_info['member_id'];
        $textcolor = imagecolorallocate($inviter_back, 255, 50, 37);
        $text_x = 180-(strlen($text)*12)/2;//根据长度定义X轴
        imagefttext($inviter_back, 16, 0, $text_x, 145, $textcolor, PUBLIC_PATH . '/font/msyh.ttf', mb_convert_encoding($text, "html-entities", "utf-8"));
        */

        imagepng($inviter_back, $refer_qrcode_logo);
    }

}
