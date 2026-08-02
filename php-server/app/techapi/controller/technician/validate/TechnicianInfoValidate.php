<?php

namespace app\techapi\controller\technician\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\technician\TechnicianEnum;

class TechnicianInfoValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require|max:20|chsDash',
        'mobile' => 'require|mobile',
        'avatar' => 'max:255',
        'gender' => 'require|in:0,1,2',
        'certificate_info' => 'max:255',
        'work_years' => 'integer|egt:0',
        'description' => 'max:1000',
        'technician_status' => 'require|checkTechnicianStatus',
    ];

    protected $message = [
        'name.require' => '师傅名称不能为空',
        'name.max' => '师傅名称不能超过20个字符',
        'name.chsDash' => '师傅名称只能包含汉字、字母、数字、下划线和破折号',
        'mobile.require' => '联系电话不能为空',
        'mobile.mobile' => '联系电话格式不正确',
        'avatar.max' => '头像链接不能超过255个字符',
        'gender.require' => '性别不能为空',
        'gender.in' => '性别值不正确',
        'certificate_info.max' => '资质证书信息不能超过255个字符',
        'work_years.integer' => '工作年限必须是整数',
        'work_years.egt' => '工作年限必须大于等于0',
        'description.max' => '描述不能超过1000个字符',
        'technician_status.require' => '师傅状态不能为空',
        'technician_status.checkTechnicianStatus' => '师傅状态值不正确',
    ];

    protected $scene = [
        'update' => ['name', 'mobile', 'avatar', 'gender', 'certificate_info', 'work_years', 'description'],
        'setStatus' => ['technician_status'],
    ];
    
    // 验证师傅状态是否在允许的范围内
    protected function checkTechnicianStatus($value, $rule, $data = [])
    {
        return array_key_exists($value, TechnicianEnum::getTechnicianStatusDict());
    }
}
