<?php
namespace app\api\controller\tblOrder\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\order\TblOrderEnum;

class TblOrderValidate extends BaseValidate
{

    protected $rule = [
        'order_id' => 'require|integer|gt:0',
        'order_sn' => 'max:50',
        'tab_selected' => 'in:pending,accepted,comment,refund',
        'id' => 'require|integer|gt:0',
    ];

    protected $message = [
        'order_id.require' => '订单ID不能为空',
        'order_id.integer' => '订单ID必须为整数',
        'order_id.gt' => '订单ID必须大于0',
        'order_sn.max' => '订单编号长度不能超过50个字符',
        'tab_selected.in' => '订单状态选项卡值不正确',
        'id.require' => 'ID不能为空',
        'id.integer' => 'ID必须为整数',
        'id.gt' => 'ID必须大于0',
    ];

    protected $scene = [
        'pages' => ['order_sn', 'tab_selected'],
        'info' => ['id'],
        'cancel' => ['order_id'],
        'close' => ['order_id'],
        'confirm' => ['order_id'],
    ];

    public function checkOrderStatus($value, $rule, $data)
    {
        if (empty($value)) {
            return true; // 空值允许
        }
        return array_key_exists($value, TblOrderEnum::getAllOrderStatusDict());
    }

}