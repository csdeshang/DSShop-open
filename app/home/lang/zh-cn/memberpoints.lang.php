<?php

/**
 * 积分功能公用
 */
$lang['points_unavailable'] = '系统未开启积分功能';
$lang['points_membername'] = '会员名称';
$lang['points_pointsnum'] = '积分变更';
$lang['points_pointsdesc'] = '描述';
/**
 * 积分日志
 */
$lang['points_log_title'] = '积分日志';
$lang['points_stage_regist'] = '注册';
$lang['points_stage_login'] = '登录';
$lang['points_stage_comments'] = '商品评论';
$lang['points_stage_order'] = '订单消费';
$lang['points_stage_system'] = '积分管理';
$lang['points_stage_rebate'] = '推荐返利';
$lang['points_stage_pointorder'] = '礼品兑换';
$lang['points_stage_app'] = '积分兑换';
$lang['points_stage_signin'] = '签到';
$lang['points_stage_inviter'] = '邀请注册';
$lang['points_stage_marketmanage'] = '营销活动';
$lang['points_addtime'] = '添加时间';
$lang['points_log_pointscount'] = '积分总数：';

/**
 * 积分礼品功能公用
 */
$lang['member_pointorder_unavailable'] = '系统未开启积分或者积分兑换功能';
$lang['member_pointorder_record_error'] = '记录信息错误';
$lang['member_pointorder_list_title'] = '兑换记录';
$lang['member_pointorder_info_title'] = '兑换详细';
$lang['member_pointorder_ordersn'] = '兑换单号';
$lang['member_pointorder_shippingfee'] = '运费（元）';
$lang['member_pointorder_exchangepoints_shippingfee'] = '合计（积分）';
$lang['member_pointorder_orderstate_handle'] = '交易操作';
$lang['member_pointorder_addtime'] = '兑换时间';
$lang['member_pointorder_shipping_code'] = '物流单号';
$lang['member_pointorder_shipping_time'] = '发货时间';
$lang['member_pointorder_gobacklist'] = '返回列表';
/**
 * 兑换信息状态
 */
$lang['member_pointorder_state_submit'] = '已提交';
$lang['member_pointorder_state_waitpay'] = '待付款';
$lang['member_pointorder_state_canceled'] = '已取消';
$lang['member_pointorder_state_paid'] = '已付款';
$lang['member_pointorder_state_confirmpay'] = '待确认';
$lang['member_pointorder_state_confirmpaid'] = '确认收款';
$lang['member_pointorder_state_waitship'] = '待发货';
$lang['member_pointorder_state_shipped'] = '已发货';
$lang['member_pointorder_state_waitreceiving'] = '待收货';
$lang['member_pointorder_state_finished'] = '已完成';
$lang['member_pointorder_state_unknown'] = '未知';
/**
 * 兑换信息列表
 */
$lang['member_pointorder_cancel_success'] = '取消兑换成功';
$lang['member_pointorder_cancel_fail'] = '取消兑换失败';
$lang['member_pointorder_confirmreceiving_success'] = '确认收货成功';
$lang['member_pointorder_confirmreceiving_fail'] = '确认收货失败';
$lang['member_pointorder_pay'] = '付款';
$lang['member_pointorder_confirmreceiving'] = '确认收货';
$lang['member_pointorder_confirmreceivingtip'] = '确认已经收到兑换礼品吗?';
$lang['member_pointorder_cancel_title'] = '取消兑换';
$lang['member_pointorder_cancel_confirmtip'] = '确认取消兑换信息?';
$lang['member_pointorder_viewinfo'] = '查看详细';
/**
 * 兑换信息详细
 */
$lang['member_pointorder_info_ordersimple'] = '兑换信息';
$lang['member_pointorder_info_memberinfo'] = '会员信息';
$lang['member_pointorder_info_membername'] = '会员名称';
$lang['member_pointorder_info_memberemail'] = 'Email';
$lang['member_pointorder_info_ordermessage'] = '买家留言';
$lang['member_pointorder_info_paymentinfo'] = '支付信息';
$lang['member_pointorder_info_paymenttime'] = '支付时间';
$lang['member_pointorder_info_paymentmessage'] = '支付留言';
$lang['member_pointorder_info_shipinfo'] = '收货地址';
$lang['member_pointorder_info_shipinfo_truename'] = '收货人';
$lang['member_pointorder_info_shipinfo_areainfo'] = '所在地区';
$lang['member_pointorder_info_shipinfo_zipcode'] = '邮政编码';
$lang['member_pointorder_info_shipinfo_telphone'] = '电话号码';
$lang['member_pointorder_info_shipinfo_mobphone'] = '手机号码';
$lang['member_pointorder_info_shipinfo_description'] = '发货描述';
$lang['member_pointorder_info_prodinfo'] = '礼品信息';
$lang['member_pointorder_info_prodinfo_exnum'] = '兑换数量';

//index
$lang['rules_integral_acquisition'] = '积分获得规则';
$lang['rule_description1'] = '成功注册会员：增加';
$lang['rule_description2'] = '积分；会员每天登录：增加';
$lang['rule_description3'] = '积分；评价完成订单：增加';
$lang['rule_description4'] = '购物并付款成功后将获得订单总价';
$lang['rule_description5'] = '最高限额不超过';
$lang['rule_description6'] = '如订单发生退款、退货等问题时，积分将不予退还。';

//memberpointorder - index
$lang['transaction_status'] = '交易状态';

//memberpointorder_info
$lang['exchange_order_status'] = '兑换订单状态';
$lang['exchange_order_been_canceled'] = '已取消了该兑换订单';
$lang['look_other_exchange_gifts'] = '马上去看看其他兑换礼品';
$lang['submit_conversion'] = '提交兑换';
$lang['gift_delivery'] = '礼品发货';
$lang['exchange_card_operation'] = '兑换单操作';
$lang['logistics_company'] = '物流公司';
$lang['logistics_tracking'] = '物流跟踪';
$lang['exchange_form_required'] = '兑换单所需';
$lang['platform_delivered'] = '平台已发货';


//membersecurity
//auth
$lang['operating_hints'] = '操作提示';
$lang['binding_information1'] = '1. 请选择“绑定邮箱”或“绑定手机”方式其一作为安全校验码的获取方式并正确输入。';
$lang['binding_information2'] = '2. 如果您的邮箱已失效，可以 ';
$lang['binding_information3'] = '绑定手机';
$lang['binding_information4'] = '后通过接收手机短信完成验证。';
$lang['binding_information5'] = '3. 如果您的手机已失效，可以';
$lang['binding_information6'] = '绑定邮箱';
$lang['binding_information7'] = '后通过接收邮件完成验证。';
$lang['binding_information8'] = '4. 请正确输入下方图形验证码，如看不清可点击图片进行更换，输入完成后进行下一步操作。';
$lang['binding_information9'] = '5. 收到安全验证码后，请在30分钟内完成验证。';
$lang['select_authentication_method'] = '选择身份认证方式';
$lang['binding_validation_information1'] = '正在';
$lang['binding_validation_information2'] = '秒后再次';
$lang['binding_validation_information3'] = '获取安全验证码';
$lang['binding_validation_information4'] = '“安全验证码”已发出，请注意查收，请在';
$lang['binding_validation_information5'] = '“30分种”';
$lang['binding_validation_information6'] = '内完成验证。';
$lang['binding_validation_information7'] = '请输入安全验证码';
$lang['binding_validation_information8'] = '确认，进入下一步';
$lang['input_verification_code'] = '请正确输入验证码';
$lang['enter_graphic_verification_code'] = '请正确输入图形验证码';
$lang['predeposit_withdraw_min']             = '最低提现金额';
$lang['predeposit_withdraw_max']             = '最高提现金额';
//index
$lang['your_account_information'] = '您的账户信息';
$lang['login_account'] = '登录账号';
$lang['bind_mailbox'] = '绑定邮箱';
$lang['mobile_phone_number'] = '手机号码';
$lang['last_login'] = '上次登录';
$lang['ip_address'] = 'IP地址:';
$lang['prompt_message1'] = '（不是您登录的？请立即';
$lang['change_password'] = '更改密码';
$lang['your_security_service'] = '您的安全服务';
$lang['current_security_level'] = '当前安全等级';
$lang['low'] = '低';
$lang['prompt_message2'] = '(建议您开启全部安全设置，以保障账户及资金安全)';
$lang['medium'] = '中';
$lang['high'] = '高';
$lang['prompt_message3'] = '(您目前账户运行很安全)';
$lang['login_password'] = '登录密码';
$lang['set'] = '已设置';
$lang['prompt_message4'] = '安全性高的密码可以使账号更安全。建议您定期更换密码，且设置一个包含数字和字母，并长度超过6位以上的密码，为保证您的账户安全，只有在您绑定邮箱或手机后才可以修改密码。';
$lang['change_password'] = '修改密码';
$lang['mailbox_binding'] = '邮箱绑定';
$lang['bound'] = '已绑定';
$lang['unbound'] = '未绑定';
$lang['verify_prompt_message1'] = '进行邮箱验证后，可用于接收敏感操作的身份验证信息，以及订阅更优惠商品的促销邮件。';
$lang['change_email'] = '修改邮箱';
$lang['change_phone'] = '修改手机';
$lang['mobile_phone_binding'] = '手机绑定';
$lang['verify_prompt_message2'] = '进行手机验证后，可用于接收敏感操作的身份验证信息，以及进行积分消费的验证确认，非常有助于保护您的账号和账户财产安全。';
$lang['payment_password'] = '支付密码';
$lang['not_set'] = '未设置';
$lang['verify_prompt_message3'] = '设置支付密码后，在使用账户中余额时，需输入支付密码。';
$lang['set_password'] = '设置密码';

//modify_email
$lang['verify_email_prompt1'] = '1. 此邮箱将接收密码找回，订单通知等敏感性安全服务及提醒使用，请务必填写正确地址。';
$lang['verify_email_prompt2'] = '2. 设置提交后，系统将自动发送验证邮件到您绑定的信箱，您需在24小时内登录邮箱并完成验证。';
$lang['verify_email_prompt3'] = '3. 更改邮箱时，请通过安全验证后重新输入新邮箱地址绑定。';
$lang['bind_mailbox_address'] = '绑定邮箱地址';
$lang['send_verification_mail'] = '发送验证邮件';
$lang['please_fill_mailbox_correctly'] = '请正确填写邮箱';

//modify_mobile
$lang['verify_mobile_prompt1'] = '1. 绑定手机后可直接通过短信接受安全验证等重要操作。';
$lang['verify_mobile_prompt2'] = '2. 更改手机时，请通过安全验证后重新输入新手机号码绑定。';
$lang['verify_mobile_prompt3'] = '3. 收到安全验证码后，请在30分钟内完成验证。';
$lang['bind_mobile_phone_number'] = '绑定手机号码';
$lang['immediately_binding'] = '立即绑定';
$lang['enter_mobile_phone_number'] = '请输入手机号码';
$lang['input_mobile_verification_code'] = '请正确输入手机校验码';
$lang['input_mobile_verification_number'] = '请正确输入手机号码';

//modify_paypwd
$lang['payment_password_instructions1'] = '1. “支付密码”用于结算订单时使用<em>“账户余额”</em>时的密码输入，请牢记并确保安全。';
$lang['payment_password_instructions2'] = '2. 如修改或找回“支付密码”，请完成安全验证操作后重新提交。';
$lang['set_payment_password'] = '设置支付密码';
$lang['payment_password_instructions3'] = '6-20位字符，可由英文、数字及标点符号组成。';
$lang['confirm_payment_password'] = '确认支付密码';
$lang['please_enter_password_correctly'] = '请正确输入密码';
$lang['two_password_inconsistencies'] = '两次密码输入不一致';

//modify_pwd
$lang['set_new_password'] = '设置新密码';
$lang['confirm_new_password'] = '确认新密码';


//controller
$lang['mailbox_has_been_used'] = '该邮箱已被使用';
$lang['system_error'] = '系统发生错误，如有疑问请与管理员联系';
$lang['verify_mail_been_sent_mailbox'] = '验证邮件已经发送至您的邮箱，请于24小时内登录邮箱并完成验证';
$lang['please_bind_email_phone_first'] = '请先绑定手机';
$lang['validation_fails'] = '验证失败';
$lang['please_retrieve_verification_code'] = '验证码已被使用或超时，请重新获取验证码';
$lang['verification_code_has_been_sent'] = '验证码已发出，请注意查收';
$lang['operation_timed_out'] = '操作超时，请重新获得验证码';
$lang['password_modify_successfully'] = '密码修改成功';
$lang['password_set_successfully'] = '密码设置成功';
$lang['password_setting_failed'] = '密码设置失败';
$lang['fill_your_phone_number_correctly'] = '请正确填写手机号';
$lang['fill_phone_verification_code_correctly'] = '请正确填写手机验证码';
$lang['mobile_verification_code_error'] = '手机验证码错误，请重新输入';
$lang['phone_verification_code_expired'] = '手机验证码已过期，请重新获取验证码';
$lang['phone_number_bound_successfully'] = '手机号绑定成功';
$lang['please_change_another_phone_number'] = '该手机号已被使用，请更换其它手机号';
$lang['modified_phone_same_original_one'] = '修改的手机与原手机相同，如有疑问请与管理员联系';
$lang['error_occurred_system_update_information'] = '系统更新信息发生错误，如有疑问请与管理员联系';
$lang['send_success'] = '发送成功';
$lang['account_security'] = '账户安全';
$lang['change_login_password'] = '修改登录密码';
$lang['email_address_verification'] = '邮箱验证';
$lang['phone_verification'] = '手机验证';
$lang['account_balance'] = '账户余额';
$lang['top_up_detail'] = '充值明细';
$lang['balance_withdrawal'] = '余额提现';
$lang['withdrawal_application'] = '提现申请';
$lang['verification_code_sending_failed'] = '验证码发送失败';

return $lang;