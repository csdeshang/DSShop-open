<?php

namespace app\adminapi\controller\tblOrder\validate;

use app\common\enum\order\TblOrderRefundEnum;
use app\deshang\base\BaseValidate;

class TblOrderRefundValidate extends BaseValidate
{
    protected $rule = [
        'refund_status' => 'checkRefundStatus',
        'platform' => 'require|checkPlatform',
        'order_id' => 'require|integer|gt:0',
        'order_goods_id' => 'require|integer',
        'apply_amount' => 'require|float|gt:0',
        'refund_explain' => 'max:500',
        'refund_id' => 'require|integer|gt:0',
        'reject_reason' => 'require|max:500',
        'agree_reason' => 'max:255',
    ];

    protected $message = [
        'refund_status.checkRefundStatus' => '退款状态值不正确',
        'platform.require' => '平台标识不能为空',
        'platform.checkPlatform' => '平台标识值不正确',
        'order_id.require' => '订单ID不能为空',
        'order_id.integer' => '订单ID必须是整数',
        'order_id.gt' => '订单ID必须大于0',
        'order_goods_id.require' => '订单商品ID不能为空',
        'order_goods_id.integer' => '订单商品ID必须是整数',
        'apply_amount.require' => '申请退款金额不能为空',
        'apply_amount.float' => '申请退款金额必须是数字',
        'apply_amount.gt' => '申请退款金额必须大于0',
        'refund_explain.max' => '退款说明不能超过500个字符',
        'refund_id.require' => '退款ID不能为空',
        'refund_id.integer' => '退款ID必须是整数',
        'refund_id.gt' => '退款ID必须大于0',
        'reject_reason.require' => '拒绝原因不能为空',
        'reject_reason.max' => '拒绝原因不能超过500个字符',
        'agree_reason.max' => '同意备注不能超过255个字符',
    ];

    protected $scene = [
        'pages' => ['refund_status'],
        'list' => ['order_id'],
        'info' => ['refund_id'],
        'add' => ['order_id', 'order_goods_id', 'apply_amount', 'refund_explain'],
        'agree' => ['refund_id', 'agree_reason'],
        'reject' => ['refund_id', 'reject_reason'],
        'receiveGoods' => ['refund_id'],
        'statusDict' => ['platform'],
        'logList' => ['refund_id'],
        'retry' => ['refund_id'],
    ];

    public function checkRefundStatus($value, $rule, $data): bool
    {
        if ($value === '' || $value === null) {
            return true;
        }
        return array_key_exists($value, TblOrderRefundEnum::getRefundStatusDict());
    }
}
