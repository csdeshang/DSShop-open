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
class Activity extends Validate
{
    protected $rule = [
        'activity_title'=>'require',
        'activity_startdate'=>'require',
        'activity_enddate'=>'require|checkEnddate:1',
        'activity_type'=>'require',
        'activity_banner'=>'require',
        'activity_sort'=>'require'
    ];
    protected $message = [
        'activity_title.require'=>'活动标题不能为空',
        'activity_startdate.require'=>'开始时间不能为空',
        'activity_enddate.require'=>'结束时间不能为空',
        'activity_enddate.checkEnddate'=>'结束时间不能为空',
        'activity_type.require'=>'必须选择活动类别',
        'activity_banner.require'=>'横幅图片不能为空',
        'activity_sort.require'=>'排序为0~255的数字'
    ];
    protected $scene = [
        'add' => ['activity_title', 'activity_startdate', 'activity_enddate', 'activity_type', 'activity_banner', 'activity_sort'],
        'edit' => ['activity_title', 'activity_startdate', 'activity_enddate', 'activity_type', 'activity_sort'],
    ];

    protected function checkEnddate($value)
    {
        $activity_startdate = strtotime(input('post.activity_startdate'));
        if ($activity_startdate >= $value){
            return '结束时间早于开始时间或相同时间';
        }
        return true;
    }
}