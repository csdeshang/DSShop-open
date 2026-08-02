<?php

namespace app\api\controller\tblGoods\validate;

use app\deshang\base\BaseValidate;

class TblGoodsCategoryValidate extends BaseValidate
{
    protected $rule = [
        'platform' => 'require|checkPlatform',
    ];

    protected $message = [
        'platform.require' => '平台标识不能为空',
        'platform.checkPlatform' => '平台标识不正确',
    ];

    protected $scene = [
        'tree' => ['platform'],
    ];
} 