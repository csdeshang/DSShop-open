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
class Entityclerk extends Validate
{
    protected $rule = [
        'entityclerk_name'=>'require', 
        'entityclerk_phone'=>'require', 
        'member_id'=>'require', 
        'entityshop_id'=>'require', 
    ];
    protected $message = [
        'entityclerk_name.require'=>'店员姓名必填',
        'entityclerk_phone.require'=>'店员电话必填',
        'member_id.require'=>'用户ID必填',
        'entityshop_id.require'=>'门店必填',
    ];
    protected $scene = [
        'add' => ['entityclerk_name','entityclerk_phone','member_id','entityshop_id'],
        'edit' => ['entityclerk_name','entityclerk_phone']
    ];
}