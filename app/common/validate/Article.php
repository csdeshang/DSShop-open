<?php

namespace app\common\validate;

use think\Validate;
/**
 * ============================================================================
 * DSShop单店铺商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 验证器
 */
class Article extends Validate
{
    protected $rule = [
        'article_sort'=>'number',
        'article_title'=>'require',
        'ac_name'=>'require',
        'ac_sort'=>'number'
    ];
    protected $message = [
        'article_sort.number'=>'排序只能为数字',
        'article_title.require'=>'标题名称不能为空',
        'ac_name.require'=>'分类名称不能为空',
        'ac_sort.number'=>'分类排序仅能为数字'
    ];
    protected $scene = [
        'add' => ['article_sort', 'article_title'],
        'edit' => ['article_sort', 'article_title'],
        'article_class_add' => ['ac_name', 'ac_sort'],
        'article_class_edit' => ['ac_name', 'ac_sort'],
    ];
}