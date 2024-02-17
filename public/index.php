<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;
$i=strpos($_SERVER['REQUEST_URI'],'/bmap/');
if($i!==false){
    if(isset($_SERVER["HTTP_REFERER"])){
        $url= parse_url($_SERVER["HTTP_REFERER"]);
        header('Access-Control-Allow-Origin:'.$url['scheme'].'://'.$url['host'].(isset($url['port'])?(':'.$url['port']):''));
        header('Access-Control-Allow-Credentials:true');
    }

    echo file_get_contents('https://api.map.baidu.com/'.substr($_SERVER['REQUEST_URI'], $i+6));exit;
}
require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
