<?php

namespace app\adminapi\controller\tblOrder\validate;

use app\common\enum\order\TblOrderEnum;
use app\deshang\base\BaseValidate;

class TblOrderDeliveryValidate extends BaseValidate
{
    protected $rule = [
        'platform' => 'require|checkPlatform',
        'delivery_method' => 'require|checkDeliveryMethod',
        'order_id' => 'require|integer|gt:0',
        'express_type' => 'max:50',
        'express_company' => 'max:100',
        'express_number' => 'max:100',
        'rider_id' => 'integer|egt:0',
        'technician_id' => 'integer|egt:0',
    ];

    protected $message = [
        'platform.require' => '平台标识不能为空',
        'platform.checkPlatform' => '平台标识值不正确',
        'delivery_method.require' => '配送方式不能为空',
        'delivery_method.checkDeliveryMethod' => '配送方式值不正确',
        'order_id.require' => '订单ID不能为空',
        'order_id.integer' => '订单ID必须是整数',
        'order_id.gt' => '订单ID必须大于0',
        'express_type.max' => '快递类型不能超过50个字符',
        'express_company.max' => '快递公司不能超过100个字符',
        'express_number.max' => '快递单号不能超过100个字符',
        'rider_id.integer' => '骑手ID必须是整数',
        'rider_id.egt' => '骑手ID必须大于等于0',
        'technician_id.integer' => '师傅ID必须是整数',
        'technician_id.egt' => '师傅ID必须大于等于0',
    ];

    protected $scene = [
        'dict' => ['platform'],
        'add' => ['delivery_method', 'order_id', 'express_type', 'express_company', 'express_number', 'rider_id', 'technician_id'],
        'pages' => ['rider_id', 'technician_id'],
    ];

    public function checkDeliveryMethod($value, $rule, $data): bool
    {
        if ($value === '' || $value === null) {
            return false;
        }
        return array_key_exists($value, TblOrderEnum::getAllOrderDeliveryDict());
    }
}
