<?php

namespace app\api\controller\video\validate;

use app\deshang\base\BaseValidate;

class VideoShortValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'blogger_id' => 'integer|egt:0',
        'type' => 'integer|egt:0',
        'query_type' => 'in:default,user_liked,user_collected',
        'user_id' => 'integer|egt:0',
        'specified_id' => 'integer|egt:0',
    ];

    protected $message = [
        'id.require' => '短视频ID不能为空',
        'id.integer' => '短视频ID必须为整数',
        'id.gt' => '短视频ID必须大于0',
        'blogger_id.integer' => '博主ID必须为整数',
        'blogger_id.egt' => '博主ID不能小于0',
        'type.integer' => '视频类型必须为整数',
        'type.egt' => '视频类型不能小于0',
        'query_type.in' => '查询类型值无效',
        'user_id.integer' => '用户ID必须为整数',
        'user_id.egt' => '用户ID不能小于0',
        'specified_id.integer' => '指定短视频ID必须为整数',
        'specified_id.egt' => '指定短视频ID不能小于0',
    ];

    protected $scene = [
        'pages' => ['blogger_id', 'type', 'query_type', 'user_id', 'specified_id'],
        'detail' => ['id'],
        'goodsList' => ['id'],
    ];
} 