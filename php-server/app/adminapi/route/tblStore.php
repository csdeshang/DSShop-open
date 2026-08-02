<?php


use think\facade\Route;
use app\adminapi\middleware\AdminAuthorizeToken;
use app\adminapi\middleware\AdminAuthorizeRole;
use app\adminapi\middleware\AdminAuthorizeLog;

Route::group('tbl-store', function () {

    // 打印机管理（平台能力）
    Route::get('printers/pages', 'tblStore.TblStorePrinter/getTblStorePrinterPages');
    Route::post('printers', 'tblStore.TblStorePrinter/createTblStorePrinter');
    Route::patch('printers/:id/bind', 'tblStore.TblStorePrinter/bindTblStorePrinter');
    Route::patch('printers/:id/unbind', 'tblStore.TblStorePrinter/unbindTblStorePrinter');
    Route::get('printers/:id/status', 'tblStore.TblStorePrinter/getTblStorePrinterStatus');
    Route::post('printers/:id/print-order', 'tblStore.TblStorePrinter/printTblOrder');
    Route::get('printers/:id', 'tblStore.TblStorePrinter/getTblStorePrinterInfo');
    Route::put('printers/:id', 'tblStore.TblStorePrinter/updateTblStorePrinter');
    Route::delete('printers/:id', 'tblStore.TblStorePrinter/deleteTblStorePrinter');
    Route::get('printer-logs/pages', 'tblStore.TblStorePrinter/getTblStorePrinterLogPages');

    // 优惠券（平台券）
    Route::get('coupons/pages', 'tblStore.TblStoreCoupon/getTblStoreCouponPages');
    Route::get('coupons/list', 'tblStore.TblStoreCoupon/getTblStoreCouponList');
    Route::get('coupons/:id', 'tblStore.TblStoreCoupon/getTblStoreCouponInfo');
    Route::post('coupons', 'tblStore.TblStoreCoupon/createTblStoreCoupon');
    Route::put('coupons/:id', 'tblStore.TblStoreCoupon/updateTblStoreCoupon');
    Route::get('coupon-users/pages', 'tblStore.TblStoreCoupon/getTblStoreCouponUserPages');

})->middleware([
    AdminAuthorizeToken::class,
    AdminAuthorizeRole::class,
    AdminAuthorizeLog::class
]);
