<?php
use app\ExceptionHandle;
use app\Request;

// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
	'think\Paginator' => '\app\common\paginator\Bootstrap',  //修改为分页类所在目录
];
