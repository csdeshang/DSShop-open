<?php

namespace app\common\validate;

use think\Validate;
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
 * 验证器
 */
class Membersecurity extends Validate
{
    protected $rule = [
        'email'=>'email',
        'password'=>'require',
        'confirm_password'=>'require',
        'mobile'=>'require',
        'vcode'=>'require',
    ];
    protected $message = [
        'email.email'=>'请正确填写邮箱',
        'password.require'=>'请正确输入密码',
        'confirm_password.require'=>'请正确输入确认密码',
        'mobile.require'=>'请正确填写手机号',
        'vcode.require'=>'请正确填写手机验证码',
    ];
    protected $scene = [
        'send_bind_email' => ['email'],
        'modify_pwd' => ['password', 'confirm_password'],
        'modify_paypwd' => ['password', 'confirm_password'],
        'modify_mobile' => ['mobile', 'vcode'],
        'send_modify_mobile' => ['mobile'],
    ];

}