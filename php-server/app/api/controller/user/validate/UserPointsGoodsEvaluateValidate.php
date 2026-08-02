<?php

namespace app\api\controller\user\validate;

use think\Validate;

class UserPointsGoodsEvaluateValidate extends Validate
{
    protected $rule = [
        'order_id' => 'require|integer|gt:0',
        'goods_id' => 'require|integer|gt:0',
        'evaluate_content' => 'require|length:1,500',
        'evaluate_images' => 'length:0,2000',
        'evaluate_score' => 'require|integer|between:1,5',
        'is_anonymous' => 'integer|in:0,1',
        'id' => 'require|integer|gt:0',
    ];

    protected $message = [
        'order_id.require' => '订单ID不能为空',
        'order_id.integer' => '订单ID必须是整数',
        'order_id.gt' => '订单ID必须大于0',
        'goods_id.require' => '商品ID不能为空',
        'goods_id.integer' => '商品ID必须是整数',
        'goods_id.gt' => '商品ID必须大于0',
        'evaluate_content.require' => '评价内容不能为空',
        'evaluate_content.length' => '评价内容长度必须在1-500个字符之间',
        'evaluate_images.length' => '评价图片长度不能超过2000个字符',
        'evaluate_score.require' => '评价分数不能为空',
        'evaluate_score.integer' => '评价分数必须是整数',
        'evaluate_score.between' => '评价分数必须在1-5之间',
        'is_anonymous.integer' => '匿名状态必须是整数',
        'is_anonymous.in' => '匿名状态必须是0或1',
        'id.require' => 'ID不能为空',
        'id.integer' => 'ID必须是整数',
        'id.gt' => 'ID必须大于0',
    ];

    protected $scene = [
        'add' => ['order_id', 'goods_id', 'evaluate_content', 'evaluate_images', 'evaluate_score', 'is_anonymous'],
        'pages' => ['goods_id', 'evaluate_score'],
        'info' => ['id'],
    ];
}
