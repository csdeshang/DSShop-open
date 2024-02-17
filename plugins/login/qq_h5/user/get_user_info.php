<?php
require_once(PLUGINS_PATH.'/login/qq_h5/comm/config.php');
require_once(PLUGINS_PATH.'/login/qq_h5/comm/utils.php');

function get_user_info()
{
    $get_user_info = "https://graph.qq.com/user/get_user_info?"
        . "access_token=" . session('access_token')
        . "&oauth_consumer_key=" . session("appid")
        . "&openid=" . session("openid")
        . "&format=json";

    $info = get_url_contents($get_user_info);
    $arr = json_decode($info, true);
    return $arr;
}

?>
