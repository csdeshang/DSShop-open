<?php


use think\facade\Route;
use app\adminapi\middleware\AdminAuthorizeToken;
use app\adminapi\middleware\AdminAuthorizeRole;
use app\adminapi\middleware\AdminAuthorizeLog;

Route::group('tbl-order', function () {

    Route::get('orders/pages', 'tblOrder.TblOrder/getTblOrderPages');
    Route::get('orders/export', 'tblOrder.TblOrder/exportTblOrders');
    Route::get('orders/order-status-dict', 'tblOrder.TblOrder/getTblOrderStatusDict');
    Route::patch('orders/:id/modify-amount', 'tblOrder.TblOrder/modifyTblOrderAmount');
    Route::get('orders/:id', 'tblOrder.TblOrder/getTblOrderInfo');

    Route::get('goods/list', 'tblOrder.TblOrder/getTblOrderGoodsList');
    Route::get('goods/pages', 'tblOrder.TblOrder/getTblOrderGoodsPages');

    Route::get('order-logs', 'tblOrder.TblOrder/getTblOrderLogList');
    Route::get('order-pay-logs', 'tblOrder.TblOrder/getTblOrderPayLogList');

    Route::get('deliveries/delivery-dict', 'tblOrder.TblOrderDelivery/getTblOrderDeliveryDict');
    Route::post('deliveries', 'tblOrder.TblOrderDelivery/addTblOrderDelivery');
    Route::get('deliveries/pages', 'tblOrder.TblOrderDelivery/getTblOrderDeliveryPages');

    Route::get('invoices/pages', 'tblOrder.TblOrderInvoice/getTblOrderInvoicePages');
    Route::patch('invoices/:id/processing', 'tblOrder.TblOrderInvoice/markProcessing');
    Route::patch('invoices/:id/reject', 'tblOrder.TblOrderInvoice/reject');
    Route::patch('invoices/:id/issue', 'tblOrder.TblOrderInvoice/issue');
    Route::patch('invoices/:id/void', 'tblOrder.TblOrderInvoice/void');
    Route::get('invoices/:id', 'tblOrder.TblOrderInvoice/getTblOrderInvoiceInfo');

    Route::get('refunds/refund-status-dict', 'tblOrder.TblOrderRefund/getTblOrderRefundStatusDict');
    Route::get('refunds/pages', 'tblOrder.TblOrderRefund/getTblOrderRefundPages');
    Route::get('refunds/list', 'tblOrder.TblOrderRefund/getTblOrderRefundList');
    Route::post('refunds', 'tblOrder.TblOrderRefund/addTblOrderRefund');
    Route::patch('refunds/:id/agree', 'tblOrder.TblOrderRefund/agreeTblOrderRefund');
    Route::patch('refunds/:id/reject', 'tblOrder.TblOrderRefund/rejectTblOrderRefund');
    Route::patch('refunds/:id/receive-goods', 'tblOrder.TblOrderRefund/receiveGoodsTblOrderRefund');
    Route::post('refunds/:id/retry', 'tblOrder.TblOrderRefund/retryTblOrderRefund');
    Route::get('refunds/:id/logs', 'tblOrder.TblOrderRefund/getTblOrderRefundLogList');
    Route::get('refunds/:id', 'tblOrder.TblOrderRefund/getTblOrderRefundInfo');

})->middleware([
    AdminAuthorizeToken::class,
    AdminAuthorizeRole::class,
    AdminAuthorizeLog::class
]);
