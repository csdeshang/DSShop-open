<?php

namespace app\home\controller;
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
class Membersecurity extends BaseMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/memberpoints.lang.php');
    }

    public function index() {
        $member_info = $this->member_info;
        $member_info['security_level'] = model('member')->getMemberSecurityLevel($member_info);
        View::assign('member_info', $member_info);
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_security');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('index');
        return View::fetch($this->template_dir . 'index');
    }

    /**
     * 绑定邮箱 - 发送邮件
     */
    public function send_bind_email() {
        $email = input('param.email');

        $membersecurity_validate = ds_validate('membersecurity');
        if (!$membersecurity_validate->scene('send_bind_email')->check(array('email' => $email))) {
            ds_json_encode(10001, $membersecurity_validate->getError());
        }

        $member_model = model('member');
        $condition = array();

        $condition[]=array('member_email','=',$email);
        $condition[] = array('member_id','<>', session('member_id'));
        $member_info = $member_model->getMemberInfo($condition, 'member_id');
        if ($member_info) {
            ds_json_encode(10001, lang('mailbox_has_been_used'));
        }


        //验证发送频率
        $verify_code_model = model('verify_code');
        $result = $verify_code_model->isVerifyCodeFrequant(5, 1);
        if (!$result['code']) {
            ds_json_encode(10001, $result['msg']);
        }

        $verify_code = $verify_code_model->genVerifyCode(5, 1);
        if (!$verify_code) {
            ds_json_encode(10001, lang('system_error'));
        }

        $uid = base64_encode(ds_encrypt(session('member_id') . ' ' . $email));
        $verify_url = HOME_SITE_URL . '/Login/bind_email.html?uid=' . $uid . '&hash=' . md5($verify_code);

        $mailtemplates_model = model('mailtemplates');
        $tpl_info = $mailtemplates_model->getTplInfo(array('mailmt_code' => 'bind_email'));
        $param = array();
        $param['site_name'] = config('ds_config.site_name');
        $param['user_name'] = session('member_name');
        $param['verify_url'] = $verify_url;
        $subject = ds_replace_text($tpl_info['mailmt_title'], $param);
        $message = ds_replace_text($tpl_info['mailmt_content'], $param);
        $message = htmlspecialchars_decode($message);

        $ob_email = new \sendmsg\Email();
        $result = $ob_email->send_sys_email($email, $subject, $message);
        if ($result) {
            $ip = request()->ip();
            $flag = $verify_code_model->addVerifyCode(array(
                'verify_code_type' => 5,
                'verify_code' => $verify_code,
                'verify_code_user_type' => 1,
                'verify_code_user_id' => session('member_id'),
                'verify_code_user_name' => session('member_name'),
                'verify_code_add_time' => TIMESTAMP,
                'verify_code_ip' => $ip,
            ));
            if (!$flag) {
                ds_json_encode(10001, lang('system_error'));
            }


            $data = array();
            $data['member_email'] = $email;
            $data['member_emailbind'] = 0;
            $member_model->editMember(array('member_id' => session('member_id')), $data,session('member_id'));
            ds_json_encode(10000, lang('verify_mail_been_sent_mailbox'));
        } else {
            ds_json_encode(10001, lang('system_error'));
        }
    }

    public function auth() {
        $member_model = model('member');
        $type = input('param.type');
        if (!request()->isPost()) {
            if (!in_array($type, array('modify_pwd', 'modify_mobile', 'modify_email', 'modify_paypwd'))) {
                $this->redirect('Membersecurity/index');
            }
            //继承父类的member_info
            $member_info = $this->member_info;
            if (!$member_info) {
                $member_info = $member_model->getMemberInfo(array('member_id' => session('member_id')), 'member_email,member_emailbind,member_mobile,member_mobilebind');
            }
            //第一次绑定邮箱，不用发验证码，直接进下一步
            //第一次绑定手机，不用发验证码，直接进下一步
            if (($type == 'modify_email' && $member_info['member_emailbind'] == '0') || ($type == 'modify_mobile' && $member_info['member_mobilebind'] == '0')) {
                session('auth_' . $type, TIMESTAMP);
                /* 设置买家当前菜单 */
                $this->setMemberCurMenu('member_security');
                /* 设置买家当前栏目 */
                $this->setMemberCurItem($type);
                echo View::fetch($this->template_dir . $type);
                exit;
            }

            //修改密码、设置支付密码时，必须绑定邮箱或手机
            if (in_array($type, array('modify_pwd', 'modify_paypwd')) && $member_info['member_emailbind'] == '0' && $member_info['member_mobilebind'] == '0') {
                $this->error(lang('please_bind_email_phone_first'), 'membersecurity/index');
            }
            View::assign('member_info', $member_info);
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_security');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem($type);
            return View::fetch($this->template_dir . 'auth');
        } else {
            if (!in_array($type, array('modify_pwd', 'modify_mobile', 'modify_email', 'modify_paypwd'))) {
                $this->redirect(url('Membersecurity/index'));
            }

            $verify_code = input('post.auth_code');
            $validate_data = array(
                'verify_code' => $verify_code,
            );
            $verify_code_validate = ds_validate('verify_code');
            if (!$verify_code_validate->scene('verify_code_search')->check($validate_data)) {
                $this->error($verify_code_validate->getError());
            }
            $verify_code_model = model('verify_code');
            if (!$verify_code_model->getVerifyCodeInfo(array(array('verify_code_type' ,'=', 6), array('verify_code_user_type' ,'=', 1), array('verify_code_user_id' ,'=', session('member_id')), array('verify_code' ,'=', $verify_code), array('verify_code_add_time','>', TIMESTAMP - VERIFY_CODE_INVALIDE_MINUTE * 60)))) {
                $this->error(lang('validation_fails'));
            }

            
            session('auth_' . $type, TIMESTAMP);

            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_security');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem($type);
            return View::fetch($this->template_dir . $type);
        }
    }

    /**
     * 统一发送身份验证码
     */
    public function send_auth_code() {
        $type = input('param.type');
        if (!in_array($type, array('email', 'mobile')))
            exit();

        $member_model = model('member');
        $member_info = $member_model->getMemberInfoByID(session('member_id'));



        //验证发送频率
        $verify_code_model = model('verify_code');
        $result = $verify_code_model->isVerifyCodeFrequant(6, 1);
        if (!$result['code']) {
            exit(json_encode(array('state' => 'false', 'msg' => $result['msg'])));
        }

        $verify_code = $verify_code_model->genVerifyCode(6, 1);
        if (!$verify_code) {
            exit(json_encode(array('state' => 'false', 'msg' => lang('system_error'))));
        }
        $mailtemplates_model = model('mailtemplates');
        $tpl_info = $mailtemplates_model->getTplInfo(array('mailmt_code' => 'authenticate'));

        $param = array();
        $param['code'] = $verify_code;
        $ten_param=array($verify_code);
        $subject = ds_replace_text($tpl_info['mailmt_title'], $param);
        $message = ds_replace_text($tpl_info['mailmt_content'], $param);
        if ($type == 'email') {
            $email = new \sendmsg\Email();
            $result['state'] = $email->send_sys_email($member_info["member_email"], $subject, $message);
        } elseif ($type == 'mobile') {
            $smslog_param=array(
                    'ali_template_code'=>$tpl_info['ali_template_code'],
                    'ali_template_param'=>$param,
                    'ten_template_code'=>$tpl_info['ten_template_code'],
                    'ten_template_param'=>$ten_param,
                    'message'=>$message,
                );
            $result = model('smslog')->sendSms($member_info["member_mobile"], $smslog_param,5,$verify_code);
        }
        if ($result['state']) {
            $ip = request()->ip();
            $flag = $verify_code_model->addVerifyCode(array(
                'verify_code_type' => 6,
                'verify_code' => $verify_code,
                'verify_code_user_type' => 1,
                'verify_code_user_id' => session('member_id'),
                'verify_code_user_name' => session('member_name'),
                'verify_code_add_time' => TIMESTAMP,
                'verify_code_ip' => $ip,
            ));
            if (!$flag) {
                exit(json_encode(array('state' => 'false', 'msg' => lang('system_error'))));
            }
            exit(json_encode(array('state' => 'true', 'msg' => lang('verification_code_has_been_sent'))));
        } else {
            exit(json_encode(array('state' => 'false', 'msg' => isset($result['message']) ? $result['message'] : lang('verification_code_sending_failed'))));
        }
    }


    /**
     * 修改密码
     */
    public function modify_pwd() {
        $member_model = model('member');

        //身份验证后，需要在30分钟内完成修改密码操作
        if (TIMESTAMP - session('auth_modify_pwd') > 1800) {
            ds_json_encode(10001,lang('operation_timed_out'));
        }

        if (!request()->isPost())
            exit();
        $data = array(
            'password' => input('post.password'), 'confirm_password' => input('post.confirm_password'),
        );
        //验证数据  BEGIN
        $membersecurity_validate = ds_validate('membersecurity');
        if (!$membersecurity_validate->scene('modify_pwd')->check($data)) {
            ds_json_encode(10001,$membersecurity_validate->getError());
        }
        //验证数据  END

        if ($data['password'] != $data['confirm_password']) {
            ds_json_encode(10001,lang('two_password_inconsistencies'));
        }

        $update = $member_model->editMember(array('member_id' => session('member_id')), array('member_password' => md5($data['password'])),session('member_id'));
        session('auth_modify_pwd', NULL);
        if ($update){
            ds_json_encode(10000,lang('password_modify_successfully'));
        }else{
            ds_json_encode(10001,lang('operation_timed_out'));
        }
    }

    /**
     * 设置支付密码
     */
    public function modify_paypwd() {
        $member_model = model('member');

        //身份验证后，需要在30分钟内完成修改密码操作
        if (TIMESTAMP - session('auth_modify_paypwd') > 1800) {
            $this->error(lang('operation_timed_out'), url('Membersecurity/auth', ['type' => 'modify_paypwd']));
        }
        if (!request()->isPost())
            exit();
        $data = array(
            'password' => input('post.password'),
            'confirm_password' => input('post.confirm_password'),
        );

        //验证数据  BEGIN
        $membersecurity_validate = ds_validate('membersecurity');
        if (!$membersecurity_validate->scene('modify_paypwd')->check($data)) {
            ds_json_encode(10001,$membersecurity_validate->getError());
        }
        //验证数据  END

        if ($data['password'] != $data['confirm_password']) {
            ds_json_encode(10001,lang('two_password_inconsistencies'));
        }

        $update = $member_model->editMember(array('member_id' => session('member_id')), array('member_paypwd' => md5($data['password'])),session('member_id'));
        session('auth_modify_paypwd', NULL);

        if ($update){
            ds_json_encode(10000,lang('password_set_successfully'));
        }else{
            ds_json_encode(10001,lang('password_setting_failed'));
        }
    }

    /**
     * 绑定手机
     */
    public function modify_mobile() {
        $member_model = model('member');
        $member_model->getMemberInfoByID(session('member_id'));
        if (request()->isPost()) {
            $data = array(
                'mobile' => input('post.mobile'),
                'vcode' => input('post.vcode'),
            );

            $membersecurity_validate = ds_validate('membersecurity');
            if (!$membersecurity_validate->scene('modify_mobile')->check($data)) {
                ds_json_encode(10001, $membersecurity_validate->getError());
            }



            $verify_code = input('post.vcode');
            $validate_data = array(
                'verify_code' => $verify_code,
            );
            $verify_code_validate = ds_validate('verify_code');
            if (!$verify_code_validate->scene('verify_code_search')->check($validate_data)) {
                ds_json_encode(10001, $verify_code_validate->getError());
            }
            $verify_code_model = model('verify_code');
            if (!$verify_code_model->getVerifyCodeInfo(array(array('verify_code_type' ,'=', 4), array('verify_code_user_type' ,'=', 1), array('verify_code_user_id' ,'=', session('member_id')), array('verify_code' ,'=', $verify_code), array('verify_code_add_time','>', TIMESTAMP - VERIFY_CODE_INVALIDE_MINUTE * 60)))) {
                ds_json_encode(10001, lang('mobile_verification_code_error'));
            }

            $member_model->editMember(array('member_id' => session('member_id')), array('member_mobilebind' => 1),session('member_id'));
            ds_json_encode(10000, lang('phone_number_bound_successfully'));
        }
    }

    /**
     * 修改手机号 - 发送验证码
     */
    public function send_modify_mobile() {
        $mobile = input('param.mobile');
        $membersecurity_validate = ds_validate('membersecurity');
        if (!$membersecurity_validate->scene('send_modify_mobile')->check(array('mobile' => $mobile))) {
            exit(json_encode(array('state' => 'false', 'msg' => $membersecurity_validate->getError())));
        }

        $member_model = model('member');
        $condition = array();
        $condition[]=array('member_mobile','=',$mobile);
        $condition[] = array('member_id','<>', session('member_id'));
        $member_info = $member_model->getMemberInfo($condition, 'member_id');
        if ($member_info) {
            exit(json_encode(array('state' => 'false', 'msg' => lang('please_change_another_phone_number'))));
        }

        //验证发送频率
        $verify_code_model = model('verify_code');
        $result = $verify_code_model->isVerifyCodeFrequant(4, 1);
        if (!$result['code']) {
            exit(json_encode(array('state' => 'false', 'msg' => $result['msg'])));
        }

        $verify_code = $verify_code_model->genVerifyCode(4, 1);
        if (!$verify_code) {
            exit(json_encode(array('state' => 'false', 'msg' => lang('system_error'))));
        }


        $mailtemplates_model = model('mailtemplates');
        $tpl_info = $mailtemplates_model->getTplInfo(array('mailmt_code' => 'modify_mobile'));
        $param = array();
        $param['code'] = $verify_code;
        $ten_param=array($verify_code);
        $message = ds_replace_text($tpl_info['mailmt_content'], $param);

        $smslog_param=array(
                    'ali_template_code'=>$tpl_info['ali_template_code'],
                    'ali_template_param'=>$param,
                    'ten_template_code'=>$tpl_info['ten_template_code'],
                    'ten_template_param'=>$ten_param,
                    'message'=>$message,
                );
        $result = model('smslog')->sendSms($mobile, $smslog_param,4,$verify_code);

        if (!$result['state']) {
            exit(json_encode(array('state' => 'false', 'msg' => $result['message'])));
        }
        $ip = request()->ip();
        $flag = $verify_code_model->addVerifyCode(array(
            'verify_code_type' => 4,
            'verify_code' => $verify_code,
            'verify_code_user_type' => 1,
            'verify_code_user_id' => session('member_id'),
            'verify_code_user_name' => session('member_name'),
            'verify_code_add_time' => TIMESTAMP,
            'verify_code_ip' => $ip,
        ));
        if (!$flag) {
            exit(json_encode(array('state' => 'false', 'msg' => lang('system_error'))));
        }
        $update = $member_model->editMember(array('member_id' => session('member_id')), array('member_mobile' => $mobile),session('member_id'));
        if (!$update) {
            exit(json_encode(array('state' => 'false', 'msg' => lang('modified_phone_same_original_one'))));
        } else {
            exit(json_encode(array('state' => 'true', 'msg' => lang('send_success'))));
        }
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    protected function getMemberItemList() {
        $menu_name = request()->action();
        switch ($menu_name) {
            case 'index':
                $menu_array = array(
                    array(
                        'name' => 'index', 'text' => lang('account_security'),
                        'url' => url('Membersecurity/index')
                    )
                );
                return $menu_array;
                break;
            case 'modify_pwd':
                $menu_array = array(
                    array(
                        'name' => 'index', 'text' => lang('account_security'),
                        'url' => url('Membersecurity/index')
                    ), array(
                        'name' => 'modify_pwd', 'text' => lang('change_login_password'),
                        'url' => url('Membersecurity/auth', ['type' => 'modify_pwd'])
                    ),
                );
                return $menu_array;
                break;
            case 'modify_email':
                $menu_array = array(
                    array(
                        'name' => 'index', 'text' => lang('account_security'),
                        'url' => url('Membersecurity/index')
                    ), array(
                        'name' => 'modify_email', 'text' => lang('email_address_verification'),
                        'url' => url('Membersecurity/auth', ['type' => 'modify_email'])
                    ),
                );
                return $menu_array;
                break;
            case 'modify_mobile':
                $menu_array = array(
                    array(
                        'name' => 'index', 'text' => lang('account_security'),
                        'url' => url('Membersecurity/index')
                    ), array(
                        'name' => 'modify_mobile', 'text' => lang('phone_verification'),
                        'url' => url('Membersecurity/auth', ['type' => 'modify_mobile'])
                    ),
                );
                return $menu_array;
                break;
            case 'modify_paypwd':
                $menu_array = array(
                    array(
                        'name' => 'index', 'text' => lang('account_security'),
                        'url' => url('Membersecurity/index')
                    ), array(
                        'name' => 'modify_paypwd', 'text' => lang('set_payment_password'),
                        'url' => url('Membersecurity/auth', ['type' => 'modify_paypwd'])
                    ),
                );
                return $menu_array;
                break;
        }
    }

}

?>
