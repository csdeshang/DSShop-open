<?php

/**
 * index
 */
$lang['goods_class_index_class'] = '商品分类';
$lang['goods_class_index_name'] = '分类名称';
$lang['goods_class_index_help1'] = '店主添加商品的时候，可以选择商品的分类，用户可以根据商品的分类来查询商品列表';
$lang['goods_class_index_help2'] = '点击商品分类名前“+”符号，显示当前商品分类的下级分类';
$lang['goods_class_index_help3'] = '<a>对商品分类作任何更改后，都需要到 设置 -> 清理缓存，新的设置才会生效</a>';

/**
 * 批量编辑
 */
$lang['goods_class_batch_edit_ok'] = '编辑分类成功。';
$lang['goods_class_batch_edit_fail'] = '编辑分类失败。';
$lang['goods_class_batch_edit_paramerror'] = '参数非法';
$lang['goods_class_batch_order_empty_tip'] = '，留空则保持不变';

/**
 * 添加分类
 */
$lang['goods_class_add_name_null'] = '分类名称不能为空';
$lang['goods_class_add_sort_int'] = '分类排序仅能为数字';
$lang['goods_class_add_back_to_list'] = '返回分类列表';
$lang['goods_class_add_again'] = '继续新增分类';
$lang['goods_class_add_name_exists'] = '该分类名称已经存在了，请您换一个';
$lang['goods_class_add_sup_class'] = '上级分类';
$lang['goods_class_add_sup_class_notice'] = '如果选择上级分类，那么新增的分类则为被选择上级分类的子分类';
$lang['goods_class_add_update_sort'] = '数字范围为0~255，数字越小越靠前';
$lang['goods_class_add_display_tip'] = '分类名称是否显示';
$lang['goods_class_null_type'] = '无类型';
$lang['goods_class_add_type_desc_one'] = '如果当前下拉选项中没有适合的类型，可以去';
$lang['goods_class_add_type_desc_two'] = '功能中添加新的类型';
$lang['goods_class_edit_prompts_one'] = '商品类型关系到商品发布时，商品规格的添加，没有商品类型的商品分类将不能添加商品规格。';
$lang['goods_class_edit_prompts_two'] = '默认勾选"关联到子分类"将商品类型附加到该子分类，如子分类不同于上级分类的类型，可以取消勾选并单独对子分类的特定类型进行编辑选择。';
$lang['goods_class_edit_related_to_subclass'] = '关联到子分类';

/**
 * TAG index
 */
$lang['goods_class_tag_name'] = 'TAG名称';
$lang['goods_class_tag_value'] = 'TAG值';
$lang['goods_class_tag_update'] = '更新TAG名称';
$lang['goods_class_tag_update_prompt'] = '更新TAG名称需要话费较长的时间，请耐心等待。';
$lang['goods_class_tag_reset'] = '导入/重置TAG';
$lang['goods_class_tag_reset_confirm'] = '您确定要重新导入TAG吗？重新导入将会重置所有TAG值信息。';
$lang['goods_class_tag_prompts_two'] = 'TAG值是分类搜索的关键字，请精确的填写TAG值。TAG值可以填写多个，每个值之间需要用,隔开。';
$lang['goods_class_tag_prompts_three'] = '导入、重置TAG功能可以根据商品分类重新更新TAG值，TAG值默认为各级商品分类的值。';
$lang['goods_class_tag_choose_data'] = '请选择要操作的数据项。';
/**
 * 重置TAG
 */
$lang['goods_class_reset_tag_fail_no_class'] = '重置TAG失败，没查找到任何分类信息。';
/**
 * 更新TAG名称
 */
$lang['goods_class_update_tag_fail_no_class'] = 'TAG名称更新失败，没查找到任何分类信息。';
/**
 * 删除TAG
 */
$lang['goods_class_tag_del_confirm'] = '你确定要删除商品分类TAG吗?';
$lang['type_add_brand_null_one'] = '还没有品牌，赶快去';
$lang['type_add_brand_null_two'] = '添加品牌吧！';
return $lang;
?>
