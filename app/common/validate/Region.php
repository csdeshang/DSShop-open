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
class Region extends Validate
{
    protected $rule = [
        'area_name'=>'require',
        'area_sort'=>'between:0,255',
        'area_region'=>'length:0,9',
    ];
    protected $message = [
        'area_name.require'=>'地区名称不能为空',
        'area_sort.between'=>'排序必须为0-255间数字',
        'area_region.length'=>'大区名称必须小于三个字符',
    ];
    protected $scene = [
        'add' => ['area_name', 'area_sort', 'area_region'],
        'edit' => ['area_name', 'area_sort', 'area_region'],
    ];
}