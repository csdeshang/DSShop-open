<?php

//判断是否已经登录
if(!empty($_COOKIE['key'])){
	header("Location:". config('ds_config.h5_site_url'));
	exit;	
}
include_once(PLUGINS_PATH.DIRECTORY_SEPARATOR.'login'.DIRECTORY_SEPARATOR.'sina_h5'.DIRECTORY_SEPARATOR.'config.php');
include_once(PLUGINS_PATH.DIRECTORY_SEPARATOR.'login'.DIRECTORY_SEPARATOR.'sina_h5'.DIRECTORY_SEPARATOR.'saetv2.ex.class.php' );
$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY);
///////////code需要传递////////////
if (isset($_REQUEST['code'])) {
	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = WB_CALLBACK_URL;
	try {
		$token = $o->getAccessToken( 'code', $keys ) ;
	} catch (OAuthException $e) {
	}
}

if ($token) {
    session('slast_key', $token);
	setcookie( 'weibojs_'.$o->client_id, http_build_query($token) );
	//转到注册登录页面

	@header('location: ' . API_SITE_URL . '/connectsina/index');
	//exit;
} else { echo "授权失败。"; }
