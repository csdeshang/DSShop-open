<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('editable', function () {
    // 获取可编辑页面信息
    Route::get('page/info', 'editable.EditablePage/getEditablePageInfo');

    



});