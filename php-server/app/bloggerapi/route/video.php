<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


// 博主 对短视频管理
Route::group('video', function () {

    // 短视频管理
    // 注意路由顺序：shorts/pages (2段) 必须在 shorts/:id (2段) 前面
    Route::get('shorts/pages', 'video.VideoShort/getVideoShortPages');
    Route::post('shorts', 'video.VideoShort/publishVideoShort');
    Route::delete('shorts/:id', 'video.VideoShort/deleteVideoShort');

    // 切换评论字段状态
    // comments/:id/toggle-field (3段) 必须在 comments/:id (2段) 前面（如果将来添加详情路由）
    Route::patch('comments/:id/toggle-field', 'video.VideoComment/toggleVideoCommentField');

})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);
