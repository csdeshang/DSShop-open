<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserBindMobileValidate extends BaseValidate
{
    protected $rule = [
        'mobile' => 'require|mobile',
        'code' => 'require|checkMobileCode',
    ];

    protected $message = [
        'mobile.require' => '手机号不能为空',
        'mobile.mobile' => '手机号格式不正确',
        'code.require' => '验证码不能为空',
        'code.checkMobileCode' => '验证码不正确',
    ];

    protected $scene = [
        'bindMobile' => ['mobile', 'code'],
        'unbindMobile' => ['code'],
    ];
}