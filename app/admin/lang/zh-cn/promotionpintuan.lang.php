<?php
$lang['pintuan_list'] = '活动列表';

$lang['pintuan_list_help1'] = '卖家发布的拼团活动列表';
$lang['pintuan_list_help2'] = '取消操作后的活动不可恢复，请慎重操作';
$lang['pintuan_list_help3'] = '点击详细按钮，查看活动详细信息';

/* 拼团相关 */
$lang['pintuan_name'] = '拼团名称';
$lang['pintuan_name_error'] = '请输入拼团名称';
$lang['pintuan_name_explain'] = '输入拼团的名称';
$lang['pintuan_starttime'] = '拼团开始时间';
$lang['pintuan_end_time'] = '拼团结束时间';
$lang['pintuan_limit_number'] = '参团人数限制';
$lang['pintuan_count'] = '组团数量';
$lang['pintuan_ok_count'] = '成团数量';
$lang['pintuan_goods_name'] = '拼团商品名称';
$lang['pintuan_state'] = '拼团状态';

/* 拼团订单 */
$lang['pintuanorder'] = '拼团订单';
$lang['pintuan_add_success'] = '新增拼团成功';
$lang['pintuan_add'] = '新增拼团';
$lang['pintuan_goods'] = '拼团商品';
$lang['pintuan_goods_explain'] = '选择参加拼团对应的商品';
$lang['greater_than_start_time'] = '结束时间必须大于开始时间';

/* 拼团开团相关 */
$lang['pintuangroup_limit_number'] = '成团人数';
$lang['pintuangroup_limit_hour'] = '成团时限';
$lang['pintuangroup_joined'] = '己参团人数';
$lang['pintuangroup_headid'] = '团长用户编号';
$lang['pintuangroup_starttime'] = '开团时间';
$lang['pintuangroup_state'] = '成团状态';

//pintuan_add
$lang['start_time_group_not_modified'] = '拼团开始时间不可修改';
$lang['end_time_group_not_modifiable'] = '拼团结束时间不可修改';
$lang['mall_price'] = '商城价';
$lang['select_goods'] = '选择商品';
$lang['search_goods_step1'] = '第一步：搜索店内商品';
$lang['group_information1'] = '不输入名称直接搜索将显示店内所有普通商品，特殊商品不能参加。';
$lang['group_information2'] = '拼团生效后该商品的所有规格SKU都将执行统一的拼团折扣';
$lang['group_discount'] = '成团折扣';
$lang['group_discount_notice'] = '当未达到参团人数时，用户发起的此次拼团将失败，已支付的金额将自动退回给会员';
$lang['group_size_notice'] = '当未达到参团人数时，用户发起的此次拼团将失败，已支付的金额将自动退回给会员';
$lang['group_length'] = '成团时限';
$lang['group_length_notice'] = '开团成功后，团长发起的组团有效时间';
$lang['purchase_restriction'] = '购买限制';
$lang['purchase_restriction_notice'] = '活动中每人最多的购买数';
$lang['discount_cannot_empty'] = '折扣不能为空';
$lang['discount_must_figures'] = '折扣必须为数字';
$lang['please_fill_figure'] = '请填写1~9之间的数字';
$lang['group_must_not_empty'] = '成团人数不能为空';
$lang['number_groups_must_number'] = '成团人数必须为数字';
$lang['number_groups_cannot_less_than'] = '成团人数不能小于2';

//search_goods
$lang['select_group_goods'] = '选择为拼团商品';

//controller
$lang['add_group_activities'] = '添加拼团活动，活动名称：';
$lang['edit_group_activities'] = '编辑拼团活动，活动名称：';
$lang['activity_number'] = '，活动编号：';
$lang['goods_are_syndicate'] = '此商品在拼团中';
return $lang;