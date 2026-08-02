<?php

namespace app\api\controller\system\validate;

use app\deshang\base\BaseValidate;

class SysAreaValidate extends BaseValidate
{
    protected $rule = [
        'pid' => 'integer|egt:0',
    ];

    protected $message = [
        'pid.integer' => '父级地区ID必须为整数',
        'pid.egt' => '父级地区ID不能小于0',
    ];

    protected $scene = [
        'list' => ['pid'],
    ];
} 