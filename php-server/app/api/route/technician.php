<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('technician', function () {

    // 师傅
    // 注意路由顺序：technicians/list (2段) 和 technicians/pages (2段) 必须在 technicians/:id (2段) 前面
    Route::get('technicians/list', 'technician.Technician/getTechnicianList');
    Route::get('technicians/pages', 'technician.Technician/getTechnicianPages');
    Route::get('technicians/:id', 'technician.Technician/getTechnicianDetail');

    // 师傅评价
    Route::get('comments/pages', 'technician.TechnicianComment/getTechnicianCommentPages');
    Route::get('comments/list', 'technician.TechnicianComment/getTechnicianCommentList');


})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);