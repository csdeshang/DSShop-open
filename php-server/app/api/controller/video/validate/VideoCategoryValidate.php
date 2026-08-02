<?php

namespace app\api\controller\video\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\video\VideoCategoryEnum;

/**
 * 视频分类验证器
 */
class VideoCategoryValidate extends BaseValidate
{
    /**
     * 验证规则
     * @var array
     */
    protected $rule = [
        'type' => 'require|checkType',
    ];

    /**
     * 验证消息
     * @var array
     */
    protected $message = [
        'type.require' => '分类类型不能为空',
        'type.checkType' => '分类类型只能是 short/drama/live',
    ];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
        'tree' => ['type'],
        'list' => ['type'],
    ];

    protected function checkType($value, $rule, $data)
    {
        return array_key_exists($value, VideoCategoryEnum::getTypeDict());
    }
    
}