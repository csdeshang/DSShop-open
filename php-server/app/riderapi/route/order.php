<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('order', function () {

    // 骑手订单配送
    // deliveries/working (2段) 必须在 deliveries/:order_id (2段) 前面 否则 GET /deliveries/working 会被 deliveries/:order_id 匹配
    Route::get('deliveries/working', 'order.OrderDelivery/getWorkingOrderDeliveryList');
    // deliveries/pages (2段) 必须在 deliveries/:order_id (2段) 前面 否则 GET /deliveries/pages 会被 deliveries/:order_id 匹配
    Route::get('deliveries/pages', 'order.OrderDelivery/getOrderDeliveryPages');
    
    // 骑手接单
    // deliveries/:order_id/grab (3段) 必须在 deliveries/:order_id (2段) 前面
    Route::post('deliveries/:order_id/grab', 'order.OrderDelivery/grabOrderDelivery');
    // 骑手取消接单
    Route::post('deliveries/:order_id/cancel', 'order.OrderDelivery/cancelOrderDelivery');
    // 骑手到达
    Route::post('deliveries/:order_id/arrived', 'order.OrderDelivery/arrivedOrderDelivery');
    // 骑手取货
    Route::post('deliveries/:order_id/pickup', 'order.OrderDelivery/pickupOrderDelivery');
    // 骑手确认送达
    Route::post('deliveries/:order_id/complete', 'order.OrderDelivery/completeOrderDelivery');
    
    // 骑手订单详情
    Route::get('deliveries/:order_id', 'order.OrderDelivery/getOrderDeliveryInfo');








})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);