<?php
header('Content-Type: text/html; charset=UTF-8');
//包含配置信息
$data = rkcache("config", true);
//判读新浪微博登录是否开启
if($data['sina_isuse'] != 1){
	@header('location: ' .  config('ds_config.h5_site_url'));
	exit;
}
define( "WB_AKEY" ,  trim($data['sina_wb_akey']));
define( "WB_SKEY" ,  trim($data['sina_wb_skey']));
define( "WB_CALLBACK_URL" , API_SITE_URL.DIRECTORY_SEPARATOR.'api/oa_sina?step=callback');