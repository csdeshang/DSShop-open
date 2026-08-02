<?php

namespace app\platform\house\app\api\controller\order\validate;

use app\deshang\base\BaseValidate;

/**
 * 上门服务订单结算验证器
 */
class HouseOrderCheckoutValidate extends BaseValidate
{
    protected $rule = [
        'cart_ids' => 'require|array',
        'address_id' => 'integer|egt:0',
        'coupon_id' => 'integer|egt:0',
        'user_remark' => 'max:255',
        'referrer_id' => 'integer|egt:0',
        'appointment_time' => 'require|integer|gt:0',
        'technician_id' => 'integer|egt:0',
    ];

    protected $message = [
        'cart_ids.require' => '购物车ID不能为空',
        'cart_ids.array' => '购物车ID必须是数组',
        'address_id.integer' => '收货地址ID必须是整数',
        'address_id.egt' => '收货地址ID必须大于等于0',
        'coupon_id.integer' => '优惠券ID必须是整数',
        'coupon_id.egt' => '优惠券ID必须大于等于0',
        'user_remark.max' => '用户备注最多255个字符',
        'referrer_id.integer' => '推荐人ID必须是整数',
        'referrer_id.egt' => '推荐人ID必须大于等于0',
        'appointment_time.require' => '预约时间不能为空',
        'appointment_time.integer' => '预约时间必须是时间戳',
        'appointment_time.gt' => '预约时间必须大于0',
        'technician_id.integer' => '师傅ID必须是整数',
        'technician_id.egt' => '师傅ID必须大于等于0',
    ];

    protected $scene = [
        'check' => ['cart_ids', 'address_id', 'coupon_id', 'appointment_time', 'technician_id'],
        'generate' => ['cart_ids', 'address_id', 'coupon_id', 'user_remark', 'referrer_id', 'appointment_time', 'technician_id'],
    ];
}
