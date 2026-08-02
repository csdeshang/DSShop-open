<?php

namespace app\api\controller\pointsGoods\validate;

use app\deshang\base\BaseValidate;

class PointsGoodsEvaluateValidate extends BaseValidate
{
    // 定义验证规则
    protected $rule = [
        'goods_id' => 'require|integer|gt:0', // 商品ID，必填，必须是大于0的整数
        'limit' => 'integer|between:1,100', // 数量限制，必须是1-100之间的整数
        'evaluate_score' => 'integer|between:1,5', // 评价分数，必须是1-5之间的整数
        'filter_type' => 'in:all,image,good,medium,bad', // 筛选类型，必须是预定义的值
        'page_current' => 'integer|egt:1', // 当前页码，必须是大于等于1的整数
        'page_size' => 'integer|between:1,100', // 每页数量，必须是1-100之间的整数
    ];

    // 定义验证提示
    protected $message = [
        'goods_id.require' => '商品ID不能为空',
        'goods_id.integer' => '商品ID必须是整数',
        'goods_id.gt' => '商品ID必须大于0',
        'limit.integer' => '数量限制必须是整数',
        'limit.between' => '数量限制必须在1-100之间',
        'evaluate_score.integer' => '评价分数必须是整数',
        'evaluate_score.between' => '评价分数必须在1-5之间',
        'filter_type.in' => '筛选类型必须是all、image、good、medium、bad之一',
        'page_current.integer' => '当前页码必须是整数',
        'page_current.egt' => '当前页码必须大于等于1',
        'page_size.integer' => '每页数量必须是整数',
        'page_size.between' => '每页数量必须在1-100之间',
    ];

    // 定义场景
    protected $scene = [
        'list' => ['goods_id', 'limit'], // 列表场景
        'pages' => ['goods_id', 'evaluate_score', 'filter_type', 'page_current', 'page_size'], // 分页场景
    ];
}
