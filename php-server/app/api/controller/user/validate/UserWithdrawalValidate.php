<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserWithdrawalValidate extends BaseValidate
{
    protected $rule = [
        'amount' => 'require|float|gt:0|max:999999.99',
        'account_id' => 'require|integer|gt:0',
        'id' => 'require|integer|gt:0',
    ];

    protected $message = [
        'amount.require' => '提现金额不能为空',
        'amount.float' => '提现金额必须为数字',
        'amount.gt' => '提现金额必须大于0',
        'amount.max' => '提现金额不能超过999999.99',
        'account_id.require' => '提现账户ID不能为空',
        'account_id.integer' => '提现账户ID必须为整数',
        'account_id.gt' => '提现账户ID必须大于0',
        'id.require' => 'ID不能为空',
        'id.integer' => 'ID必须为整数',
        'id.gt' => 'ID必须大于0',
    ];

    protected $scene = [
        'apply' => ['amount', 'account_id'],
        'info' => ['id'],
        'accountInfo' => ['id'],
        'deleteAccount' => ['id'],
    ];
} 