<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserValidate extends BaseValidate
{
    protected $rule = [
        'old_password' => 'require|min:1',
        'password' => 'require|min:6|max:32',
        'confirm_password' => 'require|confirm:password',
        'nickname' => 'max:32',
        'avatar' => 'max:255',
        'sex' => 'in:0,1,2',
        'birthday' => 'integer',
        'idcard_name' => 'require|length:1,32',
        'idcard_number' => 'require|idCard',
        'idcard_image1' => 'require|max:255',
        'idcard_image2' => 'require|max:255',
        'idcard_image3' => 'require|max:255',
    ];

    protected $message = [
        'old_password.require' => '请输入原密码',
        'old_password.min' => '原密码不能为空',
        'password.require' => '请输入新密码',
        'password.min' => '新密码长度不能少于6位',
        'password.max' => '新密码长度不能超过32位',
        'confirm_password.require' => '请输入确认密码',
        'confirm_password.confirm' => '两次输入的密码不一致',
        'nickname.max' => '昵称长度不能超过32位',
        'avatar.max' => '头像URL长度不能超过255位',
        'sex.in' => '性别值无效',
        'birthday.integer' => '生日必须为整数',
        'idcard_name.require' => '请输入真实姓名',
        'idcard_name.length' => '真实姓名长度为1-32个字符',
        'idcard_number.require' => '请输入身份证号',
        'idcard_number.idCard' => '身份证号格式错误',
        'idcard_image1.require' => '请上传手持身份证照',
        'idcard_image1.max' => '手持身份证照URL过长',
        'idcard_image2.require' => '请上传身份证正面照',
        'idcard_image2.max' => '身份证正面照URL过长',
        'idcard_image3.require' => '请上传身份证反面照',
        'idcard_image3.max' => '身份证反面照URL过长',
    ];

    protected $scene = [
        'editPassword' => ['old_password', 'password', 'confirm_password'],
        'updateInfo' => ['nickname', 'avatar', 'sex', 'birthday'],
        'editIdcard' => ['idcard_name', 'idcard_number', 'idcard_image1', 'idcard_image2', 'idcard_image3'],
    ];
} 