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
class Member extends Validate
{
    protected $rule = [
        'member_name'=>'require|length:3,13|unique:member',
        'member_truename'=>'require|checkTruename',
        'member_idcard'=>'require|checkIdcard',
        'member_password'=>'require|length:6,20',
        'member_email'=>'email',
        'member_mobile'=>'mobile',
        'member_nickname'=>'max:10',
    ];
    protected $message  =   [
        'member_name.require'=>'用户名必填',
        'member_name.length'=>'用户名长度在3到13位',
        'member_name.unique' => '用户名已存在',
        'member_truename.require'=>'真实姓名必填',
        'member_truename.checkTruename'=>'真实姓名错误',
        'member_idcard.require'=>'身份证必填',
        'member_idcard.checkIdcard'=>'身份证错误',
        'member_password.require'=>'密码为必填',
        'member_password.length'=>'密码长度必须为6-20之间',
        'member_email.email'=>'邮箱格式错误',
        'member_mobile.length'=>'手机格式错误',
        'member_nickname.max'=>'真实姓名长度超过10位',
    ];
    protected $scene = [
        'add' => ['member_name', 'member_password', 'member_email'],
        'edit' => ['member_email', 'member_mobile', 'member_email'],
        'edit_information' => ['member_nickname'],
        'login' => ['member_password'],
        'register' => ['member_name', 'member_password'],
        'auth' => ['member_truename', 'member_idcard'],
    ];
    protected function checkTruename($value,$rule,$data)
    {
        return preg_match('/^[\x{4e00}-\x{9fa5}]{2,4}$/u',$value)?true:false;
    }
    protected function checkIdcard($value,$rule,$data)
    {
        return preg_match('/^[1-9]\d{5}(18|19|20|(3\d))\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/',$value)?true:false;
    }
}