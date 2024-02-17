<?php
/**
 * 活动列表
 */
$lang['activity_index'] = '活动';
$lang['activity_index_content'] = '活动内容';
$lang['activity_index_manage'] = '活动管理';
$lang['activity_index_title'] = '活动标题';
$lang['activity_index_type'] = '活动类型';
$lang['activity_index_banner'] = '横幅图片';
$lang['activity_index_banner_mobile'] = '手机横幅图片';
$lang['activity_index_style'] = '使用样式';
$lang['activity_index_group'] = '抢购';
$lang['activity_index_default'] = '默认风格';
$lang['activity_index_long_time'] = '长期活动';
$lang['activity_index_help1'] = '平台发起活动的时候，店铺才可以申请参与活动';
$lang['activity_index_help2'] = '在网站下面的“导航管理”处可选择添加活动的导航';
$lang['activity_index_help3'] = '只有关闭或者过期的活动才能够被删除';
$lang['activity_index_help4'] = '活动列表排序，排序所属的数字越小该活动越靠前显示';
$lang['activity_index_periodofvalidity'] = '有效期';
/**
 * 添加活动
 */
$lang['activity_new_title_null'] = '活动标题不能为空';
$lang['activity_new_style_null'] = '必须选择页面风格';
$lang['activity_new_type_null'] = '必须选择活动类别';
$lang['activity_new_sort_tip'] = '排序必须是数字，范围0~255';
$lang['activity_new_end_date_too_early'] = '截止时间必须晚于开始时间';
$lang['activity_new_title_tip'] = '请为您的活动填写一个简明扼要的主题';
$lang['activity_new_type_tip'] = '请为您的活动选择一个类别';
$lang['activity_new_start_tip'] = '留空默认为活动立即开始';
$lang['activity_new_end_tip'] = '留空默认为活动永久进行';
$lang['activity_new_banner_tip'] = '支持jpg、jpeg、gif、png格式 , 图片大小 680px X 350px';
$lang['activity_new_banner_mobile_tip'] = '支持jpg、jpeg、gif、png格式 , 图片大小 750px X 240px';
$lang['activity_new_style'] = '页面风格';
$lang['activity_new_style_tip'] = '请选择该活动所在页面的风格样式';
$lang['activity_new_desc'] = '活动说明';
$lang['activity_new_sort_tip1'] = '数字范围为0~255，数字越小越靠前';
$lang['activity_new_sort_null'] = '排序不能为空';
$lang['activity_new_sort_minerror'] = '数字范围为0~255';
$lang['activity_new_sort_maxerror'] = '数字范围为0~255';
$lang['activity_new_sort_error'] = '排序为0~255的数字';
$lang['activity_new_banner_null'] = '横幅图片不能为空';
$lang['activity_new_ing_wrong'] = '图片限于png,gif,jpeg,jpg格式';
$lang['activity_new_startdate_null'] = '开始时间不能为空';
$lang['activity_new_enddate_null'] = '结束时间不能为空';
$lang['activity_not_exists'] = '该活动并不存在';
$lang['activity_time_end'] = '该活动已结束';


/**
 * 删除活动
 */
$lang['activity_del_choose_activity'] = '请选择活动';
/**
 * 活动内容
 */
$lang['activity_detail_index_passed'] = '已通过';
$lang['activity_detail_index_unpassed'] = '已拒绝';
$lang['activity_detail_index_apply_again'] = '再次申请';
$lang['activity_detail_index_pass_all'] = '您确定要通过已选信息吗?';
$lang['activity_detail_index_refuse_all'] = '您确定要拒绝已选信息吗?';
$lang['activity_detail_index_tip1'] = '申请商品在没有审核或者审核失败的时候可以删除';
$lang['activity_detail_index_tip2'] = '本页申请商品的显示规则是未审核先显示，排序越小越靠前显示';
$lang['activity_detail_index_tip3'] = '下架、违规下架商品或者所属店铺已经关闭的商品将不会在活动页面显示，请慎重审核';

/**
 * 活动内容删除
 */
$lang['activity_detail_del_choose_detail'] = '请选择活动内容(比如商品或抢购等)';


$lang['activity_submitted'] = "参与申请已提交";

//activity_apply
$lang['ds_activity_manage'] = '添加活动';
$lang['choose_products_event'] = '选择参加活动的商品';
$lang['search_product_names'] = '搜索商品名称';
return $lang;
