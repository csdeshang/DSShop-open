<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserApplyRiderValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require|max:32',
        'mobile' => 'require|mobile',
        'apply_remark' => 'max:255',
    ];

    protected $message = [
        'name.require' => '真实姓名不能为空',
        'name.max' => '真实姓名不能超过32位',
        'mobile.require' => '手机号码不能为空',
        'mobile.mobile' => '手机号码格式不正确',
        'apply_remark.max' => '申请备注不能超过255位',
    ];

    protected $scene = [
        'apply' => ['name', 'mobile', 'apply_remark'],
    ];
} 