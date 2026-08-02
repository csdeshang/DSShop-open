<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;



Route::group('technician', function () {

    // 设置师傅状态 (需要放到最前面，否则info 会影响此路由执行)
    Route::patch('info/technician-status', 'technician.TechnicianInfo/setTechnicianStatus');

    // 获取师傅信息 
    Route::get('info', 'technician.TechnicianInfo/getTechnicianInfo');
    // 更新师傅信息
    Route::put('info', 'technician.TechnicianInfo/updateTechnicianInfo');
    





    // 获取师傅余额流水
    Route::get('balance-logs/pages', 'technician.TechnicianBalance/getTechnicianBalanceLogPages');
    // 师傅余额转至用户余额
    Route::post('balance/to-user', 'technician.TechnicianBalance/technicianBalanceToUser');

    // 师傅商品关联管理
    // 注意路由顺序：goods-rels/pages (2段) 必须在 goods-rels/:goods_id (2段) 前面
    Route::get('goods-rels/pages', 'technician.TechnicianGoodsRel/getTechnicianGoodsRelPages');
    // 师傅商品关联更新
    Route::put('goods-rels/:goods_id', 'technician.TechnicianGoodsRel/updateTechnicianGoodsRel');

    // 师傅轨迹管理
    // 注意路由顺序：tracks/list (2段) 和 tracks/pages (2段) 必须在 tracks/:id (2段) 前面（如果有的话）
    Route::get('tracks/list', 'technician.TechnicianTrack/getTechnicianTrackList');
    Route::get('tracks/pages', 'technician.TechnicianTrack/getTechnicianTrackPages');
    // 师傅轨迹上报
    Route::post('tracks', 'technician.TechnicianTrack/reportTechnicianTrack');

    // 师傅评论管理
    // 注意路由顺序：comments/pages (2段) 和 comments/:id/reply (3段) 必须在 comments/:id (2段) 前面
    Route::get('comments/pages', 'technician.TechnicianComment/getTechnicianCommentPages');
    // 回复师傅评论
    Route::post('comments/:id/reply', 'technician.TechnicianComment/replyTechnicianComment');
    // 师傅评论详情（必须在3段路由之后）
    Route::get('comments/:id', 'technician.TechnicianComment/getTechnicianCommentInfo');

})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);