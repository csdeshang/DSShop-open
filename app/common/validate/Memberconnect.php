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
class Memberconnect extends Validate
{
    protected $rule = [
        'new_password'=>'require|length:6,20',
        'confirm_password'=>'require|confirm:new_password',
    ];
    protected $message = [
        'new_password.require'=>'新密码为必填|密码长度必须为6-20之间',
        'new_password.length'=>'新密码为必填|密码长度必须为6-20之间',
        'confirm_password.require'=>'新密码与确认密码不相同，请从重新输入',
        'confirm_password.confirm'=>'新密码与确认密码不相同，请从重新输入',
    ];
    protected $scene = [
        'qqunbind' => ['new_password', 'confirm_password'],
        'sinaunbind' => ['new_password', 'confirm_password'],
        'weixinunbind' => ['new_password', 'confirm_password'],
    ];

}