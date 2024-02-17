<?php
require_once(PLUGINS_PATH .DIRECTORY_SEPARATOR. 'login'.DIRECTORY_SEPARATOR. 'qq'.DIRECTORY_SEPARATOR.'comm'.DIRECTORY_SEPARATOR."config.php");
require_once(PLUGINS_PATH .DIRECTORY_SEPARATOR. 'login'.DIRECTORY_SEPARATOR. 'qq'.DIRECTORY_SEPARATOR.'comm'.DIRECTORY_SEPARATOR."utils.php");

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
