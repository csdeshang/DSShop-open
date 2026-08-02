<?php

namespace app\api\controller\tblGoods\validate;

use app\deshang\base\BaseValidate;

class TblGoodsCommentValidate extends BaseValidate
{
    protected $rule = [
        'goods_id' => 'require|integer|gt:0',
        'goods_score' => 'integer|between:1,5',
        'filter_type' => 'in:all,image,good,medium,bad',
        'page_current' => 'integer|egt:1',
        'page_size' => 'integer|between:1,100',
    ];

    protected $message = [
        'goods_id.require' => '商品ID不能为空',
        'goods_id.integer' => '商品ID必须为整数',
        'goods_id.gt' => '商品ID必须大于0',
        'goods_score.integer' => '商品评分必须为整数',
        'goods_score.between' => '商品评分必须在1-5之间',
        'filter_type.in' => '筛选类型值不正确',
        'page_current.integer' => '当前页码必须为整数',
        'page_current.egt' => '当前页码必须大于等于1',
        'page_size.integer' => '每页数量必须为整数',
        'page_size.between' => '每页数量必须在1-100之间',
    ];

    protected $scene = [
        'pages' => ['goods_id', 'goods_score', 'filter_type', 'page_current', 'page_size'],
    ];
} 