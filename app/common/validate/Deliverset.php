<?php
namespace app\common\validate;
use think\Validate;/**
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
class Deliverset extends Validate
{
    protected $rule = [
        'seller_name'=>'require', 
        'daddress_detail'=>'require', 
        'daddress_telphone'=>'require', 
        'seal_printexplain'=>'require|length:1,200',
    ];
    protected $message = [
        'seller_name.require'=>'联系人必填',
        'daddress_detail.require'=>'地址必填',
        'daddress_telphone.require'=>'电话必填',
        'seal_printexplain.require'=>'说明不能为空',
        'seal_printexplain.length'=>'长度在1-200之间'
    ];
    protected $scene = [
        'daddress_add' => ['seller_name', 'daddress_detail', 'daddress_telphone'],
        'print_set' => ['seal_printexplain'],
    ];
}