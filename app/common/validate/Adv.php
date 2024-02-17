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
class Adv extends Validate
{
    protected $rule = [
        'ap_name'=>'require',
        'ap_width'=>'require',
        'ap_height'=>'require',
        'adv_title'=>'require',
        'ap_id'=>'require',
        'adv_startdate'=>'require',
        'adv_enddate'=>'require',
        'adv_typedate'=>'max:255'
    ];
    protected $message = [
        'ap_name.require'=>'广告位名称不能为空',
        'ap_width.require'=>'广告位宽度只能为数字形式',
        'ap_height.require'=>'广告位高度只能为数字形式',
        'adv_title.require'=>'名称不能为空',
        'ap_id.require'=>'必须选择一个广告位',
        'adv_startdate.require'=>'必须选择开始时间',
        'adv_enddate.require'=>'必须选择结束时间',
        'adv_typedate.max'=>'操作值必须小于255个字符'
    ];
    protected $scene = [
        'ap_add' => ['ap_name', 'ap_width', 'ap_height'],
        'ap_edit' => ['ap_name', 'ap_width', 'ap_height'],
        'adv_add' => ['adv_title', 'ap_id', 'adv_startdate', 'adv_enddate'],
        'adv_edit' => ['adv_title', 'adv_startdate', 'adv_enddate'],
        'app_ap_add' => ['ap_name', 'ap_width', 'ap_height'],
        'app_ap_edit' => ['ap_name', 'ap_width', 'ap_height'],
        'app_adv_add' => ['adv_title', 'ap_id', 'adv_startdate', 'adv_enddate','adv_typedate'],
        'app_adv_edit' => ['adv_title', 'adv_startdate', 'adv_enddate','adv_typedate'],
    ];
}