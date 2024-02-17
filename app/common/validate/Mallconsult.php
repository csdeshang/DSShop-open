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
class Mallconsult extends Validate
{
    protected $rule = [
        'mallconsulttype_name'=>'require',
        'mallconsulttype_sort'=>'require|number',
        'type_id'=>'require|number',
        'consult_content'=>'require'
    ];
    protected $message = [
        'mallconsulttype_name.require'=>'请填写咨询类型名称',
        'mallconsulttype_sort.require'=>'请正确填写咨询类型排序',
        'mallconsulttype_sort.number'=>'请正确填写咨询类型排序',
        'type_id.require'=>'请选择咨询类型',
        'type_id.number'=>'请选择咨询类型',
        'consult_content.require'=>'请填写咨询内容'
    ];
    protected $scene = [
        'type_add' => ['mallconsulttype_name', 'mallconsulttype_sort'],
        'type_edit' => ['mallconsulttype_name', 'mallconsulttype_sort'],
        'save_mallconsult' => ['type_id', 'consult_content'],
    ];
}