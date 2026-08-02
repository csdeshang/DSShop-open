<?php

namespace app\api\controller\editable\validate;

use app\deshang\base\BaseValidate;

use app\common\enum\editable\EditablePageEnum;

class EditablePageValidate extends BaseValidate
{
    protected $rule = [
        'platform' => 'checkPlatform',
        'type' => 'checkType',
        'id' => 'integer|egt:0',
        'device_type' => 'in:pc,mobile',
    ];

    protected $message = [
        'platform.checkPlatform' => '平台标识不正确',
        'type.checkType' => '页面类型不正确',
        'id.integer' => '页面ID必须为整数',
        'id.egt' => '页面ID不能小于0',
        'device_type.in' => '设备类型只能是pc或mobile',
    ];

    protected $scene = [
        'info' => ['platform', 'type', 'id', 'device_type'],
    ];

    public function checkType($value, $rule, $data)
    {
        return array_key_exists($value, EditablePageEnum::getEditablePageTypeDict());
    }
} 