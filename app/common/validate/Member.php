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
        'member_truename'=>'checkTruename',
        'member_idcard'=>'require|checkIdcard',
        'member_password'=>'require|length:6,20',
        'member_email'=>'email',
        'member_mobile'=>'mobile|unique:member',
        'member_nickname'=>'max:20',
        'member_qq'=>'length:5,12',
        'member_ww'=>'length:5,20',
        
        'member_qqopenid'=>'unique:member',
        'member_sinaopenid'=>'unique:member',
        'member_wxunionid'=>'unique:member',
    ];
    protected $message  =   [
        'member_name.require'=>'用户名必填',
        'member_name.length'=>'用户名长度在3到13位',
        'member_name.unique' => '用户名已存在',
        'member_truename.checkTruename'=>'真实姓名错误',
        'member_idcard.require'=>'身份证必填',
        'member_idcard.checkIdcard'=>'身份证错误',
        'member_password.require'=>'密码为必填',
        'member_password.length'=>'密码长度必须为6-20之间',
        'member_email.email'=>'邮箱格式错误',
        'member_mobile.mobile'=>'手机格式错误',
        'member_mobile.unique'=>'手机号已被绑定',
        'member_nickname.max'=>'会员昵称长度不能超过20位',
        'member_qq.length'=>'QQ的长度应该在5至12位之间',
        'member_ww.length'=>'旺旺的长度应该在5至20位之间',
        
        'member_qqopenid.unique'=>'QQ号已被绑定',
        'member_sinaopenid.unique'=>'Sina号已被绑定',
        'member_wxunionid.unique'=>'微信号已被绑定',
    ];
    protected $scene = [
        'model_add' => ['member_name', 'member_password', 'member_email', 'member_mobile','member_nickname','member_qq','member_ww','member_qqopenid','member_sinaopenid','member_wxunionid'],
        'model_edit' => ['member_email', 'member_mobile','member_nickname','member_qq','member_ww','member_qqopenid','member_sinaopenid','member_wxunionid'],
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