<?php



use think\facade\Route;
use app\api\middleware\UserAuthorizeLog;
use app\api\middleware\UserAuthorizeToken;



// 外卖
Route::group('food', function () {

    


    //商品详情
    Route::get('goods/detail', 'app\platform\food\app\api\controller\goods\FoodGoods@getFoodGoodsDetail');

    //商品分类

    //购物车
    // 注意路由顺序：carts/batch (2段) 必须在 carts/:id (2段) 前面
    Route::get('carts', 'app\platform\food\app\api\controller\cart\FoodCart@getFoodCartList');
    Route::post('carts', 'app\platform\food\app\api\controller\cart\FoodCart@addFoodCart');
    Route::delete('carts/batch', 'app\platform\food\app\api\controller\cart\FoodCart@deleteFoodCart');
    Route::put('carts/:id', 'app\platform\food\app\api\controller\cart\FoodCart@updateFoodCart');


    //下单流程
    Route::post('order-checkout/check', 'app\platform\food\app\api\controller\order\FoodOrderCheckout@checkFoodOrderCheckout'); // 获取下单信息
    Route::post('order-checkout/generate', 'app\platform\food\app\api\controller\order\FoodOrderCheckout@generateFoodOrder'); // 生成订单





})->middleware([
    UserAuthorizeLog::class,
    UserAuthorizeToken::class,
]);
