<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('blogger', function () {


    // 博主管理
    // 注意路由顺序：特殊路径（2段）必须在动态路径（2段）前面
    // 博主分页列表
    Route::get('bloggers/pages', 'blogger.Blogger/getBloggerPages');
    // 博主详情
    Route::get('bloggers/:id', 'blogger.Blogger/getBloggerDetail');





})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);