<?php

namespace app\common\logic;


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
 * 逻辑层模型
 */
class Connectapi {

    public function smsRegister($phone, $captcha, $password, $client, $inviter_id = 0) {
        if ($this->check_captcha($phone, $captcha)) {
            if (config('ds_config.sms_register') != 1) {
                return array('state' => 0, 'msg' => '系统没有开启手机注册功能');
            }
            $member_model = model('member');
            $member_name = 'phone_' . random(10);
            $member = $member_model->getMemberInfo(array('member_name' => $member_name)); //检查重名
            if (!empty($member)) {
                return array('state' => 0, 'msg' => '用户名已被注册');
            }
            $member = $member_model->getMemberInfo(array('member_mobile' => $phone)); //检查手机号是否已被注册
            if (!empty($member)) {
                return array('state' => 0, 'msg' => '手机号已被注册');
            }
            $member = array();
            $member['member_name'] = $member_name;
            $member['member_password'] = $password;
            $member['member_mobile'] = $phone;
            $member['member_email'] = '';
            $member['member_mobilebind'] = 1;
            $member['inviter_id'] = $inviter_id;
            $insert_id = $member_model->addMember($member);
            if ($insert_id) {
                $member_model->addMemberAfter($insert_id,$member);
                $member = $member_model->getMemberInfo(array('member_mobile' => $phone));
                $key = $member_model->getBuyerToken($member['member_id'], $member['member_name'], $client);
                return array('state' => 1, 'username' => $member_name, 'token' => $key,'info'=>$member);
            } else {
                return array('state' => 0, 'msg' => '注册失败', $member);
            }
        }
    }

    /**
     * 手机找回密码
     * @param array $order_info
     * @param string $phone 手机号码
     * @param string $password 密码
     * @return array
     */
    public function smsPassword($phone, $captcha, $password, $client) {
        if (config('ds_config.sms_password') != 1) {
            return array('state' => 0, 'msg' => '系统没有开启手机找回密码功能');
        }
        $condition = array();
        $condition[] = array('smslog_phone','=',$phone);
        $condition[] = array('smslog_captcha','=',$captcha);
        $condition[] = array('smslog_type','=',3);
        $smslog_model = model('smslog');
        $sms_log = $smslog_model->getSmsInfo($condition);
        if (empty($sms_log) || ($sms_log['smslog_smstime'] < TIMESTAMP - 1800)) {//半小时内进行验证为有效
            return array('state' => 0, 'msg' => '动态码错误或已过期，重新输入');
        }
        $member_model = model('member');
        $member = $member_model->getMemberInfo(array('member_mobile' => $phone)); //检查手机号是否已被注册
        if (!empty($member)) {
            $new_password = md5($password);
            $member_model->editMember(array('member_id' => $member['member_id']), array('member_password' => $new_password),$member['member_id']);
            $member_model->createSession($member); //自动登录
            $key = $member_model->getBuyerToken($member['member_id'], $member['member_name'], $client);
            return array('state' => 1, 'msg' => '密码修改成功', 'token' => $key,'info'=>$member);
        }
    }

    public function getStateInfo() {
        $data['sms_register'] = config('ds_config.sms_register') == 1 ? 1 : 0;
        $data['sms_login'] = config('ds_config.sms_login') == 1 ? 1 : 0;
        $data['sms_password'] = config('ds_config.sms_password') == 1 ? 1 : 0;
        return $data;
    }

    /**
     * 手机验证码验证
     */
    protected function check_captcha($phone, $captcha, $type = '1') {
        if (strlen($phone) == 11 && strlen($captcha) == 6) {
            $condition = array();
            $condition[] = array('smslog_phone','=',$phone);
            $condition[] = array('smslog_captcha','=',$captcha);
            $condition[] = array('smslog_type','=',$type);
            $smslog_model = model('smslog');
            $sms_log = $smslog_model->getSmsInfo($condition);
            if (empty($sms_log) || ($sms_log['smslog_smstime'] < TIMESTAMP - 1800)) {//半小时内进行验证为有效
                $state = '动态码错误或已过期，重新输入';
                ds_json_encode(10001,$state);
            }
            return true;
        }
        return false;
    }

    /**
     * 微信注册
     * @param type $reg_info
     * @param type $reg_type  自动注册类型   wx  qq  sina
     * @return type
     */
    public function wx_register($reg_info, $reg_type) {
        $reg_info['nickname'] = isset($reg_info['nickname']) ? $reg_info['nickname'] : '';
        $reg_info['nickname'] = removeEmoji($reg_info['nickname']);
        
        $member = array();
        $member_model = model('member');
        if ($reg_type == 'wx' && !empty($reg_info['member_wxunionid'])) {
            //如果用户存在.
            $exist_member = $member_model->getMemberInfo(array('member_wxunionid' => $reg_info['member_wxunionid']));
            if (!empty($exist_member)) {
                return $exist_member;
            }
            $member['member_wxunionid'] = $reg_info['member_wxunionid'];
            $member['member_wxopenid'] = $reg_info['member_wxopenid'];
            $member['member_wxinfo'] = serialize($reg_info);
        }elseif ($reg_type == 'qq' && !empty($reg_info['member_qqopenid'])) {
            //如果用户存在.
            $exist_member = $member_model->getMemberInfo(array('member_qqopenid' => $reg_info['member_qqopenid']));
            if (!empty($exist_member)) {
                return $exist_member;
            }
            $member['member_qqopenid'] = $reg_info['member_qqopenid'];
            $member['member_qqinfo'] = serialize($reg_info);
        }elseif ($reg_type == 'sina' && !empty($reg_info['member_sinaopenid'])){
            //如果用户存在.
            $exist_member = $member_model->getMemberInfo(array('member_sinaopenid' => $reg_info['member_sinaopenid']));
            if (!empty($exist_member)) {
                return $exist_member;
            }
            $member['member_sinaopenid'] = $reg_info['member_sinaopenid'];
            $member['member_sinainfo'] = serialize($reg_info);
        }else{
            return;
        }
            
        
        
        $member['member_password'] = rand(100000, 999999);
        $member['member_email'] = '';
        $member['member_birthday'] = TIMESTAMP;
        $member['member_nickname'] = $reg_info['nickname'];
        if (isset($reg_info['inviter_id'])) {
            $member['inviter_id'] = $reg_info['inviter_id'];
        }


        /*
          $rand = rand(100, 899);
          if (empty($reg_info['nickname']))
          $reg_info['nickname'] = 'name_' . $rand;
          if (strlen($reg_info['nickname']) < 3)
          $reg_info['nickname'] = $reg_info['nickname'] . $rand;
          $member_name = $reg_info['nickname'];
         */
        $member_name = $reg_type . '_' . random(10);
        $member_info = $member_model->getMemberInfo(array('member_name' => $member_name));
        
        if (empty($member_info)) {
            $member['member_name'] = $member_name;
            $insert_id = $member_model->addMember($member);
            $member_info = $member_model->getMemberInfo(array('member_name' => $member_name));
        } else {
            for ($i = 1; $i < 999; $i++) {
                /*
                  $rand += $i;
                  $member_name = $reg_info['nickname'] . $rand;
                 */
                $member_name = $reg_type . '_' . random(10);
                $member_info = $member_model->getMemberInfo(array('member_name' => $member_name));
                if (empty($member_info)) {//查询为空表示当前会员名可用
                    $member['member_name'] = $member_name;
                    $insert_id = $member_model->addMember($member);
                    break;
                }
            }
        }
        if ($insert_id) {
            $member_model->addMemberAfter($insert_id,$member_info);
            if (0 && isset($reg_info['headimgurl'])) {#提高体验暂时不对图片进行处理
                $headimgurl = $reg_info['headimgurl'];
                $avatar = @copy($headimgurl, BASE_UPLOAD_PATH . '/' . ATTACH_AVATAR . "/avatar_$insert_id.jpg");
                if ($avatar) {
                    $member_model->editMember(array('member_id' => $insert_id), array('member_avatar' => "avatar_$insert_id.jpg"),$insert_id);
                }
            }
            $member = $member_model->getMemberInfo(array('member_id' => $insert_id));
            return $member;
        }
    }

}
