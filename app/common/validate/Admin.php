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
class Admin extends Validate
{
    protected  $rule = [
        'admin_name'=>'require|length:3,12',
        'admin_password'=>'require|min:6',
        'admin_gid'=>'require',
        'captcha'=>'require|min:3',
    ];
    protected  $message = [
        'admin_name.require'=>'登录名必填',
        'admin_name.length'=>'登录名长度在3到12位',
        'admin_password.require'=>'密码为必填',
        'admin_password.min'=>'密码长度至少为6位',
        'admin_gid.require'=>'权限组为必填',
        'captcha.require'=>'验证码为必填',
        'captcha.min'=>'验证码长度至少为3位',
    ];
    protected $scene = [
        'admin_add' => ['admin_name', 'admin_password', 'admin_gid'],
        'index' => ['admin_name', 'admin_password', 'captcha'],//login index
    ];
}