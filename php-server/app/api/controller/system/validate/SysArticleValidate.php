<?php

namespace app\api\controller\system\validate;

use app\deshang\base\BaseValidate;

class SysArticleValidate extends BaseValidate
{
    protected $rule = [
        'cid' => 'integer|egt:0',
        'keyword' => 'max:20',
        'id' => 'require|integer|egt:0',
        'page_current' => 'integer|egt:1',
        'page_size' => 'integer|between:1,100',
    ];

    protected $message = [
        'cid.integer' => '分类ID必须为整数',
        'cid.egt' => '分类ID不能小于0',
        'keyword.max' => '搜索关键词不能超过20个字符',
        'id.require' => '文章ID不能为空',
        'id.integer' => '文章ID必须为整数',
        'id.egt' => '文章ID不能小于0',
        'page_current.integer' => '当前页码必须为整数',
        'page_current.egt' => '当前页码必须大于等于1',
        'page_size.integer' => '每页数量必须为整数',
        'page_size.between' => '每页数量必须在1-100之间',
    ];

    protected $scene = [
        'list' => ['cid'],
        'pages' => ['cid', 'keyword', 'page_current', 'page_size'],
        'detail' => ['id'],
    ];
} 