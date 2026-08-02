<?php

namespace app\techapi\controller\technician\validate;

use app\deshang\base\BaseValidate;

class TechnicianGoodsRelValidate extends BaseValidate
{
    protected $rule = [
        'goods_id' => 'require|integer|gt:0',
    ];

    protected $message = [
        'goods_id.require' => '商品ID不能为空',
        'goods_id.integer' => '商品ID必须是整数',
        'goods_id.gt' => '商品ID必须大于0',
    ];

    protected $scene = [
        'update' => ['goods_id'],
    ];
} 