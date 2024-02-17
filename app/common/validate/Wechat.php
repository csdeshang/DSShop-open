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
class Wechat extends Validate
{
    protected $rule = [
        'name'=>'require',
        'sort'=>'number',
        'type'=>'require',
        'value'=>'checkValue:1'
    ];
    protected $message = [
        'name.require'=>'菜单名称不能为空',
        'sort.number'=>'排序只能为数字',
        'type.require'=>'类型不能为空',
        'value.checkValue'=>'URL地址错误'
    ];
    protected $scene = [
        'menu_add' => ['name', 'sort', 'type', 'value'],
        'menu_edit' => ['name', 'sort', 'type', 'value'],
    ];

    protected function checkValue($value){
        if(input('post.menu_type') == 'view'){
            if (empty($value)){
                return 'URL地址格式不能为空';
            }
            if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$value)) {
                return "URL地址格式不正确";
            }
        }
        return true;
    }
}