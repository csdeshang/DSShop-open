<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;



Route::any('trade/notify/:trade_channel/:trade_scene/:trade_type', 'trade.TradeNotify/notify');


Route::group('trade', function () {

    // 获取商户可用支付列表
    Route::get('payment/list', 'trade.TradePayment/getPaymentList');
    

    // 付款(充值、订单支付)
    Route::post('pay', 'trade.TradePay/pay');
    
    // 查询支付状态
    Route::get('pay/status', 'trade.TradePay/getPayStatus');




})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);