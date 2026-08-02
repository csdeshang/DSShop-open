<?php



use think\facade\Route;
use app\adminapi\middleware\AdminAuthorizeToken;
use app\adminapi\middleware\AdminAuthorizeRole;
use app\adminapi\middleware\AdminAuthorizeLog;




Route::group('food', function () {

    Route::get('goods/pages', 'app\platform\food\app\adminapi\controller\goods\FoodGoods@getFoodGoodsPages');
    Route::post('goods/batch-soft-delete', 'app\platform\food\app\adminapi\controller\goods\FoodGoods@softDeleteFoodGoods');
    Route::post('goods', 'app\platform\food\app\adminapi\controller\goods\FoodGoods@addFoodGoods');
    Route::get('goods/:id/edit', 'app\platform\food\app\adminapi\controller\goods\FoodGoods@getFoodGoodsUpdateInfo');
    Route::put('goods/:id', 'app\platform\food\app\adminapi\controller\goods\FoodGoods@updateFoodGoods');



})->middleware([
    AdminAuthorizeToken::class,
    AdminAuthorizeRole::class,
    AdminAuthorizeLog::class
]);


