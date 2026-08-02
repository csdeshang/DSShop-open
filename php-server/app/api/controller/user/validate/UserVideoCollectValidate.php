<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\video\VideoEnum;

class UserVideoCollectValidate extends BaseValidate
{
    protected $rule = [
        'content_type' => 'require|checkContentType',
        'content_id' => 'require|integer|gt:0',
    ];

    protected $message = [
        'content_type.require' => '内容类型不能为空',
        'content_type.checkContentType' => '内容类型值无效',
        'content_id.require' => '内容ID不能为空',
        'content_id.integer' => '内容ID必须为整数',
        'content_id.gt' => '内容ID必须大于0',
    ];

    protected $scene = [
        'toggle' => ['content_type', 'content_id'],
    ];

    protected function checkContentType($value, $rule, $data)
    {
        $contentTypeDict = VideoEnum::getContentTypeDict();
        return in_array($value, $contentTypeDict);
    }
} 