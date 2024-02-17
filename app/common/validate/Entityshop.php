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
class Entityshop extends Validate
{
    protected $rule = [
        'entityshop_name'=>'require', 
        'entityshop_linkname'=>'require', 
        'entityshop_phone'=>'require', 
    ];
    protected $message = [
        'entityshop_name.require'=>'门店名称必填',
        'entityshop_linkname.require'=>'联系人必填',
        'entityshop_phone.require'=>'联系电话必填'
    ];
    protected $scene = [
        'add' => ['entityshop_name','entityshop_linkname','entityshop_phone'],
        'edit' => ['entityshop_name','entityshop_linkname','entityshop_phone']
    ];
}