<?php

namespace app\riderapi\controller\order\validate;

use app\deshang\base\BaseValidate;

class OrderDeliveryValidate extends BaseValidate
{
    protected $rule = [
        'order_id' => 'require|integer|gt:0',
        'longitude' => 'checkLongitude',
        'latitude' => 'checkLatitude',
        'tab_selected' => 'max:20',
    ];

    protected $message = [
        'order_id.require' => '订单ID不能为空',
        'order_id.integer' => '订单ID必须是整数',
        'order_id.gt' => '订单ID必须大于0',
        'longitude.checkLongitude' => '经度格式不正确',
        'latitude.checkLatitude' => '纬度格式不正确',
        'tab_selected.max' => '标签页标识不能超过20个字符',
    ];

    protected $scene = [
        'grab' => ['order_id', 'longitude', 'latitude'],
        'cancel' => ['order_id', 'longitude', 'latitude'],
        'arrived' => ['order_id', 'longitude', 'latitude'],
        'pickup' => ['order_id', 'longitude', 'latitude'],
        'complete' => ['order_id', 'longitude', 'latitude'],
        'info' => ['order_id'],
        'workingList' => ['tab_selected', 'longitude', 'latitude'],
    ];
} 