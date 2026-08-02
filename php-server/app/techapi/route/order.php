<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('order', function () {

    // 师傅订单配送
    // 注意路由顺序：deliveries/pages (2段) 和 deliveries/:order_id/xxx (3段) 必须在 deliveries/:order_id (2段) 前面
    Route::get('deliveries/pages', 'order.OrderDelivery/getOrderDeliveryPages');
    // 师傅抢单(未选择师傅的订单) order_delivery 表 technician_id 为0的订单
    Route::post('deliveries/:order_id/grab', 'order.OrderDelivery/grabOrderDelivery');
    // 师傅接单
    Route::post('deliveries/:order_id/accept', 'order.OrderDelivery/acceptOrderDelivery');
    // 师傅出发
    Route::post('deliveries/:order_id/departure', 'order.OrderDelivery/departureOrderDelivery');
    // 师傅确认到达
    Route::post('deliveries/:order_id/arrived', 'order.OrderDelivery/arrivedOrderDelivery');
    // 师傅开始服务
    Route::post('deliveries/:order_id/start-service', 'order.OrderDelivery/startServiceOrderDelivery');
    // 师傅确认完成
    Route::post('deliveries/:order_id/complete', 'order.OrderDelivery/completeOrderDelivery');
    // 师傅取消接单
    Route::post('deliveries/:order_id/cancel', 'order.OrderDelivery/cancelOrderDelivery');
    // 师傅订单详情（必须在3段路由之后）
    Route::get('deliveries/:order_id', 'order.OrderDelivery/getOrderDeliveryInfo');

    
})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);
