<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserApplyTechnicianValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require|max:32',
        'mobile' => 'require|mobile',
        'gender' => 'require|in:0,1,2',
        'certificate_info' => 'max:255',
        'work_years' => 'integer|egt:0',
        'description' => 'max:255',
        'apply_remark' => 'max:255',
    ];

    protected $message = [
        'name.require' => '真实姓名不能为空',
        'name.max' => '真实姓名不能超过32位',
        'mobile.require' => '手机号码不能为空',
        'mobile.mobile' => '手机号码格式不正确',
        'gender.require' => '性别不能为空',
        'gender.in' => '性别值无效',
        'certificate_info.max' => '证书信息不能超过255位',
        'work_years.integer' => '工作年限必须为整数',
        'work_years.egt' => '工作年限必须大于等于0',
        'description.max' => '个人描述不能超过255位',
        'apply_remark.max' => '申请备注不能超过255位',
    ];

    protected $scene = [
        'apply' => ['name', 'mobile', 'gender', 'certificate_info', 'work_years', 'description', 'apply_remark'],
    ];
}
