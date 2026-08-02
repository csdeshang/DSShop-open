<?php

namespace app\riderapi\controller\rider\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\rider\RiderBalanceEnum;

class RiderBalanceValidate extends BaseValidate
{
    protected $rule = [
        'change_amount' => 'require|float|gt:0',
        'change_type' => 'checkChangeType',
        'change_mode' => 'checkChangeMode',
    ];

    protected $message = [
        'change_amount.require' => '转账金额不能为空',
        'change_amount.float' => '转账金额必须是数字',
        'change_amount.gt' => '转账金额必须大于0',
        'change_type.checkChangeType' => '变动类型值不正确',
        'change_mode.checkChangeMode' => '变动方式值不正确',
    ];

    protected $scene = [
        'toUser' => ['change_amount'],
        'pages' => ['change_type', 'change_mode'],
    ];
    
    // 验证变动类型
    public function checkChangeType($value, $rule, $data)
    {
        if (empty($value)) {
            return true; // 空值允许，用于查询全部
        }
        return array_key_exists($value, RiderBalanceEnum::getChangeTypeDict());
    }
    
    // 验证变动方式
    public function checkChangeMode($value, $rule, $data)
    {
        if (empty($value)) {
            return true; // 空值允许，用于查询全部
        }
        return array_key_exists($value, RiderBalanceEnum::getChangeModeDict());
    }
}
