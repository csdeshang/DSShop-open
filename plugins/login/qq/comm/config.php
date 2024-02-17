<?php
/**
 * PHP SDK for QQ登录 OpenAPI
 *
 * @version 1.2
 * @author connect@qq.com
 * @copyright © 2011, Tencent Corporation. All rights reserved.
 */

/**
 * @brief 本文件作为demo的配置文件。
 */

/**
 * 正式运营环境请关闭错误信息
 * ini_set("error_reporting", E_ALL);
 * ini_set("display_errors", TRUE);
 * QQDEBUG = true  开启错误提示
 * QQDEBUG = false 禁止错误提示
 * 默认禁止错误信息
 */
define("QQDEBUG", false);
if (defined("QQDEBUG") && QQDEBUG)
{
    @ini_set("error_reporting", E_ALL);
    @ini_set("display_errors", TRUE);
}

/**
 * session
 */
//require_once(PLUGINS_PATH.DIRECTORY_SEPARATOR.'login'.DIRECTORY_SEPARATOR.'qq'.DIRECTORY_SEPARATOR.'comm'.DIRECTORY_SEPARATOR."session.php");

//包含配置信息
$data = rkcache("config", true);
//qq互联是否开启
if($data['qq_isuse'] != 1){
	@header('location: index.php');
	exit;
}

//申请到的appid
$qq_appid = trim($data['qq_appid']);

//申请到的appkey
$qq_appkey = trim($data['qq_appkey']);

//QQ登录成功后回调的地址
$callback = HTTP_TYPE.$_SERVER['HTTP_HOST']."/home/Api/oa_qq_callback.html";

//调用的api接口(访问用户资料get_user_info)
$scope = "get_user_info";

//用session保存调用
session('appid', $qq_appid);
session('appkey', $qq_appkey);
session('callback', $callback);
session('scope', $scope);
?>
