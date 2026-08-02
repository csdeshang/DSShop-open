<?php

namespace app\techapi\controller\technician\validate;

use app\deshang\base\BaseValidate;

class TechnicianTrackValidate extends BaseValidate
{
    protected $rule = [
        'longitude' => 'require|checkLongitude',
        'latitude' => 'require|checkLatitude',
        'address' => 'max:255',
        'start_time' => 'dateFormat:Y-m-d H:i:s',
        'end_time' => 'dateFormat:Y-m-d H:i:s',
    ];

    protected $message = [
        'longitude.require' => '经度不能为空',
        'longitude.checkLongitude' => '经度必须在-180到180度范围内',
        'latitude.require' => '纬度不能为空',
        'latitude.checkLatitude' => '纬度必须在-90到90度范围内',
        'address.max' => '地址不能超过255个字符',
        'start_time.dateFormat' => '开始时间格式不正确，请使用Y-m-d H:i:s格式',
        'end_time.dateFormat' => '结束时间格式不正确，请使用Y-m-d H:i:s格式',
    ];

    protected $scene = [
        'report' => ['longitude', 'latitude', 'address'],
        'list' => ['start_time', 'end_time'],
        'pages' => ['start_time', 'end_time'],
    ];
}
