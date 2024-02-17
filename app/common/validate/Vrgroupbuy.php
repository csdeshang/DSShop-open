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
class Vrgroupbuy extends Validate
{
    protected $rule = [
        'vrgclass_name'=>'require|length:1,10',
        'vrgclass_sort'=>'require|between:0,255'
    ];
    protected $message = [
        'vrgclass_name.require'=>'分类名不能为空且只能在1-10之间',
        'vrgclass_name.length'=>'分类名不能为空且只能在1-10之间',
        'vrgclass_sort.require'=>'分类排序不能为空且只能在0-255之间',
        'vrgclass_sort.between'=>'分类排序不能为空且只能在0-255之间'
    ];
    protected $scene = [
        'class_add' => ['vrgclass_name', 'vrgclass_sort'],
        'class_edit' => ['vrgclass_name', 'vrgclass_sort'],
    ];
}