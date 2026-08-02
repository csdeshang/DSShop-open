<?php

namespace app\api\controller\distributor\validate;

use app\deshang\base\BaseValidate;

use app\common\enum\distributor\DistributorBalanceEnum;

class DistributorBalanceValidate extends BaseValidate
{
    protected $rule = [
        'change_type' => 'checkChangeType',
        'change_amount' => 'require|float|gt:0|max:999999.99',
    ];

    protected $message = [
        'change_type.checkChangeType' => '变更类型不正确',
        
        'change_amount.require' => '转换金额不能为空',
        'change_amount.float' => '转换金额必须为数字',
        'change_amount.gt' => '转换金额必须大于0',
        'change_amount.max' => '转换金额不能超过999999.99',
    ];

    protected $scene = [
        'pages' => ['change_type'],
        'toUser' => ['change_amount'],
    ];

    public function checkChangeType($value, $rule, $data)
    {
        return array_key_exists($value, DistributorBalanceEnum::getChangeTypeDict());
    }


} 