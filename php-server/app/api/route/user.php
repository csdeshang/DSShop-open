<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('user', function () {
    // 用户
    Route::get('user/info', 'user.User/getUserInfo');
    Route::get('user/info-with-roles', 'user.User/getUserInfoWithRoles');
    Route::post('user/info', 'user.User/updateUserInfo');
    Route::put('user/password', 'user.User/editUserPassword');
    Route::put('user/idcard', 'user.User/editUserIdcard');

    // 用户等级
    Route::get('level/info', 'user.UserLevel/getUserLevelInfo');
    Route::get('level/list', 'user.UserLevel/getUserLevelList');


    // 用户绑定微信状态
    Route::get('bind/wechat/status', 'user.UserBindWechat/getBindWechatStatus');
    // 用户解绑微信 (绑定微信 通过 wechatAuth 统一处理)
    Route::post('bind/wechat/delete', 'user.UserBindWechat/deleteBindWechat');

    // 用户绑定手机
    Route::post('bind/mobile', 'user.UserBindMobile/bindMobile');
    Route::post('unbind/mobile', 'user.UserBindMobile/unbindMobile');

    // 用户申请骑手
    Route::post('apply/rider', 'user.UserApplyRider/apply');
    // 用户申请师傅
    Route::post('apply/technician', 'user.UserApplyTechnician/apply');



    // 用户地址
    // 注意路由顺序：更具体的路由（带参数）必须在更通用的路由（不带参数）前面
    // addresses/:id/default (3段) 必须在 addresses/:id (2段) 前面
    // addresses/:id (2段) 必须在 addresses (1段) 前面
    Route::patch('addresses/:id/default', 'user.UserAddress/setDefaultUserAddress');
    Route::get('addresses/:id', 'user.UserAddress/getUserAddressInfo');
    Route::put('addresses/:id', 'user.UserAddress/updateUserAddress');
    Route::delete('addresses/:id', 'user.UserAddress/deleteUserAddress');
    Route::get('addresses', 'user.UserAddress/getUserAddressList');
    Route::post('addresses', 'user.UserAddress/createUserAddress');


    // 用户优惠券
    Route::get('store-coupons/pages', 'user.UserTblStoreCoupon/getUserTblStoreCouponPages');
    Route::post('store-coupons/:id/receive', 'user.UserTblStoreCoupon/receiveTblStoreCoupon');


    // 用户充值记录
    Route::get('recharge-logs/pages', 'user.UserRecharge/getUserRechargeLogPages');
    // 用户提现记录
    Route::get('withdrawal-logs/pages', 'user.UserWithdrawal/getUserWithdrawalLogPages');
    // 用户提现记录详情
    Route::get('withdrawal-logs/:id', 'user.UserWithdrawal/getUserWithdrawalLogInfo');
    // 申请提现
    Route::post('withdrawal-logs/apply', 'user.UserWithdrawal/applyUserWithdrawal');
    // 用户余额记录
    Route::get('balance-logs/pages', 'user.UserBalance/getUserBalanceLogPages');
    // 用户积分记录
    Route::get('points-logs/pages', 'user.UserPoints/getUserPointsLogPages');
    // 积分签到（points-sign/status 须在 points-sign 前）
    Route::get('points-sign/status', 'user.UserPointsSign/getUserPointsSignStatus');
    Route::post('points-sign', 'user.UserPointsSign/doUserPointsSign');
    // 用户成长记录
    Route::get('growth-logs/pages', 'user.UserGrowth/getUserGrowthLogPages');

    // 积分商品订单
    // 注意路由顺序：3段路径必须在2段路径前面
    Route::get('points-goods-orders/pages', 'user.UserPointsGoodsOrder/getPointsGoodsOrderPages');
    // 积分商品订单结算（3段路径，必须在2段路径前面）
    Route::get('points-goods-orders/checkout/check', 'user.UserPointsGoodsOrderCheckout/checkPointsGoodsOrderCheckout');
    Route::post('points-goods-orders/checkout/generate', 'user.UserPointsGoodsOrderCheckout/generatePointsGoodsOrder');
    // 积分商品订单操作（3段路径）
    Route::patch('points-goods-orders/:id/cancel', 'user.UserPointsGoodsOrder/cancelPointsGoodsOrder');
    Route::patch('points-goods-orders/:id/confirm', 'user.UserPointsGoodsOrder/confirmPointsGoodsOrder');
    // 积分商品订单详情（2段路径，必须在最后）
    Route::get('points-goods-orders/:id', 'user.UserPointsGoodsOrder/getPointsGoodsOrderDetail');


    // 用户积分商品评价
    // points-goods-evaluates/pages (2段) 必须在 points-goods-evaluates/:id (2段) 前面
    Route::get('points-goods-evaluates/pages', 'user.UserPointsGoodsEvaluate/getPointsGoodsEvaluatePages');
    Route::get('points-goods-evaluates/:id', 'user.UserPointsGoodsEvaluate/getPointsGoodsEvaluateInfo');
    Route::post('points-goods-evaluates', 'user.UserPointsGoodsEvaluate/addPointsGoodsEvaluate');

    // 用户提现账户
    // withdrawal-accounts/list (2段) 必须在 withdrawal-accounts/:id (2段) 前面
    Route::get('withdrawal-accounts/list', 'user.UserWithdrawal/getUserWithdrawalAccountList');
    Route::get('withdrawal-accounts/:id', 'user.UserWithdrawal/getUserWithdrawalAccountInfo');
    Route::post('withdrawal-accounts', 'user.UserWithdrawal/createUserWithdrawalAccount');
    Route::put('withdrawal-accounts/:id', 'user.UserWithdrawal/updateUserWithdrawalAccount');
    Route::delete('withdrawal-accounts/:id', 'user.UserWithdrawal/deleteUserWithdrawalAccount');


    // 用户常用发票信息
    // invoices/:id/default (3段) 必须在 invoices/:id (2段) 前面
    Route::patch('invoices/:id/default', 'user.UserInvoice/setDefaultUserInvoice');
    Route::get('invoices/:id', 'user.UserInvoice/getUserInvoiceInfo');
    Route::put('invoices/:id', 'user.UserInvoice/updateUserInvoice');
    Route::delete('invoices/:id', 'user.UserInvoice/deleteUserInvoice');
    Route::get('invoices', 'user.UserInvoice/getUserInvoiceList');
    Route::post('invoices', 'user.UserInvoice/createUserInvoice');



    // 用户商品评论
    Route::get('goods-comments/pages', 'user.UserGoodsComment/getUserGoodsCommentPages');
    Route::post('goods-comments', 'user.UserGoodsComment/addUserGoodsComment');


    // 用户商品收藏
    Route::get('goods-favorites/pages', 'user.UserTblGoodsFavorites/getTblGoodsFavoritesPages');
    Route::post('goods-favorites/:id', 'user.UserTblGoodsFavorites/createTblGoodsFavorites');
    Route::delete('goods-favorites/:id', 'user.UserTblGoodsFavorites/deleteTblGoodsFavorites');
    Route::get('goods-favorites/:id', 'user.UserTblGoodsFavorites/getTblGoodsFavoritesInfo');

    // 用户消息通知
    // notice-logs/pages (2段) 必须在 notice-logs/:id (2段) 前面
    Route::get('notice-logs/pages', 'user.UserSysNoticeLog/getUserSysNoticeLogPages');
    Route::get('notice-logs/:id', 'user.UserSysNoticeLog/getUserSysNoticeLogInfo');

    // 用户视频浏览记录
    // video-view-logs/pages, video-view-logs/batch, video-view-logs/clear (2段) 必须在 video-view-logs/:id (2段) 前面
    Route::get('video-view-logs/pages', 'user.UserVideoViewLog/getVideoViewLogPages');
    Route::post('video-view-logs', 'user.UserVideoViewLog/createVideoViewLog');
    Route::delete('video-view-logs/batch', 'user.UserVideoViewLog/batchDeleteVideoViewLog');
    Route::delete('video-view-logs/clear', 'user.UserVideoViewLog/clearAllVideoViewLog');
    Route::delete('video-view-logs/:id', 'user.UserVideoViewLog/deleteVideoViewLog');
    Route::get('video-view-logs/:id', 'user.UserVideoViewLog/getVideoViewLogInfo');

    // 视频评论管理
    // video-comment-likes/:id (2段) 必须在 video-comment-likes (1段) 前面
    Route::patch('video-comment-likes/:id', 'user.UserVideoComment/toggleVideoCommentLike');
    // 视频评论
    Route::post('video-comments', 'user.UserVideoComment/createVideoComment');
    
    // 视频点赞(短视频/短剧)
    Route::post('video-likes', 'user.UserVideoLike/toggleVideoLike');
    // 视频收藏(短视频/短剧)
    Route::post('video-collects', 'user.UserVideoCollect/toggleVideoCollect');
    // 视频分享(短视频/短剧)
    Route::post('video-shares', 'user.UserVideoShare/toggleVideoShare');

    // 博主关注管理
    Route::patch('blogger-follows/:id', 'user.UserBloggerFollow/toggleBloggerFollow');

    
})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);
