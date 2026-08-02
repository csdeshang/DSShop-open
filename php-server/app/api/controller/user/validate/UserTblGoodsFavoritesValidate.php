<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserTblGoodsFavoritesValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
    ];

    protected $message = [
        'id.require' => '商品ID不能为空',
        'id.integer' => '商品ID必须为整数',
        'id.gt' => '商品ID必须大于0',
    ];

    protected $scene = [
        'create' => ['id'],
        'delete' => ['id'],
        'info' => ['id'],
    ];
} 