<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

use app\common\enum\user\UserWithdrawalEnum;


class UserWithdrawalAccountValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'account_type' => 'require|max:10|in:'.UserWithdrawalEnum::ACCOUNT_TYPE_ALIPAY.','.UserWithdrawalEnum::ACCOUNT_TYPE_WECHAT.','.UserWithdrawalEnum::ACCOUNT_TYPE_BANK,
        'account_name' => 'require|max:20',
        'account_number' => 'require|max:32',
        'account_holder' => 'require|max:10',
        'is_default' => 'in:0,1',
    ];

    protected $message = [
        'id.require' => '账户ID不能为空',
        'id.integer' => '账户ID必须为整数',
        'id.gt' => '账户ID必须大于0',
        'account_type.require' => '账户类型不能为空',
        'account_type.max' => '账户类型不能超过10个字符',
        'account_type.in' => '账户类型必须是银行卡、支付宝或微信支付',
        'account_name.require' => '账户名称不能为空',
        'account_name.max' => '账户名称不能超过20个字符',
        'account_number.require' => '账户号码不能为空',
        'account_number.max' => '账户号码不能超过32个字符',
        'account_holder.require' => '账户持有人不能为空',
        'account_holder.max' => '账户持有人不能超过10个字符',
        'is_default.in' => '是否默认账户必须是0或1',
    ];

    protected $scene = [
        
        'create' => ['account_type', 'account_name', 'account_number', 'account_holder', 'is_default'],
        'update' => ['id', 'account_type', 'account_name', 'account_number', 'account_holder', 'is_default'],
    ];
}