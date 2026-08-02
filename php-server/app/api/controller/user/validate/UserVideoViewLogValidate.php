<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserVideoViewLogValidate extends BaseValidate
{
    protected $rule = [
        'content_type' => 'require|in:short,drama,live',
        'content_id' => 'require|number|gt:0',
        'id' => 'require|number|gt:0',
        'ids' => 'require|array|isAllNumbers',
    ];

    protected $message = [
        'content_type.require' => '内容类型不能为空',
        'content_type.in' => '内容类型必须是short、drama或live',
        'content_id.require' => '内容ID不能为空',
        'content_id.number' => '内容ID必须是数字',
        'content_id.gt' => '内容ID必须大于0',
        'id.require' => '记录ID不能为空',
        'id.number' => '记录ID必须是数字',
        'id.gt' => '记录ID必须大于0',
        'ids.require' => '记录ID数组不能为空',
        'ids.array' => '记录ID必须是数组格式',
        'ids.isAllNumbers' => '记录ID数组只能包含数字',
    ];

    protected $scene = [
        'create' => ['content_type', 'content_id'],
        'batchDelete' => ['ids'],
        'pages' => ['content_type'],
        'clearAll' => ['content_type'],
        'info' => ['id'],
    ];
} 