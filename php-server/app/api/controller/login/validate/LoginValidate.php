<?php

namespace app\api\controller\login\validate;

use app\deshang\base\BaseValidate;

class LoginValidate extends BaseValidate
{
    protected $rule = [
        'username'   => 'require|min:4|max:32|alphaNum',
        'password'   => 'require|min:6|max:32',
        'login_type' => 'in:user',
        'mobile'     => 'require|mobile',
        'code'       => 'require|checkMobileCode',
    ];

    protected $message = [
        'username.require'   => '用户名不能为空',
        'username.min'       => '用户名长度不能少于4个字符',
        'username.max'       => '用户名长度不能超过32个字符',
        'username.alphaNum'  => '用户名只能包含字母和数字',
        'password.require'   => '密码不能为空',
        'password.min'       => '密码长度不能少于6个字符',
        'password.max'       => '密码长度不能超过32个字符',
        'login_type.in'      => '登录类型无效，仅支持 user',
        'mobile.require'     => '手机号不能为空',
        'mobile.mobile'     => '手机号格式不正确',
        'code.require'       => '验证码不能为空',
        'code.checkMobileCode' => '验证码格式不正确，请输入6位数字',
    ];

    protected $scene = [
        'normal' => ['username', 'password', 'login_type'],
        'mobile' => ['mobile', 'code', 'login_type'],
    ];
} 