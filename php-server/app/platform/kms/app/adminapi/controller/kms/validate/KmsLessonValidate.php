<?php

namespace app\platform\kms\app\adminapi\controller\kms\validate;

use think\Validate;
use app\common\enum\kms\KmsLessonEnum;

class KmsLessonValidate extends Validate
{
    protected $rule = [
        'goods_id' => 'require|integer|gt:0',
        'lesson_name' => 'require|max:255',
        'lesson_desc' => 'max:1000',
        'lesson_type' => 'require|checkLessonType',
        'video_url' => 'max:500',
        'audio_url' => 'max:500',
        'content' => 'max:10000',
        'sort' => 'integer|egt:0',
        'is_free' => 'in:0,1',
    ];

    protected $message = [
        'goods_id.require' => '商品ID不能为空',
        'goods_id.integer' => '商品ID必须为整数',
        'goods_id.gt' => '商品ID必须大于0',
        'lesson_name.require' => '课时名称不能为空',
        'lesson_name.max' => '课时名称不能超过255个字符',
        'lesson_desc.max' => '课时描述不能超过1000个字符',
        'lesson_type.require' => '课时类型不能为空',
        'lesson_type.checkLessonType' => '课时类型值无效（1:视频 2:图文 3:音频）',
        'video_url.max' => '视频URL不能超过500个字符',
        'audio_url.max' => '音频URL不能超过500个字符',
        'content.max' => '图文内容不能超过10000个字符',
        'sort.integer' => '排序值必须为整数',
        'sort.egt' => '排序值不能小于0',
        'is_free.in' => '免费状态值无效',
    ];

    protected $scene = [
        'list' => ['goods_id'],
        'create' => ['goods_id', 'lesson_name', 'lesson_type'],
        'update' => ['lesson_name', 'lesson_type'],
    ];

    protected function checkLessonType($value, $rule, $data = [])
    {
        return array_key_exists($value, KmsLessonEnum::getLessonTypeDict());
    }
}
