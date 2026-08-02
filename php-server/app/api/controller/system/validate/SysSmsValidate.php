<?php

namespace app\api\controller\system\validate;

use app\deshang\base\BaseValidate;


class SysSmsValidate extends BaseValidate
{

    protected $rule = [
        'key'          => 'require',
        'mobile'   => 'require|mobile',
    ];

    protected $message = [
        'key.require'          => 'key不能为空',
        'mobile.require'   => 'mobile不能为空',
        'mobile.mobile'   => 'mobile格式不正确',
    ];

    protected $scene = [
        'sendSms' => ['key','mobile'],
    ];
}
