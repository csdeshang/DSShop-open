<?php

namespace app\api\controller\system\validate;

use app\deshang\base\BaseValidate;

class SysAgreementValidate extends BaseValidate
{
    protected $rule = [
        'code' => 'require|max:50',
    ];

    protected $message = [
        'code.require' => '协议代码不能为空',
        'code.max' => '协议代码长度不能超过50个字符',
    ];

    protected $scene = [
        'info' => ['code'],
    ];
} 