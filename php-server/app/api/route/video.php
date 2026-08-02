<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('video', function () {


    // 短视频分页列表
    Route::get('shorts/pages', 'video.VideoShort/getVideoShortPages');
    // 短视频关联商品列表
    // shorts/:id/goods-list (3段) 必须在 shorts/:id (2段) 前面
    Route::get('shorts/:id/goods-list', 'video.VideoShort/getVideoShortGoodsList');
    // 短视频详情
    Route::get('shorts/:id', 'video.VideoShort/getVideoShortDetail');

    
    // 评论分页(短视频/短剧)
    Route::get('comments/pages', 'video.VideoComment/getVideoCommentPages');

    // 视频分类
    Route::get('categories/tree', 'video.VideoCategory/getVideoCategoryTree');
    Route::get('categories/list', 'video.VideoCategory/getVideoCategoryList');





})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);