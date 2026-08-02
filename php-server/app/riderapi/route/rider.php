<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('rider', function () {

    // 设置骑手状态 (需要放到最前面，否则info 会影响此路由执行)
    Route::patch('info/status', 'rider.RiderInfo/setRiderStatus');
    // 获取骑手信息
    Route::get('info', 'rider.RiderInfo/getRiderInfo');
    // 更新骑手信息
    Route::put('info', 'rider.RiderInfo/updateRiderInfo');

    

    // 获取骑手余额流水
    Route::get('balance-logs/pages', 'rider.RiderBalance/getRiderBalanceLogPages');

    // 骑手余额转至用户余额
    Route::post('balance/to-user', 'rider.RiderBalance/riderBalanceToUser');

    // 骑手评论管理
    // comments/pages (2段) 必须在 comments/:id (2段) 前面 否则 GET /comments/pages 会被 comments/:id 匹配
    Route::get('comments/pages', 'rider.RiderComment/getRiderCommentPages');
    // comments/:id/reply (3段) 必须在 comments/:id (2段) 前面
    Route::post('comments/:id/reply', 'rider.RiderComment/replyRiderComment');
    Route::get('comments/:id', 'rider.RiderComment/getRiderCommentInfo');

    // 骑手轨迹管理
    // tracks/list (2段) 必须在 tracks/:id (2段) 前面 否则 GET /tracks/list 会被 tracks/:id 匹配
    Route::get('tracks/list', 'rider.RiderTrack/getRiderTrackList');
    // tracks/pages (2段) 必须在 tracks/:id (2段) 前面 否则 GET /tracks/pages 会被 tracks/:id 匹配
    Route::get('tracks/pages', 'rider.RiderTrack/getRiderTrackPages');
    // 骑手轨迹上报（创建轨迹记录）
    Route::post('tracks', 'rider.RiderTrack/reportRiderTrack');




})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);