<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('distributor', function () {

    // 分销商申请
    Route::post('apply', 'distributor.DistributorApply/createDistributorApply');
    // 分销商申请信息
    Route::get('apply/info', 'distributor.DistributorApply/getDistributorApplyInfo');
    // 分销商申请权限
    Route::get('apply/permission', 'distributor.DistributorApply/getDistributorApplyPermission');

    // 分销商统计信息
    Route::get('statistics', 'distributor.Distributor/getDistributorStatistics');

    // 分销商订单列表
    Route::get('orders/pages', 'distributor.DistributorOrder/getDistributorOrderPages');

    // 分销商佣金列表
    Route::get('balance-logs/pages', 'distributor.DistributorBalance/getDistributorBalanceLogPages');
    // 分销商佣金转至余额
    Route::post('balance/to-user', 'distributor.DistributorBalance/distributorBalanceToUser');

    // 分销商关系
    Route::get('relations/pages', 'distributor.Distributor/getDistributorRelationPages');


})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);