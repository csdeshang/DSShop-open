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
class Brand extends Validate
{
    protected $rule = [
        'brand_name'=>'require',
        'brand_initial'=>'require',
        'brand_sort'=>'require|number'
    ];
    protected $message = [
        'brand_name.require'=>'品牌名称不能为空',
        'brand_initial.require'=>'请填写首字母',
        'brand_sort.require'=>'排序仅可以为数字',
        'brand_sort.number'=>'排序仅可以为数字'
    ];
    protected $scene = [
        'brand_add' => ['brand_name', 'brand_initial', 'brand_sort'],
        'brand_edit' => ['brand_name', 'brand_initial', 'brand_sort'],
    ];
}