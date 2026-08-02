<?php

namespace app\swagger\controller;

use app\BaseController;


class Index extends BaseController
{
    public function index()
    {

        $openapi = \OpenApi\Generator::scan(['D:\phpstudy_pro\WWW\git\dsadmin\php-server\app']);
        header('Content-Type: application/x-yaml');
        echo $openapi->toYaml();

    }
}
