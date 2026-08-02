<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('tbl-store', function () {

    // 平台优惠券
    Route::get('coupons/pages', 'tblStore.tblStoreCoupon/getTblStoreCouponPages');
    Route::get('coupons/list', 'tblStore.tblStoreCoupon/getTblStoreCouponList');
    Route::get('coupons/:id', 'tblStore.tblStoreCoupon/getTblStoreCouponInfo');

})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);
