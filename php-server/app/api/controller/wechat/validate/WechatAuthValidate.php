<?php

namespace app\api\controller\wechat\validate;

use app\deshang\base\BaseValidate;

class WechatAuthValidate extends BaseValidate
{
    protected $rule = [
        'scene' => 'require|in:wechat_official,wechat_mini,wechat_app,wechat_web',
        'mode' => 'require|in:getOpenid,login,bind',
        'redirect_url' => 'require|url',
        'code' => 'string',
        'url' => 'require|url',
    ];

    protected $message = [
        'scene.require' => '场景不能为空',
        'scene.in' => '场景必须是wechat_official,wechat_mini,wechat_app或wechat_web',
        'mode.require' => '授权模式不能为空',
        'mode.in' => '授权模式必须是getOpenid,login或bind',
        'redirect_url.require' => '重定向URL不能为空',
        'redirect_url.url' => '重定向URL格式不正确',
        'code.string' => 'code必须是字符串',
        'url.require' => '当前页面URL不能为空',
        'url.url' => '当前页面URL格式不正确',
    ];

    protected $scene = [
        'getWechatAuthInfo' => ['code', 'scene', 'mode'],
        'checkWechatAuth' => ['scene'],
        'getOfficialAuthUrl' => ['mode', 'redirect_url'],
        'getWebAuthUrl' => ['mode', 'redirect_url'],
        'getJsSdkConfig' => ['url'],
    ];
}
