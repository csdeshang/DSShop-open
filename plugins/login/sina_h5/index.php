<?php

//判断是否已经登录
if(!empty($_COOKIE['key'])){
	header("Location:". config('ds_config.h5_site_url'));
	exit;	
}
include_once('config.php' );
include_once('saetv2.ex.class.php' );
$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

$code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );

@header("location:$code_url");
exit;
?>