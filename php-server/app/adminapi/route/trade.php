<?php

use think\facade\Route;

use app\adminapi\middleware\AdminAuthorizeToken;
use app\adminapi\middleware\AdminAuthorizeRole;
use app\adminapi\middleware\AdminAuthorizeLog;


Route::group('trade', function () {

    // 平台支付配置列表（list 须在 :id 之前，避免被误匹配）
    Route::get('payment-configs/list', 'trade.TradePaymentConfig/getPaymentConfig');
    Route::get('payment-configs/:id', 'trade.TradePaymentConfig/getPaymentConfigInfoById');
    Route::post('payment-configs', 'trade.TradePaymentConfig/createPaymentConfig');
    Route::put('payment-configs/:id', 'trade.TradePaymentConfig/updatePaymentConfig');
    Route::delete('payment-configs/:id', 'trade.TradePaymentConfig/deletePaymentConfig');

    
    // 交易支付记录
    Route::get('pay-logs/pages', 'trade.TradePayLog/getTradePayLogPages');

    // 交易退款记录
    Route::get('refund-logs/pages', 'trade.TradeRefundLog/getTradeRefundLogPages');

    // 交易转账记录
    Route::get('transfer-logs/pages', 'trade.TradeTransferLog/getTradeTransferLogPages');








})->middleware([
    AdminAuthorizeToken::class,
    AdminAuthorizeRole::class,
    AdminAuthorizeLog::class
]);