<?php

namespace app\platform\kms\app\api\controller\kms\validate;

use app\deshang\base\BaseValidate;

class KmsLessonValidate extends BaseValidate
{
    protected $rule = [
        'goods_id' => 'require|integer|gt:0',
        'id' => 'require|integer|gt:0',
    ];

    protected $message = [
        'goods_id.require' => '商品ID不能为空',
        'goods_id.integer' => '商品ID必须为整数',
        'goods_id.gt' => '商品ID必须大于0',
        'id.require' => '课时ID不能为空',
        'id.integer' => '课时ID必须为整数',
        'id.gt' => '课时ID必须大于0',
    ];

    protected $scene = [
        'list' => ['goods_id'],
        'detail' => ['id'],
    ];
}

