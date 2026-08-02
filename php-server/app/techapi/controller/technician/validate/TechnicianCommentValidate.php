<?php

namespace app\techapi\controller\technician\validate;

use app\deshang\base\BaseValidate;

/**
 * 师傅评论验证器
 */
class TechnicianCommentValidate extends BaseValidate
{
    /**
     * 验证规则
     * @var array
     */
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'is_reply' => 'in:0,1',
        'is_show' => 'in:0,1',
        'reply_content' => 'require|length:1,500',
    ];

    /**
     * 验证消息
     * @var array
     */
    protected $message = [
        'id.require' => '评论ID不能为空',
        'id.integer' => '评论ID必须为整数',
        'id.gt' => '评论ID必须大于0',
        'is_reply.in' => '回复状态值错误',
        'is_show.in' => '显示状态值错误',
        'reply_content.require' => '回复内容不能为空',
        'reply_content.length' => '回复内容长度不能超过500字符',
    ];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
        'pages' => ['is_reply', 'is_show'],
        'info' => ['id'],
        'reply' => ['id', 'reply_content'],
    ];
} 