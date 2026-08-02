<?php

use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;

Route::group('points-goods', function () {

    // 积分商品管理
    // goods/list (2段) 必须在 goods/:id (2段) 前面
    Route::get('goods/list', 'pointsGoods.PointsGoods/getPointsGoodsList');
    Route::get('goods/pages', 'pointsGoods.PointsGoods/getPointsGoodsPages');
    Route::get('goods/:id', 'pointsGoods.PointsGoods/getPointsGoodsDetail');

    // 积分商品评价管理
    Route::get('evaluates/list', 'pointsGoods.PointsGoodsEvaluate/getPointsGoodsEvaluateList');
    Route::get('evaluates/pages', 'pointsGoods.PointsGoodsEvaluate/getPointsGoodsEvaluatePages');

})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class
]);