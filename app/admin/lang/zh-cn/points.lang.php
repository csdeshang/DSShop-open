<?php
/**
 * 积分功能公用
 */
$lang['admin_points_userrecord_error'] = '会员信息错误';
$lang['admin_points_membername'] = '会员名称';
$lang['admin_points_operatetype'] = '增减类型';
$lang['admin_points_operatetype_add'] = '增加';
$lang['admin_points_operatetype_reduce'] = '减少';
$lang['admin_points_pointsnum'] = '积分值';
$lang['admin_points_pointsdesc'] = '描述';
$lang['admin_points_pointsdesc_notice'] = '描述信息将显示在积分明细相关页，会员和管理员都可见';

/**
 * 积分添加
 */
$lang['admin_points_member_error_again'] = '会员信息错误，请重新填写会员名';
$lang['admin_points_points_null_error'] = '请添加积分值';
$lang['admin_points_points_min_error'] = '积分值必须大于0';
$lang['admin_points_points_short_error'] = '积分不足，会员当前积分数为';
$lang['admin_points_addmembername_error'] = '请输入会员名';
$lang['admin_points_member_tip_2'] = ', 当前积分数为';

/**
 * 积分日志
 */
$lang['admin_points_adminname'] = '管理员名称';
$lang['admin_points_stage'] = '操作阶段';
$lang['admin_points_stage_regist'] = '注册';
$lang['admin_points_stage_login'] = '登录';
$lang['admin_points_stage_comments'] = '商品评论';
$lang['admin_points_stage_order'] = '订单消费';
$lang['admin_points_stage_system'] = '系统调整';
$lang['admin_points_stage_rebate'] = '推荐返利';
$lang['admin_points_stage_pointorder'] = '礼品兑换';
$lang['admin_points_stage_app'] = '积分兑换';
$lang['admin_points_stage_signin'] = '签到';
$lang['admin_points_stage_inviter'] = '推荐注册';
$lang['admin_points_stage_marketmanage'] = '营销活动';
$lang['admin_points_addtime'] = '添加时间';


$lang['points_ruletip'] = '积分规则如下';
$lang['points_item'] = '项目';
$lang['points_number'] = '赠送积分';
$lang['points_number_reg'] = '会员注册';
$lang['points_number_login'] = '会员每天登录';
$lang['points_number_comments'] = '订单商品评论';
$lang['points_number_order'] = '购物并付款';
$lang['points_number_orderrate'] = '消费额与赠送积分比例';
$lang['points_number_orderrate_tip'] = '例:设置为10，表明消费10单位货币赠送1积分，建议设置0-100之间';
$lang['points_number_ordermax'] = '每订单最多赠送积分';
$lang['points_number_ordermax_tip'] = '例:设置为100，表明每订单赠送积分最多为100积分';
return $lang;