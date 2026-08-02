<?php

namespace app\riderapi\controller\rider\validate;

use app\deshang\base\BaseValidate;

class RiderCommentValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'is_reply' => 'in:0,1',
        'is_show' => 'in:0,1',
        'reply_content' => 'require|max:500',
    ];

    protected $message = [
        'id.require' => '评论ID不能为空',
        'id.integer' => '评论ID必须是整数',
        'id.gt' => '评论ID必须大于0',
        'is_reply.in' => '回复状态值不正确',
        'is_show.in' => '显示状态值不正确',
        'reply_content.require' => '回复内容不能为空',
        'reply_content.max' => '回复内容不能超过500个字符',
    ];

    protected $scene = [
        'pages' => ['is_reply', 'is_show'],
        'info' => ['id'],
        'reply' => ['id', 'reply_content'],
    ];
} 