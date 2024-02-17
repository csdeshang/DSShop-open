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
class Spec extends Validate
{
    protected $rule = [
        'sp_name'=>'require',
        'sp_sort'=>'require|number',
        'gc_id'=>'require|number',
    ];
    protected $message = [
        'sp_name.require'=>'规格名称为必填',
        'sp_sort.require'=>'规格排序为必填',
        'sp_sort.number'=>'规格排序必须为数字',
        'gc_id.require'=>'分类为必填',
        'gc_id.number'=>'分类ID必须为数字',
    ];
    protected $scene = [
        'spec_add' => ['sp_name', 'sp_sort', 'gc_id'],
        'spec_edit' => ['sp_name', 'sp_sort', 'gc_id'],
    ];
}