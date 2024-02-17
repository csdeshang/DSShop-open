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
class Consulting extends Validate
{
    protected $rule = array(
       'consulttype_name'=>'require',
       'consulttype_sort'=>'require|number',
    );
    protected $message = array(
       'consulttype_name.require'=>'请填写咨询类型名称',
       'consulttype_sort.require'=>'请正确填写咨询类型排序',
       'consulttype_sort.number'=>'请正确填写咨询类型排序',
    );
    protected $scene = [
        'type_add' => ['consulttype_name', 'sort'],
        'type_edit' => ['consulttype_name', 'sort'],
    ];
}