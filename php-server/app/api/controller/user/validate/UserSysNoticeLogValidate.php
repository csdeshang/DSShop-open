<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserSysNoticeLogValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
    ];

    protected $message = [
        'id.require' => '消息通知ID不能为空',
        'id.integer' => '消息通知ID必须为整数',
        'id.gt' => '消息通知ID必须大于0',
    ];

    protected $scene = [
        'info' => ['id'],
    ];
} 