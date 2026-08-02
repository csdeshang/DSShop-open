<?php


use think\facade\Route;
use app\deshang\core\PlatformLoader;

//Thtinkphp 路由中间件  https://doc.thinkphp.cn/v8_0/route_middleware.html





Route::group(function () {
    //枚举数据 
    Route::get('enums/data', 'enum.Enum/getEnumData');

    //二维码生成
    // 注意路由顺序：特殊路径（2段）必须在动态路径（2段）前面
    // 生成二维码并直接输出图片
    Route::get('qrcodes/image', 'Qrcode/image');
    // 生成二维码并返回图片数据
    Route::get('qrcodes', 'Qrcode/create');


    //普通登录(手机号、邮箱、用户名)
    Route::post('login/normal', 'login.Login/loginNormal');
    //手机验证码登录 (手机号+验证码)
    Route::post('login/mobile', 'login.Login/loginMobile');
    //普通注册 (用户名+密码)
    Route::post('register/normal', 'login.Register/registerNormal');
    //手机验证码注册 (手机号+验证码)
    Route::post('register/mobile', 'login.Register/registerMobile');
    // 重置密码 (手机号+验证码+新密码)
    Route::post('reset-password/mobile', 'login.ResetPassword/resetPasswordMobile');


    //刷新token
    Route::get('refresh-token', 'login.Login/refreshToken');
    //用户退出
    Route::get('logout', 'login.Login/logout');

    // SSO 单点登录（商户PC、店铺PC、用户PC 免二次登录）
    Route::post('sso/ticket', 'Sso/ticket');
    Route::post('sso/exchange', 'Sso/exchange');

    
    //验证码
    // 注意路由顺序：特殊路径（2段）必须在动态路径（2段）前面
    // 校验验证码
    Route::post('captcha/verify', 'login.Captcha/verify');
    // 生成验证码
    Route::post('captcha', 'login.Captcha/create');
});



PlatformLoader::loadRoute('api');