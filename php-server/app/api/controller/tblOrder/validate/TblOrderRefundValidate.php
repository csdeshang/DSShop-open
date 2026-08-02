<?php

namespace app\api\controller\tblOrder\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\order\TblOrderRefundEnum;

class TblOrderRefundValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'order_id' => 'require|integer|gt:0',
        'order_goods_id' => 'integer',
        'apply_amount' => 'require|float|gt:0|max:999999.99',
        'refund_explain' => 'require|max:500',
        'refund_type' => 'require|checkRefundType',
        'refund_images' => 'max:2000',
        'express_company' => 'require|max:50',
        'express_number' => 'require|max:50',
    ];

    protected $message = [
        'id.require' => '退款申请ID不能为空',
        'id.integer' => '退款申请ID必须为整数',
        'id.gt' => '退款申请ID必须大于0',
        'order_id.require' => '订单ID不能为空',
        'order_id.integer' => '订单ID必须为整数',
        'order_id.gt' => '订单ID必须大于0',
        'order_goods_id.integer' => '订单商品ID必须为整数',
        'apply_amount.require' => '退款金额不能为空',
        'apply_amount.float' => '退款金额必须为数字',
        'apply_amount.gt' => '退款金额必须大于0',
        'apply_amount.max' => '退款金额不能超过999999.99',
        'refund_explain.require' => '退款说明不能为空',
        'refund_explain.max' => '退款说明长度不能超过500个字符',
        'refund_type.require' => '退款类型不能为空',
        'refund_type.checkRefundType' => '退款类型值无效',
        'refund_images.max' => '退款图片数据长度不能超过2000个字符',
        'express_company.require' => '快递公司不能为空',
        'express_company.max' => '快递公司名称长度不能超过50个字符',
        'express_number.require' => '快递单号不能为空',
        'express_number.max' => '快递单号长度不能超过50个字符',
    ];

    protected $scene = [
        'apply' => ['order_id', 'order_goods_id', 'apply_amount', 'refund_explain', 'refund_type', 'refund_images'],
        'edit' => ['id', 'apply_amount', 'refund_explain'],
        'cancel' => ['id'],
        'returnGoods' => ['id', 'express_company', 'express_number'],
        'info' => ['id'],
    ];

    public function checkRefundType($value, $rule, $data)
    {
        return array_key_exists($value, TblOrderRefundEnum::getAllRefundTypeDict());
    }
} 