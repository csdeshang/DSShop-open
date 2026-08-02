<?php

namespace app\riderapi\controller\rider\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\rider\RiderEnum;

class RiderInfoValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require|max:20|chsDash',
        'mobile' => 'require|mobile',
        'status' => 'require|checkRiderStatus',
    ];

    protected $message = [
        'name.require' => '骑手名称不能为空',
        'name.max' => '骑手名称不能超过20个字符',
        'name.chsDash' => '骑手名称只能包含汉字、字母、数字、下划线和破折号',
        'mobile.require' => '联系电话不能为空',
        'mobile.mobile' => '联系电话格式不正确',
        'status.require' => '骑手状态不能为空',
        'status.checkRiderStatus' => '骑手状态值不正确',
    ];

    protected $scene = [
        'update' => ['name', 'mobile'],
        'setStatus' => ['status'],
    ];
    
    // 验证骑手状态是否在允许的范围内
    protected function checkRiderStatus($value, $rule, $data = [])
    {
        return array_key_exists($value, RiderEnum::getRiderStatusDict());
    }
}
