<?php

namespace app\bloggerapi\controller\video\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\video\VideoShortEnum;

class VideoShortValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|gt:0',
        'type' => 'require|checkType', // 内容类型，使用枚举验证
        'title' => 'require|length:1,32', 
        'description' => 'require|length:1,1000', // TEXT类型，设置合理长度
        'cid' => 'require|gt:0',
        'cover_image' => 'require|length:1,255', // 对应VARCHAR(255)
        'video_url' => 'requireIf:type,1|length:1,255', // 当type=1时必填，对应VARCHAR(255)
        'images_url' => 'requireIf:type,2|length:1,5000', // 当type=2时必填，对应TEXT类型
        'goods_ids' => 'isAllNumbers',
    ];

    protected $message = [
        'id.require' => '短视频ID不能为空',
        'id.gt' => '短视频ID必须大于0',
        
        'type.require' => '内容类型不能为空',
        'type.checkType' => '内容类型值无效（1:视频 2:图片）',
        
        'title.require' => '标题不能为空',
        'title.length' => '标题长度必须在1-32个字符之间',
        
        'description.require' => '描述不能为空',
        'description.length' => '描述长度不能超过1000个字符',
        
        'cid.require' => '分类ID不能为空',
        'cid.gt' => '分类ID必须大于0',
        
        'cover_image.require' => '封面图片不能为空',
        'cover_image.length' => '封面图片地址长度不能超过255个字符',
        
        'video_url.requireIf' => '纯视频类型必须上传视频文件',
        'video_url.length' => '视频地址长度不能超过255个字符',
        
        'images_url.requireIf' => '图文混合类型必须上传图片文件',
        'images_url.length' => '图片地址长度不能超过5000个字符',
        'goods_ids.isAllNumbers' => '商品ID列表必须是数字',
    ];

    protected $scene = [
        'publish' => ['type', 'title', 'description', 'cid', 'cover_image', 'video_url', 'images_url', 'goods_ids'],
        'delete' => ['id'],
    ];

    // 验证内容类型
    public function checkType($value, $rule, $data)
    {
        return array_key_exists($value, VideoShortEnum::getTypeDict());
    }
}
