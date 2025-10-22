<?php

namespace app\home\controller;
use think\facade\View;
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
class Login extends BaseMall {
    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/login.lang.php');
    }
    /**
     * 用户登录
     * @return
     */
    public function login() {
        $member_model = model('member');
        $inajax = input('param.inajax');
        //检查登录状态
        $member_model->checkloginMember();
        if (!request()->isPost()) {
            $ref_url='';
            if(session('ref_url')){
              $ref_url=session('ref_url');
            }
            View::assign('ref_url',  $ref_url);
            if ($inajax==1) {
                return View::fetch($this->template_dir . 'login_inajax');
            } else {
                return View::fetch($this->template_dir . 'login');
            }
        } else {
            if (config('ds_config.captcha_status_login') == 1 && !captcha_check(input('post.captcha_normal'))) {
                ds_json_encode(10001,lang('image_verification_code_error'));
            }
            if (empty(input('post.member_name')) || empty(input('post.member_password'))) {
                ds_json_encode(10001, lang('param_error'));
            }
            $data = array(
                'member_name' => input('post.member_name'),
                'member_password' => input('post.member_password'),
            );
            //验证数据  END
            $map = array(
                'member_name' => $data['member_name'],
                'member_password' => md5($data['member_password']),
            );
            $member_info = $member_model->getMemberInfo($map);
            if (empty($member_info) && preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $data['member_name'])) {
                //根据会员名没找到时查手机号
                $map = array();
                $map['member_mobile'] = $data['member_name'];
                $map['member_mobilebind'] = 1;
                $map['member_password'] = md5($data['member_password']);
                $member_info = Db::name('member')->where($map)->find();
            }
            if (empty($member_info) && (strpos($data['member_name'], '@') > 0)) {
                //按邮箱和密码查询会员
                $map = array();
                $map['member_email'] = $data['member_name'];
                $map['member_password'] = md5($data['member_password']);
                $member_info = Db::name('member')->where($map)->find();
            }
            if ($member_info) {
                if (!$member_info['member_state']) {
                    ds_json_encode(10001, lang('login_index_account_stop'));
                }
                //执行登录,赋值操作
                $member_model->createSession($member_info,'login');
                ds_json_encode(10000,lang('login_index_login_success'), '','',false);
            } else {
                ds_json_encode(10001,lang('login_index_login_fail'));
            }
        }
    }

    public function logout() {
        Cookie('cart_goods_num','');
        Cookie('msgnewnum'.session('member_id'),'');
        session(null);
        $this->redirect('Index/index');
    }

    /**
     * 会员注册页面
     *
     * @param
     * @return
     */
    public function register() {
        if (!request()->isPost()) {
            $member_model = model('member');
            $member_model->checkloginMember();
            if(input('param.inviter_id')){
                $inviter_id = intval(input('param.inviter_id'));
                cookie('inviter_id',$inviter_id);
            }else{
                $inviter_id = intval(cookie('inviter_id'));
            }
            
            $member = Db::name('member')->where('member_id', $inviter_id)->field('member_id,member_name')->find();
            View::assign('member', $member);
            return View::fetch($this->template_dir . 'register');
        } else {
            $register_type = input('post.register_type');
            if ((!config('ds_config.sms_register') || !$register_type) && config('ds_config.captcha_status_register') == 1 && !captcha_check(input('post.captcha_normal'))) {
                ds_json_encode(10001,lang('image_verification_code_error'));
            }
            $member_model = model('member');
            $member_model->checkloginMember();
            $password = input('post.member_password');
            $password_confirm = input('post.member_password_confirm');
            if ($password != $password_confirm) {
                ds_json_encode(10001,lang('login_passwords_not_match'));
            }

            $data = array(
                'member_name' => trim(input('post.member_name')),
                'member_password' => $password,
                'member_password_confirm' => $password_confirm,
            );
            $res=word_filter($data['member_name']);
            if(!$res['code']){
                ds_json_encode(10001,$res['msg']);
            }
            if($res['data']['if_sensitive']){
                ds_json_encode(10001,implode('、',$res['data']['sensitive_msg']));
            }
            if(input('param.inviter_id')){
                $inviter_id = intval(input('param.inviter_id'));
            }else{
                $inviter_id = intval(cookie('inviter_id'));
            }
            cookie('inviter_id','');
            $data['inviter_id'] = $inviter_id;
            //是否开启验证码
            if (config('ds_config.sms_register')==1 && $register_type) {
                $sms_mobile = trim(input('sms_mobile'));
                $sms_captcha = trim(input('sms_captcha'));
                $logic_connect_api = model('connectapi','logic');
                $state_data = $logic_connect_api->smsRegister($sms_mobile, $sms_captcha, $password,$inviter_id);
                if($state_data['state']=='1'){
                    $member_info = $state_data['info'];
                }
            }else if(config('ds_config.member_normal_register')==1){
                $member_info = $member_model->register($data);
            }else{
                ds_json_encode(10001,lang('login_register_cancel'));
            }

            
            if (!isset($member_info['error'])) {
                $member_model->createSession($member_info, 'register');
                ds_json_encode(10000,lang('login_usersave_regist_success'), '','',false);
            } else {
                ds_json_encode(10001,$member_info['error']);
            }
        }
    }


    /**
     * 会员名称检测
     *
     * @param
     * @return
     */
    public function check_member() {
        $member_name = input('param.member_name');
        $member_model = model('member');
        if(empty($member_name)){
            echo 'false';exit;
        }
        $check_member_name = $member_model->getMemberInfo(array('member_name' => $member_name));
        if (is_array($check_member_name) && count($check_member_name) > 0) {
            echo 'false';exit;
        } else {
            echo 'true';exit;
        }
    }

    /**
     * 电子邮箱检测
     *
     * @param
     * @return
     */
    public function check_email() {
        $member_model = model('member');
        $check_member_email = $member_model->getMemberInfo(array('member_email' => input('param.email')));
        if (is_array($check_member_email) && count($check_member_email) > 0) {
            echo 'false';exit;
        } else {
            echo 'true';exit;
        }
    }

    /**
     * 忘记密码页面
     */
    public function forget_password() {
        View::assign('html_title', config('ds_config.site_name') . ' - ' . lang('quick_login_forget'));
        return View::fetch($this->template_dir . 'find_password');
    }

    /**
     * 邮箱绑定验证
     */
    public function bind_email() {

        $member_model = model('member');
        $uid = @base64_decode(input('param.uid'));
        $uid = ds_decrypt($uid, '');
        list($member_id, $member_email) = explode(' ', $uid);
        if (!is_numeric($member_id)) {
            $this->error(lang('validation_fails'), HOME_SITE_URL);
        }

        $member_info = $member_model->getMemberInfo(array('member_id' => $member_id), 'member_email');
        if ($member_info['member_email'] != $member_email) {
            $this->error(lang('validation_fails'), HOME_SITE_URL);
        }

        $hash = array_keys($_GET);
        $verify_code_model = model('verify_code');
        $verify_code_info = $verify_code_model->getVerifyCodeInfo(array(array('verify_code_type' ,'=', 5), array('verify_code_user_type' ,'=', 1), array('verify_code_user_id' ,'=', $member_id), array('verify_code_add_time','>', TIMESTAMP - VERIFY_CODE_INVALIDE_MINUTE * 60)));
        if (!$verify_code_info || md5($verify_code_info['verify_code']) != $_GET[$hash['1']]) {
            $this->error(lang('validation_fails'), HOME_SITE_URL);
        }


        $update = $member_model->editMember(array('member_id' => $member_id), array('member_emailbind' => 1),$member_id);
        if (!$update) {
            $this->error(lang('system_error'), HOME_SITE_URL);
        }

        $this->success(lang('successful_email_setting'), url('Membersecurity/index'));
    }

}

?>
