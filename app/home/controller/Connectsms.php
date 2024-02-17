<?php

/*
 * 手机验证码
 */

namespace app\home\controller;

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
class Connectsms extends BaseMall {
    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/login.lang.php');
    }
    /**
     * 短信动态码
     */
    public function get_captcha() {
        header("Content-Type: text/html;charset=utf-8");
        $sms_mobile = input('param.sms_mobile');
        if (strlen($sms_mobile) == 11) {
            $log_type = input('param.type'); //短信类型:1为注册,2为登录,3为找回密码
            
            $member_model = model('member');
            $member = $member_model->getMemberInfo(array('member_mobile' => $sms_mobile));
            $sms_captcha = rand(100000, 999999);
            $log_msg = lang('ds_you_in').'' . date("Y-m-d");
            switch ($log_type) {
                case '1':
                    if (config('ds_config.sms_register') != 1) {
                        echo lang('system_obile_registration_function');
                        exit;
                    }
                    if (!empty($member)) {
                        //检查手机号是否已被注册
                        echo lang('change_another_number');;
                        exit;
                    }
                    $mailmt_code = 'register';
                    break;
                case '2':
                    if (config('ds_config.sms_login') != 1) {
                        echo lang('enable_mobile_phone_login');
                        exit;
                    }
                    if (empty($member)) {
                        //检查手机号是否已绑定会员
                        echo lang('check_correct_number');
                        exit;
                    }
                    $mailmt_code = 'login';
                    break;
                case '3':
                    if (config('ds_config.sms_password') != 1) {
                        echo lang('mobile_back_password');
                        exit;
                    }
                    if (empty($member)) {
                        //检查手机号是否已绑定会员
                        echo lang('check_correct_number');
                        exit;
                    }
                    $mailmt_code = 'reset_password';
                    break;
                default:
                    echo lang('param_error');
                    exit;
                    break;
            }
            
            $smslog_model = model('smslog');
            $mailtemplates_model = model('mailtemplates');
                $tpl_info = $mailtemplates_model->getTplInfo(array('mailmt_code' => $mailmt_code));
                $param = array();
                $param['code'] = $sms_captcha;
                $ten_param=array($sms_captcha);
                $message = ds_replace_text($tpl_info['mailmt_content'], $param);
                $smslog_param=array(
                    'ali_template_code'=>$tpl_info['ali_template_code'],
                    'ali_template_param'=>$param,
                    'ten_template_code'=>$tpl_info['ten_template_code'],
                    'ten_template_param'=>$ten_param,
                    'message'=>$message,
                );
            $result = $smslog_model->sendSms($sms_mobile,$smslog_param,$log_type,$sms_captcha,$member['member_id'],$member['member_name']);
            if($result['state']){
                session('sms_mobile', $sms_mobile);
                session('sms_captcha', $sms_captcha);
                echo 'true';
                //exit;
            }else{
                echo $result['message'];
                exit;
            }
        } else {
            echo lang('phone_length_incorrect');
            exit;
        }
    }

    /**
     * 验证注册动态码
     */
    public function check_captcha() {
        $state = lang('validation_fails');
        $phone = input('get.phone');
        $captcha = input('get.sms_captcha');
        if (strlen($phone) == 11 && strlen($captcha) == 6) {
            $state = 'true';
            $condition = array();
            $condition[] = array('smslog_phone','=',$phone);
            $condition[] = array('smslog_captcha','=',$captcha);
            $condition[] = array('smslog_type','=',1);
            $smslog_model = model('smslog');
            $sms_log = $smslog_model->getSmsInfo($condition);
            if (empty($sms_log) || ($sms_log['smslog_smstime'] < TIMESTAMP - 1800)) {//半小时内进行验证为有效
                $state = lang('dynamic_code_expired');
            }
        }
        exit($state);
    }

    /**
     * 登录
     */
    public function login() {
        if(!config('ds_config.sms_login') && config('ds_config.captcha_status_login')==1 && !captcha_check(input('post.captcha_mobile'))){
            ds_json_encode(10001,lang('image_verification_code_error'));
        }
        
        if (request()->isPost()) {
            if (config('ds_config.sms_login') != 1) {
                ds_json_encode(10001,lang('enable_mobile_phone_login'));
            }
            $phone = input('post.sms_mobile');
            $captcha = input('post.sms_captcha');
            $condition = array();
            $condition[] = array('smslog_phone','=',$phone);
            $condition[] = array('smslog_captcha','=',$captcha);
            $condition[] = array('smslog_type','=',2);
            $smslog_model = model('smslog');
            $sms_log = $smslog_model->getSmsInfo($condition);
            if (empty($sms_log) || ($sms_log['smslog_smstime'] < TIMESTAMP - 1800)) {//半小时内进行验证为有效
                ds_json_encode(10001,lang('dynamic_code_expired'));
            }
            $member_model = model('member');
            $member = $member_model->getMemberInfo(array('member_mobile' => $phone)); //检查手机号是否已被注册
            if (!empty($member)) {
                $member_model->createSession($member); //自动登录
                ds_json_encode(10000,lang('login_index_login_success'), '','',false);
            }
        }
    }

    /**
     * 找回密码
     */
    public function find_password() {

        if (config('ds_config.sms_password') != 1) {
            ds_json_encode(10001,lang('mobile_back_password'));
        }
        $sms_mobile = trim(input('sms_mobile'));
        $sms_captcha = trim(input('sms_captcha'));
        $member_password = trim(input('member_password'));
        //判断验证码是否正确
        if ($sms_captcha != session('sms_captcha')) {
            ds_json_encode(10001,lang('login_index_wrong_checkcode'));
        }
        if ($sms_mobile != session('sms_mobile')) {
            ds_json_encode(10001,lang('receive_number_inconsistent'));
        }
        
        $condition = array();
        $condition[] = array('smslog_phone','=',$sms_mobile);
        $condition[] = array('smslog_captcha','=',$sms_captcha);
        $condition[] = array('smslog_type','=',3);
        $smslog_model = model('smslog');
        $sms_log = $smslog_model->getSmsInfo($condition);
        if (empty($sms_log) || ($sms_log['smslog_smstime'] < TIMESTAMP - 1800)) {//半小时内进行验证为有效
            ds_json_encode(10001,lang('dynamic_code_expired'));
        }

        $member_model = model('member');
        $member = $member_model->getMemberInfo(array('member_mobile' => $sms_mobile)); //检查手机号是否已被注册
        if (!empty($member)) {
            $member_model->editMember(array('member_id' => $member['member_id']), array('member_password' => md5($member_password)),$member['member_id']);
            $member_model->createSession($member); //自动登录
            ds_json_encode(10000,lang('password_changed_successfully'));
        }
    }

}
