<?php



use think\facade\Route;
use app\api\middleware\UserAuthorizeLog;
use app\api\middleware\UserAuthorizeToken;



// 上门服务
Route::group('house', function () {

    


    //商品详情
    Route::get('goods/detail', 'app\platform\house\app\api\controller\goods\HouseGoods@getHouseGoodsDetail');

    //商品分类

    //购物车
    // 注意路由顺序：carts/batch (2段) 必须在 carts/:id (2段) 前面
    Route::get('carts', 'app\platform\house\app\api\controller\cart\HouseCart@getHouseCartList');
    Route::post('carts', 'app\platform\house\app\api\controller\cart\HouseCart@addHouseCart');
    Route::delete('carts/batch', 'app\platform\house\app\api\controller\cart\HouseCart@deleteHouseCart');
    Route::put('carts/:id', 'app\platform\house\app\api\controller\cart\HouseCart@updateHouseCart');


    //下单流程
    Route::post('order-checkout/check', 'app\platform\house\app\api\controller\order\HouseOrderCheckout@checkHouseOrderCheckout'); // 获取下单信息
    Route::post('order-checkout/generate', 'app\platform\house\app\api\controller\order\HouseOrderCheckout@generateHouseOrder'); // 生成订单





})->middleware([
    UserAuthorizeLog::class,
    UserAuthorizeToken::class,
]);
