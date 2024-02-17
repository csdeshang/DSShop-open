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
class Inform extends Validate
{
    protected $rule=[
        'informtype_name'=>'require|max:50|min:1',
        'informtype_desc'=>'require|max:50|min:1',
        'informsubject_type_name'=>'require|min:1|max:50',
        'informsubject_content'=>'require|min:1|max:50',
        'informsubject_type_id'=>'require|min:1|max:50',
        'inform_handle_message'=>'require|max:100|min:1',
        'inform_content'=>'require|max:100|min:1',
    ];
    protected $message=[
        'informtype_name.require'=>'举报类型不能为空且不能大于50个字符',
        'informtype_name.max'=>'举报类型不能为空且不能大于50个字符',
        'informtype_name.min'=>'举报类型不能为空且不能大于50个字符',
        'informtype_desc.require'=>'举报类型描述不能为空且不能大于100个字符',
        'informtype_desc.max'=>'举报类型描述不能为空且不能大于100个字符',
        'informtype_desc.min'=>'举报类型描述不能为空且不能大于100个字符',
        'informsubject_type_name.require'=>'举报主题不能为空且不能大于50个字符',
        'informsubject_type_name.min'=>'举报主题不能为空且不能大于50个字符',
        'informsubject_type_name.max'=>'举报主题不能为空且不能大于50个字符',
        'informsubject_content.require'=>'举报内容不能为空且不能大于50个字符',
        'informsubject_content.min'=>'举报内容不能为空且不能大于50个字符',
        'informsubject_content.max'=>'举报内容不能为空且不能大于50个字符',
        'informsubject_type_id.require'=>'举报ID不能为空且不能大于50个字符',
        'informsubject_type_id.min'=>'举报ID不能为空且不能大于50个字符',
        'informsubject_type_id.max'=>'举报ID不能为空且不能大于50个字符',
        'inform_handle_message.require'=>'处理信息不能为空且不能大于100个字符',
        'inform_handle_message.max'=>'处理信息不能为空且不能大于100个字符',
        'inform_handle_message.min'=>'处理信息不能为空且不能大于100个字符',
        'inform_content.require'=>'举报内容不能为空且不能大于100个字符',
        'inform_content.max'=>'举报内容不能为空且不能大于100个字符',
        'inform_content.min'=>'举报内容不能为空且不能大于100个字符',
    ];
    protected $scene = [
        'inform_subject_type_save' => ['informtype_name', 'informtype_desc'],
        'inform_subject_save' => ['informsubject_type_name', 'informsubject_content', 'informsubject_type_id'],
        'inform_handle' => ['inform_handle_message'],
        'inform_save' => ['inform_content', 'informsubject_content'],
    ];
}