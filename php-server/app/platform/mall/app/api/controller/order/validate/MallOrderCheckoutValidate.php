<?php

namespace app\platform\mall\app\api\controller\order\validate;

use app\deshang\base\BaseValidate;

/**
 * 商城订单结算验证器
 */
class MallOrderCheckoutValidate extends BaseValidate
{
    /**
     * 验证规则
     * @var array
     */
    protected $rule = [
        'cart_ids' => 'require|array',
        'address_id' => 'integer|egt:0',
        'coupon_id' => 'integer|egt:0',
        'user_remark' => 'max:255',
        'referrer_id' => 'integer|egt:0',
    ];

    /**
     * 验证消息
     * @var array
     */
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
    ];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
        'check' => ['cart_ids', 'address_id', 'coupon_id'],
        'generate' => ['cart_ids', 'address_id', 'coupon_id', 'user_remark', 'referrer_id'],
    ];
}
