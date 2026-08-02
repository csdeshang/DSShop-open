<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;



// 微信消息接收路由（无需认证）
Route::any('wechat/serve/official', 'wechat.WechatServe/official');
Route::any('wechat/serve/mini', 'wechat.WechatServe/mini');



Route::group('wechat', function () {

    // 微信授权
    Route::post('auth', 'wechat.WechatAuth/getWechatAuthInfo');

    // 检测微信授权
    Route::get('check-auth', 'wechat.WechatAuth/checkWechatAuth');

    // 获取公众号网页授权URL（仅用于wechat_official场景）
    Route::post('official-auth-url', 'wechat.WechatAuth/getOfficialAuthUrl');

    // 获取网站应用PC扫码登录URL（仅用于wechat_web场景）
    Route::post('web-auth-url', 'wechat.WechatAuth/getWebAuthUrl');

    // 获取JS SDK配置
    Route::post('js-sdk-config', 'wechat.WechatAuth/getJsSdkConfig');

    // 微信订阅消息
    // 获取订阅模板ID
    Route::get('subscribe/template-id', 'wechat.WechatSubscribe/getWechatSubscribeTemplateId');

    // 保存订阅记录
    Route::post('subscribe/record', 'wechat.WechatSubscribe/saveWechatSubscribeRecord');

    // 获取公众号订阅URL
    Route::post('subscribe/official-url', 'wechat.WechatSubscribe/getOfficialSubscribeUrl');

})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);

