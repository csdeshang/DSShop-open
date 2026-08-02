<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('blogger', function () {

    // 获取博主信息
    Route::get('info', 'blogger.BloggerInfo/getBloggerInfo');
    
    // 更新博主信息
    Route::put('info', 'blogger.BloggerInfo/updateBloggerInfo');






})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);