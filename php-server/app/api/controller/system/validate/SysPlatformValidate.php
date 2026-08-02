<?php

namespace app\api\controller\system\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\system\SysPlatformEnum;

class SysPlatformValidate extends BaseValidate
{
    protected $rule = [
        'scene' => 'checkScene',
    ];

    protected $message = [
        'scene.checkScene' => '应用场景不正确',
    ];

    protected $scene = [
        'list' => ['scene'],
    ];

    public function checkScene($value, $rule, $data)
    {
        return array_key_exists($value, SysPlatformEnum::getSceneDict());
    }
} 