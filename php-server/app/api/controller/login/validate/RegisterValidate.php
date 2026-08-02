<?php

namespace app\api\controller\login\validate;

use app\deshang\base\BaseValidate;

class RegisterValidate extends BaseValidate
{
    protected $rule = [
        'username' => 'require|min:4|max:32|alphaNum',
        'password' => 'require|max:32|min:6',
        'confirm_password' => 'require|confirm:password',
        'mobile' => 'require|mobile',
        'code' => 'require|checkMobileCode',
        'inviter_id' => 'integer|egt:0',
    ];

    protected $message = [
        'username.require' => '用户名不能为空',
        'username.min' => '用户名长度不能少于4个字符',
        'username.max' => '用户名长度不能超过32个字符',
        'username.alphaNum' => '用户名只能包含字母和数字',
        'password.require' => '密码不能为空',
        'password.max' => '密码长度不能超过50个字符',
        'password.min' => '密码长度不能少于6个字符',
        'confirm_password.require' => '确认密码不能为空',
        'confirm_password.confirm' => '两次输入的密码不一致',
        'mobile.require' => '手机号不能为空',
        'mobile.mobile' => '手机号格式不正确',
        'code.require' => '验证码不能为空',
        'code.checkMobileCode' => '验证码格式不正确，请输入6位数字',
        'inviter_id.integer' => '邀请人ID必须为整数',
        'inviter_id.egt' => '邀请人ID不能小于0',
    ];

    protected $scene = [
        'normal' => ['username', 'password', 'confirm_password', 'inviter_id'],
        'mobile' => ['username', 'password', 'confirm_password', 'mobile', 'code', 'inviter_id'],
    ];
} 