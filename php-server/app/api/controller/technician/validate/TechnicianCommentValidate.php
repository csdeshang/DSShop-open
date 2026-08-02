<?php

namespace app\api\controller\technician\validate;

use app\deshang\base\BaseValidate;

/**
 * 师傅评价验证器
 */
class TechnicianCommentValidate extends BaseValidate
{
    /**
     * 验证规则
     * @var array
     */
    protected $rule = [
        'technician_id' => 'integer|gt:0',
        'is_reply' => 'in:0,1',
        'service_score' => 'in:1,2,3,4,5',
        'limit' => 'integer|between:1,100',
        'id' => 'require|integer|gt:0',
        'page_current' => 'integer|egt:1',
        'page_size' => 'integer|between:1,100',
    ];

    /**
     * 验证消息
     * @var array
     */
    protected $message = [
        'technician_id.integer' => '师傅ID必须为整数',
        'technician_id.gt' => '师傅ID必须大于0',
        'is_reply.in' => '回复状态值错误',
        'service_score.in' => '评分值错误',
        'limit.integer' => '限制条数必须为整数',
        'limit.between' => '限制条数必须在1-100之间',
        'id.require' => '评论ID不能为空',
        'id.integer' => '评论ID必须为整数',
        'id.gt' => '评论ID必须大于0',
        'page_current.integer' => '当前页码必须为整数',
        'page_current.egt' => '当前页码必须大于等于1',
        'page_size.integer' => '每页数量必须为整数',
        'page_size.between' => '每页数量必须在1-100之间',
    ];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
        'pages' => ['technician_id', 'is_reply', 'service_score', 'page_current', 'page_size'],
        'list' => ['technician_id', 'is_reply', 'service_score', 'limit'],
        'info' => ['id'],
    ];
} 