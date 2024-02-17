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
class Album extends Validate
{
    protected $rule = [
        'aclass_name'=>'require', 
        'aclass_des'=>'require', 
        'aclass_sort'=>'require', 
    ];
    protected $message = [
        'aclass_name.require'=>'相册名称必填',
        'aclass_des.require'=>'相册描述必填',
        'aclass_sort.require'=>'相册排序必填'
    ];
    protected $scene = [
        'album_add' => ['aclass_name','aclass_des','aclass_sort']
    ];

}