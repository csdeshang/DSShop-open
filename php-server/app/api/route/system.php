<?php


use think\facade\Route;

use app\api\middleware\UserAuthorizeToken;
use app\api\middleware\UserAuthorizeLog;


Route::group('system', function () {

    // 系统协议
    Route::get('agreements/:code', 'system.SysAgreement/getSysAgreementInfo');

    // 发送短信(登录、注册、找回密码、修改密码)
    Route::post('sms', 'system.SysSms/sendSms');

    // 地区
    Route::get('areas/list', 'system.SysArea/getSysAreaList');
    Route::get('areas/tree', 'system.SysArea/getSysAreaTree');
    
    // 快递列表
    Route::get('expresses/list', 'system.SysExpress/getSysExpressList');

    // 平台列表
    Route::get('platforms/list', 'system.SysPlatform/getSysPlatformList');

    // 文章
    // articles/list (2段) 必须在 articles/:id (2段) 前面
    Route::get('articles/list', 'system.SysArticle/getSysArticleList');
    // articles/pages (2段) 必须在 articles/:id (2段) 前面
    Route::get('articles/pages', 'system.SysArticle/getSysArticlePages');
    Route::get('articles/:id', 'system.SysArticle/getSysArticleDetail');

    // 文章分类
    Route::get('article-categories/list', 'system.SysArticleCategory/getSysArticleCategoryList');
    Route::get('article-categories/tree', 'system.SysArticleCategory/getSysArticleCategoryTree');

    
    // 系统配置
    // configs/by-key、configs/by-keys、configs/by-type、configs/by-types (3段) 必须在 configs/:id (2段) 前面（如果将来添加详情路由）
    Route::get('configs/by-key', 'system.SysConfig/getSysConfigByKey'); // 获取单个配置，通过查询参数 key
    Route::post('configs/by-keys', 'system.SysConfig/getSysConfigsByKeys'); // 获取多个配置，通过请求体 keys 数组
    Route::get('configs/by-type', 'system.SysConfig/getSysConfigsByType'); // 获取指定类型的所有配置，通过查询参数 type
    Route::post('configs/by-types', 'system.SysConfig/getSysConfigsByTypes'); // 获取多个类型的所有配置，通过请求体 types 数组



    // LBS 
    // 注意路由顺序：3段路径必须在2段路径前面
    // 通过IP获取经纬度
    Route::get('lbs/coords-by-ip', 'system.SysLbs/getCoordsByIp');
    // 根据经纬度获取位置信息
    Route::get('lbs/address-by-lng-lat', 'system.SysLbs/getAddressByLngLat');
    // 获取周边位置列表
    Route::post('lbs/around-address-list', 'system.SysLbs/getAroundAddressList');
    // 获取城市地址列表
    Route::post('lbs/city-address-list', 'system.SysLbs/getCityAddressList');
    // 根据关键词检索城市
    Route::get('lbs/city-search', 'system.SysLbs/getCityListByKeyword');
    // 获取城市列表
    Route::get('lbs/cities', 'system.SysLbs/getCityList');


});