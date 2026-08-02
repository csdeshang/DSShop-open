<?php

namespace app\adminapi\controller\tblOrder\validate;

use app\common\enum\order\TblOrderEnum;
use app\deshang\base\BaseValidate;

class TblOrderValidate extends BaseValidate
{
    protected $rule = [
        'platform' => 'max:50|checkPlatform',
        'order_status' => 'checkOrderStatus',
        'order_id' => 'require|integer|gt:0',
        'goods_list' => 'require|array',
        'shipping_amount' => 'float|egt:0',
        'order_merge_id' => 'integer|gt:0',
    ];

    protected $message = [
        'platform.max' => '平台名称不能超过50个字符',
        'platform.checkPlatform' => '平台名称无效,请确认是否安装',
        'order_status.checkOrderStatus' => '订单状态值不正确',
        'order_id.require' => '订单ID不能为空',
        'order_id.integer' => '订单ID必须是整数',
        'order_id.gt' => '订单ID必须大于0',
        'goods_list.require' => '商品列表不能为空',
        'goods_list.array' => '商品列表必须是数组',
        'shipping_amount.float' => '运费必须是数字',
        'shipping_amount.egt' => '运费不能小于0',
        'order_merge_id.integer' => '合并支付订单ID必须是整数',
        'order_merge_id.gt' => '合并支付订单ID必须大于0',
    ];

    protected $scene = [
        'pages' => ['platform', 'order_status'],
        'statusDict' => ['platform'],
        'info' => ['order_id'],
        'modifyAmount' => ['order_id', 'goods_list', 'shipping_amount'],
        'goodsList' => ['order_id'],
        'logList' => ['order_id'],
        'payLogList' => ['order_id', 'order_merge_id'],
    ];

    public function checkOrderStatus($value, $rule, $data)
    {
        if ($value === '' || $value === null) {
            return true;
        }
        return array_key_exists($value, TblOrderEnum::getAllOrderStatusDict());
    }
}
