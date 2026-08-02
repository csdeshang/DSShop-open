<?php



use think\facade\Route;
use app\api\middleware\UserAuthorizeLog;
use app\api\middleware\UserAuthorizeToken;



// 商城
Route::group('mall', function () {

    


    //商品详情
    Route::get('goods/detail', 'app\platform\mall\app\api\controller\goods\MallGoods@getMallGoodsDetail');

    //商品分类

    //购物车
    // 注意路由顺序：carts/batch (2段) 必须在 carts/:id (2段) 前面
    Route::get('carts', 'app\platform\mall\app\api\controller\cart\MallCart@getMallCartList');
    Route::post('carts', 'app\platform\mall\app\api\controller\cart\MallCart@addMallCart');
    Route::delete('carts/batch', 'app\platform\mall\app\api\controller\cart\MallCart@deleteMallCart');
    Route::put('carts/:id', 'app\platform\mall\app\api\controller\cart\MallCart@updateMallCart');


    //下单流程
    Route::post('order-checkout/check', 'app\platform\mall\app\api\controller\order\MallOrderCheckout@checkMallOrderCheckout'); // 获取下单信息
    Route::post('order-checkout/generate', 'app\platform\mall\app\api\controller\order\MallOrderCheckout@generateMallOrder'); // 生成订单





})->middleware([
    UserAuthorizeLog::class,
    UserAuthorizeToken::class,
]);
