<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserGoodsCommentValidate extends BaseValidate
{
    protected $rule = [
        'order_id' => 'require|integer|gt:0',
        'goods_comments' => 'require|array',
        'is_anonymous' => 'in:0,1',
        'rider_content' => 'max:255',
        'rider_service_score' => 'in:1,2,3,4,5',
        'technician_content' => 'max:255',
        'technician_service_score' => 'in:1,2,3,4,5',
    ];

    protected $message = [
        'order_id.require' => '订单ID不能为空',
        'order_id.integer' => '订单ID必须为整数',
        'order_id.gt' => '订单ID必须大于0',
        'goods_comments.require' => '商品评论不能为空',
        'goods_comments.array' => '商品评论必须为数组',
        'is_anonymous.in' => '匿名设置值无效',
        'rider_content.max' => '配送员评价内容不能超过255位',
        'rider_service_score.in' => '配送员服务评分值无效',
        'technician_content.max' => '师傅评价内容不能超过255位',
        'technician_service_score.in' => '师傅服务评分值无效',
    ];

    protected $scene = [
        'add' => ['order_id', 'goods_comments', 'is_anonymous', 'rider_content', 'rider_service_score', 'technician_content', 'technician_service_score'],
    ];
} 