<?php



use think\facade\Route;
use app\adminapi\middleware\AdminAuthorizeToken;
use app\adminapi\middleware\AdminAuthorizeRole;
use app\adminapi\middleware\AdminAuthorizeLog;




Route::group('mall', function () {

    Route::get('goods/pages', 'app\platform\mall\app\adminapi\controller\goods\MallGoods@getMallGoodsPages');
    Route::post('goods/batch-soft-delete', 'app\platform\mall\app\adminapi\controller\goods\MallGoods@softDeleteMallGoods');
    Route::post('goods', 'app\platform\mall\app\adminapi\controller\goods\MallGoods@addMallGoods');
    Route::get('goods/:id/edit', 'app\platform\mall\app\adminapi\controller\goods\MallGoods@getMallGoodsUpdateInfo');
    Route::put('goods/:id', 'app\platform\mall\app\adminapi\controller\goods\MallGoods@updateMallGoods');



})->middleware([
    AdminAuthorizeToken::class,
    AdminAuthorizeRole::class,
    AdminAuthorizeLog::class
]);


