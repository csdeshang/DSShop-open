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
class Mailtemplates extends Validate
{
    protected $rule = [
        'code'=>'require',
        'title'=>'require',
        'content'=>'require',
    ];
    protected $message = [
        'code.require'=>'编号不能为空',
        'title.require'=>'标题不能为空',
        'content.require'=>'正文不能为空',
    ];
    protected $scene = [
        'email_tpl_edit' => ['code','title', 'content'],
    ];
}