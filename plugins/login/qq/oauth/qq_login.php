<?php
require_once(PLUGINS_PATH .DIRECTORY_SEPARATOR. 'login'.DIRECTORY_SEPARATOR. 'qq'.DIRECTORY_SEPARATOR.'comm'.DIRECTORY_SEPARATOR."config.php");


function qq_login($appid, $scope, $callback)
{
    session('state',md5(uniqid(rand(), TRUE))); //CSRF protection
    $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=" 
        . $appid . "&redirect_uri=" . urlencode($callback)
        . "&state=" . session('state')
        . "&scope=".$scope;
    //halt($login_url);
    header("Location:$login_url");
}

//用户点击qq登录按钮调用此函数

qq_login(session("appid"), session("scope"), session("callback"));
?>
