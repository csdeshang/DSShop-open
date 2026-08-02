<?php

namespace app\bloggerapi\controller\video\validate;

use app\deshang\base\BaseValidate;

class VideoCommentValidate extends BaseValidate
{
    protected $rule = [
        'id'    => 'require|integer|gt:0',
        'field' => 'require|in:is_top,is_show',
    ];

    protected $message = [
        'id.require'    => '评论ID不能为空',
        'id.integer'    => '评论ID必须为整数',
        'id.gt'         => '评论ID必须大于0',
        'field.require' => '字段名不能为空',
        'field.in'      => '字段名只能是is_top或is_deleted',
    ];

    protected $scene = [
        'toggleField' => ['id', 'field'],
    ];
} 