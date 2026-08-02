<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserBloggerFollowValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
    ];

    protected $message = [
        'id.require' => '博主ID不能为空',
        'id.integer' => '博主ID必须为整数',
        'id.gt' => '博主ID必须大于0',
    ];

    protected $scene = [
        'toggle' => ['id'],
    ];
} 