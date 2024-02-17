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
class Goods extends Validate
{
    protected $rule = [
        'goods_name'=>'require|max:200',
        'goods_price'=>'require',
        'gc_name'=>'require',
        'gc_sort'=>'between:0,255',
        'goods_content'=>'require',
    ];
    protected $message = [
        'goods_name.require'=>'商品名称不能为空',
        'goods_name.max'=>'商品名称不能超过200个字符',
        'goods_price.require'=>'商品价格不能为空',
        'gc_name.require'=>'分类标题为必填',
        'gc_sort.between'=>'排序应该在0至255之间',
        'goods_content.require'=>'咨询内容不能为空',
    ];
    protected $scene = [
        'edit_save_goods' => ['goods_name', 'goods_price'],
        'goods_class_add' => ['gc_name', 'gc_sort'],//goodsclass
        'goods_class_edit' => ['gc_name', 'gc_sort'],//goodsclass
        'save_consult' => ['goods_content'],//home goods
    ];
}