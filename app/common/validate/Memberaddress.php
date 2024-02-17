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
class Memberaddress extends Validate
{
    protected $rule = [
        'city_id'=>'gt:0',
        'area_id'=>'gt:0',
        'address_realname'=>'require',
        'area_info'=>'require',
        'address_detail'=>'require',
        'address_mob_phone'=>'checkMemberAddressMobPhone:1'//

    ];
    protected $message = [
        'city_id.gt'=>'请选择地区',
        'area_id.gt'=>'地区至少两级',
        'address_realname.require'=>'姓名不能为空',
        'area_info.require'=>'地区不能为空',
        'address_detail.require'=>'地址不能为空',
        'address_mob_phone.checkMemberAddressMobPhone'=>'联系方式不能为空'//

    ];
    protected $scene = [
        'add' => ['address_realname', 'city_id', 'area_id'],
        'edit' => ['address_realname', 'city_id', 'area_id'],
        'address_valid' => ['address_realname', 'area_info', 'address_detail', 'address_mob_phone'],//mobile
    ];

    protected function checkMemberAddressMobPhone($value)
    {
        if (empty(input('post.mob_phone'))&&empty(input('post.tel_phone'))){
            if (empty($value)) {
                return '联系方式不能为空';
            }
        }
        return true;
    }

}