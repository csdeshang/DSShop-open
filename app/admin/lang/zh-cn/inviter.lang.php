<?php
/* 分销设置 */
$lang['inviter_back']			= '分销背景图片';
$lang['inviter_open']                        ='分销开关';
$lang['inviter_level']                        ='分销级别';
$lang['inviter_level_1']                        ='一级分销';
$lang['inviter_level_2']                        ='二级分销';
$lang['inviter_level_3']                        ='三级分销';
$lang['inviter_show']                        ='详情页显示分销佣金';
$lang['inviter_show_notice']                        ='开启后，分销员可以在商品详情页看到分销佣金';
$lang['inviter_return']                        ='分销员返佣';
$lang['inviter_return_notice']                        ='开启后，分销员自己购买将获得一级分销佣金';
$lang['inviter_view']                        ='分销员审核';
$lang['inviter_view_notice']                        ='开启后，分销员需要审核后才能分销';
$lang['inviter_condition']                        ='分销员条件';
$lang['inviter_condition_0']                        ='无';
$lang['inviter_condition_1']                        ='历史消费金额达到';
$lang['inviter_ratio_1_notice']                        ='基数为分销金额';
$lang['inviter_ratio_2_notice']                        ='基数为分销金额';
$lang['inviter_ratio_3_notice']                        ='基数为分销金额';
$lang['inviter_ratio_error'] = '分销比例总额不可超过100%';

/* 分销商品 */
$lang['inviter_ratio'] = '佣金比例';
$lang['goods_price'] = '价格';
$lang['inviter_total_quantity'] = '已分销件数';
$lang['inviter_total_amount'] = '已分销金额';
$lang['inviter_amount'] = '已分销佣金';
$lang['inviter_ratio_1'] = '1级佣金比例';
$lang['inviter_ratio_2'] = '2级佣金比例';
$lang['inviter_ratio_3'] = '3级佣金比例';
$lang['ds_percent'] = '%';

/* 分销员管理 */
$lang['member_email'] = '电子邮箱';
$lang['member_truename'] = '真实姓名';
$lang['member_addtime'] = '注册时间';
$lang['inviter_goods_quantity'] = '分销商品数';
$lang['inviter_goods_amount'] = '分销商品金额';
$lang['inviter_quantity'] = '分销下级成员';
$lang['inviter_member_1'] = '一级成员';
$lang['inviter_member_2'] = '二级成员';
$lang['inviter_member_3'] = '三级成员';
$lang['inviter_state_1'] = '已启用';
$lang['inviter_state_2'] = '已禁用';
$lang['adjust_superior'] = '调整上级';
$lang['inviter_parent_name'] = '上级';
$lang['inviter_member_empty'] = '没有该分销员';
$lang['inviter_class'] = '分销员等级';


/* 分销员等级 */
$lang['inviterclass_amount'] = '佣金门槛';
$lang['inviterclass_empty'] = '没有该分销员等级';
$lang['inviterclass_amount_tips'] = '分销员历史已结算佣金达到此金额后，自动升级到对应等级';
$lang['inviter_parent_error'] = '分销员的上级不能是自己';
$lang['inviter_parent_error2'] = '分销员的上级不能是自己的下级成员';

/* 调整上级 */
$lang['inviter_name'] = '分销员用户名';

/* 分销订单 */
$lang['money'] = '分销佣金';
$lang['remark'] = '分销详情';
$lang['valid'] = '是否有效';
$lang['addtime'] = '添加时间';

$lang['order_list'] = '订单列表';
$lang['goods_list'] = '商品列表';
$lang['goods_add'] = '新增商品';
$lang['inviter_ratio'] = '佣金比例';
$lang['goods_price'] = '价格';
$lang['inviter_total_quantity'] = '已分销件数';
$lang['inviter_total_amount'] = '已分销金额';
$lang['inviter_amount'] = '已分销佣金';
$lang['cannot_exceed'] = '不可超过';
$lang['inviter_goods'] = '分销商品';
$lang['inviter_goods_commonid_required'] = '请选择分销商品';
$lang['inviter_ratio_required'] = '请填写分佣比例';
$lang['inviter_ratio_number'] = '请填写数字';
$lang['inviter_ratio_min'] = '分佣比例不能低于';
$lang['inviter_ratio_max'] = '分佣比例不能超过';
$lang['goods_add_success'] = '分销商品增加成功';
$lang['goods_add_fail'] = '分销商品增加失败';
$lang['goods_edit_success'] = '分销商品编辑成功';
$lang['goods_edit_fail'] = '分销商品编辑失败';
$lang['goods_del_success'] = '分销商品删除成功';
$lang['goods_del_fail'] = '分销商品删除失败';
$lang['inviter_goods_empty'] = '没有该分销商品';
$lang['inviter_name'] = '分销员';
$lang['inviter_addtime'] = '添加时间';
$lang['goods_quantity'] = '商品数量';
$lang['goods_amount'] = '商品总金额';

//goods_add
$lang['mall_price'] = '商城价';
$lang['choose_goods'] = '选择商品';
$lang['search_items'] = '第一步：搜索店内商品';

//order_list
$lang['commission'] = '佣金';
$lang['remark'] = '备注';
$lang['whether_effective'] = '是否有效';

//search_goods
$lang['sale_price'] = '销售价';
$lang['already_distributed_goods'] = '已为分销商品';
$lang['choose_distribute_goods'] = '选择为分销商品';
$lang['orderinviter_valid_array'][0]='待结算';
$lang['orderinviter_valid_array'][1]='有效';
$lang['orderinviter_valid_array'][2]='无效';
return $lang;