<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('tbl-goods', function () {

    // 获取商品列表
    // 全局商品列表
    Route::get('goods/list', 'tblGoods.TblGoods/getTblGoodsList');
    // 全局商品分页列表
    Route::get('goods/pages', 'tblGoods.TblGoods/getTblGoodsPages');
    // 随机商品列表（猜你喜欢）
    Route::get('goods/random', 'tblGoods.TblGoods/getRandomGoods');


    // 商品分类
    Route::get('categories/tree', 'tblGoods.TblGoodsCategory/getTblGoodsCategoryTree');

    // 商品评论
    Route::get('comments/pages', 'tblGoods.TblGoodsComment/getTblGoodsCommentPages');



})->middleware([
    UserAuthorizeToken::class,
    UserAuthorizeLog::class,
]);