<?php

namespace app\api\controller\login\validate;

use app\deshang\base\BaseValidate;

class ResetPasswordValidate extends BaseValidate
{
    protected $rule = [
        'mobile' => 'require|mobile',
        'email' => 'require|email',
        'code' => 'require|checkMobileCode',
        'password' => 'require|max:32|min:6',
        'confirm_password' => 'require|confirm:password',
    ];

    protected $message = [
        'mobile.require' => '手机号不能为空',
        'mobile.mobile' => '手机号格式不正确',
        'email.require' => '邮箱地址不能为空',
        'email.email' => '邮箱格式不正确',
        'code.require' => '验证码不能为空',
        'code.checkMobileCode' => '验证码格式不正确，请输入6位数字',
        'password.require' => '新密码不能为空',
        'password.max' => '新密码长度不能超过50个字符',
        'password.min' => '新密码长度不能少于6个字符',
        'confirm_password.require' => '确认密码不能为空',
        'confirm_password.confirm' => '两次输入的密码不一致',
    ];

    protected $scene = [
        'mobile' => ['mobile', 'code', 'password', 'confirm_password'],
        'email' => ['email', 'code', 'password', 'confirm_password'],
    ];
} 