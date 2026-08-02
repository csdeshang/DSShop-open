<?php



use think\facade\Route;
use app\api\middleware\UserAuthorizeLog;
use app\api\middleware\UserAuthorizeToken;



// 视频教育
Route::group('kms', function () {

    


    //商品详情
    Route::get('goods/detail', 'app\platform\kms\app\api\controller\goods\KmsGoods@getKmsGoodsDetail');

    //商品分类

    //购物车
    // 注意路由顺序：carts/batch (2段) 必须在 carts/:id (2段) 前面
    Route::get('carts', 'app\platform\kms\app\api\controller\cart\KmsCart@getKmsCartList');
    Route::post('carts', 'app\platform\kms\app\api\controller\cart\KmsCart@addKmsCart');
    Route::delete('carts/batch', 'app\platform\kms\app\api\controller\cart\KmsCart@deleteKmsCart');
    Route::put('carts/:id', 'app\platform\kms\app\api\controller\cart\KmsCart@updateKmsCart');


    //下单流程
    Route::post('order-checkout/check', 'app\platform\kms\app\api\controller\order\KmsOrderCheckout@checkKmsOrderCheckout'); // 获取下单信息
    Route::post('order-checkout/generate', 'app\platform\kms\app\api\controller\order\KmsOrderCheckout@generateKmsOrder'); // 生成订单


    //KMS课程课时
    Route::get('lessons', 'app\platform\kms\app\api\controller\kms\KmsLesson@getKmsLessonList');
    Route::get('lessons/:id', 'app\platform\kms\app\api\controller\kms\KmsLesson@getKmsLessonDetail');



})->middleware([
    UserAuthorizeLog::class,
    UserAuthorizeToken::class,
]);
