<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\video\VideoEnum;

class UserVideoCommentValidate extends BaseValidate
{
    protected $rule = [
        'content_type' => 'require|checkContentType',
        'content_id' => 'require|number|gt:0',
        'comment_content' => 'require|length:1,500',
        'pid' => 'number|egt:0',
        'id' => 'require|number|gt:0',
    ];

    protected $message = [
        'content_type.require' => '内容类型不能为空',
        'content_type.checkContentType' => '内容类型必须是video_short、video_drama或live',
        'content_id.require' => '内容ID不能为空',
        'content_id.number' => '内容ID必须是数字',
        'content_id.gt' => '内容ID必须大于0',
        'comment_content.require' => '评论内容不能为空',
        'comment_content.length' => '评论内容长度必须在1-500字符之间',
        'pid.number' => '父评论ID必须是数字',
        'pid.egt' => '父评论ID不能小于0',
        'id.require' => '评论ID不能为空',
        'id.number' => '评论ID必须是数字',
        'id.gt' => '评论ID必须大于0',
    ];

    protected $scene = [
        'create' => ['content_type', 'content_id', 'comment_content', 'pid'],
        'toggleLike' => ['id'],
    ];



    public function checkContentType($value, $rule, $data)
    {
        return array_key_exists($value, VideoEnum::getContentTypeDict());
    }
} 