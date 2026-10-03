<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('tbl-order', function () {

    // 订单开票（顺序：pages 与多段路径须在 :id 两段式之前）
    Route::get('invoices/pages', 'tblOrder.TblOrderInvoice/getTblOrderInvoicePages');
    Route::post('invoices', 'tblOrder.TblOrderInvoice/applyTblOrderInvoice');
    Route::get('invoices/:id', 'tblOrder.TblOrderInvoice/getTblOrderInvoiceInfo');

    // 订单
    // 注意路由顺序：orders/pages (2段) 和 orders/:id/xxx (3段) 必须在 orders/:id (2段) 前面
    // 订单分页
    Route::get('orders/pages', 'tblOrder.tblOrder/getTblOrderPages');
    // 订单状态数量
    Route::get('orders/status/count', 'tblOrder.tblOrder/getTblOrderStatusCount');
    // 取消订单
    Route::patch('orders/:id/cancel', 'tblOrder.tblOrder/cancelTblOrder');
    // 关闭订单
    Route::patch('orders/:id/close', 'tblOrder.tblOrder/closeTblOrder');
    // 确认收货
    Route::patch('orders/:id/confirm', 'tblOrder.tblOrder/confirmTblOrder');
    // 获取订单配送员信息
    Route::get('orders/:id/rider', 'tblOrder.tblOrder/getTblOrderRiderInfo');
    // 获取订单师傅信息
    Route::get('orders/:id/technician', 'tblOrder.tblOrder/getTblOrderTechnicianInfo');
    // 订单详情
    Route::get('orders/:id', 'tblOrder.tblOrder/getTblOrderInfo');



    // 订单退款
    // 注意路由顺序：refunds/pages、refunds/:id/xxx 须在 refunds/:id 及 POST refunds 前面（非完全匹配时 POST refunds 会前缀匹配多段路径）
    // 订单退款分页
    Route::get('refunds/pages', 'tblOrder.tblOrderRefund/getTblOrderRefundPages');
    // 用户退货
    Route::post('refunds/:id/return-goods', 'tblOrder.tblOrderRefund/userReturnGoods');
    // 取消退款
    Route::patch('refunds/:id/cancel', 'tblOrder.tblOrderRefund/cancelTblOrderRefund');
    // 申请退款
    Route::post('refunds', 'tblOrder.tblOrderRefund/applyTblOrderRefund');
    // 修改退款信息
    Route::put('refunds/:id', 'tblOrder.tblOrderRefund/editTblOrderRefund');
    // 订单退款详情
    Route::get('refunds/:id', 'tblOrder.tblOrderRefund/getTblOrderRefundInfo');
})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);
