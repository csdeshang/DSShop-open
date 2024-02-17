<?php


$lang['limit_admin']			= '管理员';
$lang['limit_gadmin']			= '权限组';
$lang['admin_add_limit_admin']			= '添加管理员';
$lang['admin_add_limit_gadmin']			= '添加权限组';
/**
 * 管理员列表
 */
$lang['admin_index_not_allow_del']	= '该账号为系统管理员,不得删除';
$lang['admin_index_login_null']		= '此管理员未登录过';
$lang['admin_index_username']		= '登录名';
$lang['admin_index_password']		= '密码';
$lang['admin_rpassword']			= '确认密码';
$lang['admin_index_truename']		= '真实姓名';
$lang['admin_index_email']			= '电子邮件';
$lang['admin_index_im']				= '即时通讯';
$lang['admin_index_last_login']		= '上次登录';
$lang['admin_index_login_times']	= '登录次数';
$lang['admin_index_sys_admin']		= '系统管理员';
$lang['admin_index_del_admin']		= '删除';
$lang['admin_index_sys_admin_no']	= '超级管理员不可编辑';
/**
 * 管理员添加
 */
$lang['admin_add_admin_not_exists']		= '该名称已存在';
$lang['admin_add_username_tip']			= '请输入登录名';
$lang['admin_add_password_tip']			= '请输入密码';
$lang['admin_add_gid_tip']				= '请选择一个权限组，如果还未设置，请马上设置';
$lang['admin_add_username_null']		= '登录名不能为空';
$lang['admin_add_username_max']			= '登录名长度为3-20';
$lang['admin_add_password_null']		= '密码不能为空';
$lang['admin_add_gid_null']				= '请选择一个权限组';
$lang['admin_add_password_max']			= '密码长度为6-20';
/**
 * 管理权限设置
 */
$lang['admin_set_admin_not_exists']		= '此管理员不存在';
$lang['admin_set_limt']					= '设置权限';
/**
 * 管理员修改
 */
$lang['admin_edit_success']				= '更新成功';
$lang['admin_edit_fail']				= '更新失败';
$lang['admin_edit_repeat_error']		= '两次输入的密码不一致，请重新输入';
$lang['admin_edit_admin_error']			= '管理员信息错误';
$lang['admin_edit_admin_pw']			= '密码';
$lang['admin_edit_admin_pw2']			= '确认密码';
$lang['admin_edit_pwd_tip1']			= '不修改留空即可';


$lang['gadmin_name']				= '权限组';
$lang['gadmin_del_confirm']				= '删除该组同时会清除该组内成员的所有权限，确认删除吗?';
return $lang;


?>
