<?php

namespace app\api\controller\wechat\validate;

use think\Validate;

class WechatSubscribeValidate extends Validate
{
    protected $rule = [
        'keys' => 'require|max:255',
        'type' => 'require|in:mini,official',
        'redirect_url' => 'require|url',
        'scene' => 'integer|between:0,10000',
    ];

    protected $message = [
        'keys.require' => '模板key不能为空',
        'keys.max' => '模板key长度不能超过255字符',
        'type.require' => '订阅类型不能为空',
        'type.in' => '订阅类型只能是mini或official',
        'redirect_url.require' => '重定向URL不能为空',
        'redirect_url.url' => '重定向URL格式不正确',
        'scene.integer' => '场景值必须是整数',
        'scene.between' => '场景值必须在0-10000之间',
    ];

    protected $scene = [
        'getTemplateId' => ['keys', 'type'],
        'saveRecord' => ['keys', 'type'], // 简化了参数
        'getOfficialUrl' => ['keys', 'redirect_url', 'scene'],
    ];
} 