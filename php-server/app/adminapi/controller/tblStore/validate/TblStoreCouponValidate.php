<?php

namespace app\adminapi\controller\tblStore\validate;

use app\common\enum\store\TblStoreCouponEnum;
use app\deshang\base\BaseValidate;

/**
 * 店铺优惠券验证器
 */
class TblStoreCouponValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'coupon_name' => 'require|max:100',
        'coupon_type' => 'require|checkCouponType',
        'discount_value' => 'require|float|gt:0',
        'min_spend' => 'float|egt:0',
        'start_time' => 'require|integer|gt:0',
        'end_time' => 'require|integer|gt:0',
        'total_stock' => 'require|integer|gt:0',
        'limit_per_user' => 'integer|egt:0',
        'claim_type' => 'require|checkClaimType',
        'points_required' => 'integer|egt:0',
        'sort' => 'integer|between:0,999',
        'coupon_id' => 'integer|gt:0',
    ];

    protected $message = [
        'id.require' => '优惠券ID不能为空',
        'id.integer' => '优惠券ID必须是整数',
        'id.gt' => '优惠券ID必须大于0',
        'coupon_name.require' => '优惠券名称不能为空',
        'coupon_name.max' => '优惠券名称不能超过100个字符',
        'coupon_type.require' => '优惠券类型不能为空',
        'coupon_type.checkCouponType' => '优惠券类型值不正确',
        'discount_value.require' => '折扣值不能为空',
        'discount_value.float' => '折扣值必须是数字',
        'discount_value.gt' => '折扣值必须大于0',
        'min_spend.float' => '最低消费必须是数字',
        'min_spend.egt' => '最低消费不能小于0',
        'start_time.require' => '开始时间不能为空',
        'start_time.integer' => '开始时间必须是时间戳',
        'start_time.gt' => '开始时间必须大于0',
        'end_time.require' => '结束时间不能为空',
        'end_time.integer' => '结束时间必须是时间戳',
        'end_time.gt' => '结束时间必须大于0',
        'total_stock.require' => '总库存不能为空',
        'total_stock.integer' => '总库存必须是整数',
        'total_stock.gt' => '总库存必须大于0',
        'limit_per_user.integer' => '每人限领必须是整数',
        'limit_per_user.egt' => '每人限领不能小于0',
        'claim_type.require' => '领取方式不能为空',
        'claim_type.integer' => '领取方式必须是整数',
        'claim_type.checkClaimType' => '领取方式值不正确',
        'points_required.integer' => '所需积分必须是整数',
        'points_required.egt' => '所需积分不能小于0',
        'sort.integer' => '排序必须是整数',
        'sort.between' => '排序值必须在0-999之间',
        'coupon_id.integer' => '优惠券ID必须是整数',
        'coupon_id.gt' => '优惠券ID必须大于0',
    ];

    protected $scene = [
        'info' => ['id'],
        'create' => ['coupon_name', 'coupon_type', 'discount_value', 'min_spend', 'start_time', 'end_time', 'total_stock', 'limit_per_user', 'claim_type', 'points_required', 'sort'],
        'update' => ['id', 'coupon_name', 'coupon_type', 'discount_value', 'min_spend', 'start_time', 'end_time', 'total_stock', 'limit_per_user', 'claim_type', 'points_required', 'sort'],
        'userPages' => ['coupon_id'],
    ];

    public function checkCouponType($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }

        return array_key_exists($value, TblStoreCouponEnum::getStoreCouponTypeDict());
    }

    public function checkClaimType($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }

        return array_key_exists($value, TblStoreCouponEnum::getStoreCouponClaimTypeDict());
    }
}
