<?php
require_once(PLUGINS_PATH.'/login/qq_h5/comm/config.php');
require_once(PLUGINS_PATH.'/login/qq_h5/comm/utils.php');

function get_user_info()
{
    $get_user_info = "https://graph.qq.com/user/get_user_info?"
        . "access_token=" . session('qq_access_token')
        . "&oauth_consumer_key=" . session("qq_appid")
        . "&openid=" . session("qq_openid")
        . "&format=json";

    $info = get_url_contents($get_user_info);
    $arr = json_decode($info, true);
    return $arr;
}

?>
