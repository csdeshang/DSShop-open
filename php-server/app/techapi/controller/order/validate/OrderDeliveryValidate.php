<?php

namespace app\techapi\controller\order\validate;

use app\deshang\base\BaseValidate;

class OrderDeliveryValidate extends BaseValidate
{
    protected $rule = [
        'order_id' => 'require|integer|gt:0',
        'longitude' => 'require|checkLongitude',
        'latitude' => 'require|checkLatitude',
        'tab_selected' => 'max:20',
        'goods_id' => 'require|integer|gt:0',
    ];

    protected $message = [
        'order_id.require' => '订单ID不能为空',
        'order_id.integer' => '订单ID必须是整数',
        'order_id.gt' => '订单ID必须大于0',
        'longitude.require' => '经度不能为空',
        'longitude.checkLongitude' => '经度格式不正确',
        'latitude.require' => '纬度不能为空',
        'latitude.checkLatitude' => '纬度格式不正确',
        'tab_selected.max' => '标签页标识不能超过20个字符',
        'goods_id.require' => '商品ID不能为空',
        'goods_id.integer' => '商品ID必须是整数',
        'goods_id.gt' => '商品ID必须大于0',
    ];

    protected $scene = [
        'pages' => ['tab_selected'],
        'info' => ['order_id'],
        'grab' => ['order_id', 'longitude', 'latitude'],
        'accept' => ['order_id', 'longitude', 'latitude'],
        'departure' => ['order_id', 'longitude', 'latitude'],
        'arrived' => ['order_id', 'longitude', 'latitude'],
        'startService' => ['order_id', 'longitude', 'latitude'],
        'complete' => ['order_id', 'longitude', 'latitude'],
        'cancel' => ['order_id', 'longitude', 'latitude'],
        'updateGoodsRel' => ['goods_id'],
    ];
} 