<?php

namespace app\bloggerapi\controller\blogger\validate;

use app\deshang\base\BaseValidate;

class BloggerInfoValidate extends BaseValidate
{
    protected $rule = [
        'blogger_name' => 'require|max:32',
        'avatar' => 'max:255',
        'description' => 'max:500',
    ];

    protected $message = [
        'blogger_name.require' => '博主昵称不能为空',
        'blogger_name.max' => '博主昵称不能超过32个字符',
        'avatar.max' => '头像地址不能超过255个字符',
        'description.max' => '个人简介不能超过500个字符',
    ];

    protected $scene = [
        'update' => ['blogger_name', 'avatar', 'description'],
    ];
} 