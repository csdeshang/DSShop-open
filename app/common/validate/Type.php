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
class Type extends Validate
{
    protected $rule = [
        'type_name'=>'require',
        'type_sort'=>'require|number',
        'class_id'=>'require|number',
        'attr_name'=>'require',
        'type_id'=>'require|number',
        'attr_show'=>'require|number',
        'attr_sort'=>'require|number',
    ];
    protected $message = [
        'type_name.require'=>'类型名不能为空',
        'type_sort.require'=>'请填写类型排序|类型排序必须为数字',
        'type_sort.number'=>'请填写类型排序|类型排序必须为数字',
        'class_id.require'=>'分类为必填|分类ID必须为数字',
        'class_id.number'=>'分类为必填|分类ID必须为数字',
        'attr_name.require'=>'属性名称为必填',
        'type_id.require'=>'类型ID为必填|类型ID必须为数字',
        'type_id.number'=>'类型ID为必填|类型ID必须为数字',
        'attr_show.require'=>'属性是否显示为必填|属性是否显示必须为数字',
        'attr_show.number'=>'属性是否显示为必填|属性是否显示必须为数字',
        'attr_sort.require'=>'属性排序为必填|属性排序必须为数字',
        'attr_sort.number'=>'属性排序为必填|属性排序必须为数字',
    ];
    protected $scene = [
        'type_add' => ['type_name', 'type_sort', 'class_id'],
        'type_edit' => ['type_name', 'type_sort', 'class_id'],
        'attr_edit' => ['attr_name', 'type_id', 'attr_show', 'attr_sort'],
    ];
}