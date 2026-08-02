<?php



use think\facade\Route;
use app\adminapi\middleware\AdminAuthorizeToken;
use app\adminapi\middleware\AdminAuthorizeRole;
use app\adminapi\middleware\AdminAuthorizeLog;




Route::group('house', function () {

    Route::get('goods/pages', 'app\platform\house\app\adminapi\controller\goods\HouseGoods@getHouseGoodsPages');
    Route::post('goods/batch-soft-delete', 'app\platform\house\app\adminapi\controller\goods\HouseGoods@softDeleteHouseGoods');
    Route::post('goods', 'app\platform\house\app\adminapi\controller\goods\HouseGoods@addHouseGoods');
    Route::get('goods/:id/edit', 'app\platform\house\app\adminapi\controller\goods\HouseGoods@getHouseGoodsUpdateInfo');
    Route::put('goods/:id', 'app\platform\house\app\adminapi\controller\goods\HouseGoods@updateHouseGoods');



})->middleware([
    AdminAuthorizeToken::class,
    AdminAuthorizeRole::class,
    AdminAuthorizeLog::class
]);


