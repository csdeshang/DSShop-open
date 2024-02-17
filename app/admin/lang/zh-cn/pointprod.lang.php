<?php
/**
 * 积分礼品功能公用
 */
$lang['admin_pointprodp']	 		= '礼品';
$lang['admin_pointprod_record_error']			= '记录信息错误';
$lang['admin_pointprod_add_title']			= '新增礼品';
$lang['admin_pointprod_show_up']			= '上架';
$lang['admin_pointprod_show_down']			= '下架';
$lang['admin_pointprod_forbid']				= '禁售';
$lang['admin_pointprod_goods_name']			= '礼品名称';
$lang['pointprod_help1'] = '积分兑换功能需要先开启积分状态 （营销 -> 基本设置），礼品将会出现在积分中心页面，会员可以使用积分来兑换礼品，兑换成功后，由系统平台进行发货。';
$lang['admin_pointprod_goods_points']		= '兑换积分';
$lang['admin_pointprod_goods_price']		= '礼品原价';
$lang['admin_pointprod_goods_storage']		= '库存';
$lang['admin_pointprod_goods_view']			= '浏览';
$lang['admin_pointprod_salenum']			= '售出';
$lang['goods_album_all_photo'] = '全部图片';

/**
 * 添加
 */
$lang['admin_pointprod_baseinfo']		= '礼品基本信息';
$lang['admin_pointprod_goods_image']	= '礼品图片';
$lang['admin_pointprod_goods_tag']		= '礼品标签';
$lang['admin_pointprod_goods_serial']	= '礼品编号';
$lang['admin_pointprod_requireinfo']	= '兑换要求';
$lang['admin_pointprod_limittip']		= '限制每会员兑换数量';
$lang['admin_pointprod_limit_yes']		= '限制';
$lang['admin_pointprod_limit_no']		= '不限制';
$lang['admin_pointprod_limitnum']		= '每会员限兑数量';
$lang['admin_pointprod_freightcharge']	= '运费承担方式';
$lang['admin_pointprod_freightprice']	= '运费价格';
$lang['admin_pointprod_limittimetip']		= '限制兑换时间';
$lang['admin_pointprod_limittime_yes']		= '限制';
$lang['admin_pointprod_limittime_no']		= '不限制';
$lang['admin_pointprod_time_hour']	= '时';
$lang['admin_pointprod_stateinfo']	= '状态设置';
$lang['admin_pointprod_isshow']	= '是否上架';
$lang['admin_pointprod_iscommend']	= '是否推荐';
$lang['admin_pointprod_isforbid']	= '是否禁售';
$lang['admin_pointprod_forbidreason']= '禁售原因';
$lang['admin_pointprod_seoinfo']	= 'SEO设置';
$lang['admin_pointprod_seokey']		= '关键字';
$lang['admin_pointprod_otherinfo']		= '其他设置';
$lang['admin_pointprod_sort']		= '礼品排序';
$lang['admin_pointprod_sorttip']		= '注：数值越小排序越靠前';
$lang['admin_pointprod_seodescription']		= 'SEO描述';
$lang['admin_pointprod_descriptioninfo']	= '礼品描述';
$lang['admin_pointprod_uploadimg']	= '图片上传';
$lang['admin_pointprod_uploadimg_more']	= '批量上传';
$lang['admin_pointprod_uploadimg_common']	= '普通上传';
$lang['admin_pointprod_uploadimg_complete']	= '已传图片';
$lang['admin_pointprod_uploadimg_add']	= '插入';
$lang['admin_pointprod_uploadimg_addtoeditor']	= '插入编辑器';
$lang['admin_pointprod_add_goodsname_error']	= '请添加礼品名称';
$lang['admin_pointprod_add_goodsprice_null_error']	= '请添加礼品原价';
$lang['admin_pointprod_add_goodsprice_number_error']	= '礼品原价必须为数字且大于等于0';
$lang['admin_pointprod_add_goodspoint_null_error']	= '请添加兑换积分';
$lang['admin_pointprod_add_goodspoint_number_error']	= '兑换积分为整数且大于等于0';
$lang['admin_pointprod_add_goodsserial_null_error']	= '请添加礼品编号';
$lang['admin_pointprod_add_storage_null_error']	    = '请添加礼品库存';
$lang['admin_pointprod_add_storage_number_error']	= '礼品库存必须为整数且大于等于0';
$lang['admin_pointprod_add_limitnum_error']			= '请添加每会员限兑数量';
$lang['admin_pointprod_add_limitnum_digits_error']	= '会员限兑数量为整数且大于等于0';
$lang['admin_pointprod_add_freightprice_null_error']		= '请添加运费价格';
$lang['admin_pointprod_add_freightprice_number_error']		= '运费价格必须为数字且大于等于0';
$lang['admin_pointprod_add_limittime_null_error']		= '请添加开始时间和结束时间';
$lang['admin_pointprod_add_sort_null_error']		= '请添加礼品排序';
$lang['admin_pointprod_add_sort_number_error']		= '礼品排序为整数且大于等于0';
$lang['admin_pointprod_add_upload']		= '上传';
$lang['admin_pointprod_add_upload_img_error']		= '图片限于png,gif,jpeg,jpg格式';
$lang['admin_pointprod_add_iframe_uploadfail']		= '上传失败';
$lang['admin_pointprod_add_success']		= '礼品添加成功';
/**
 * 更新
 */
$lang['admin_pointprod_edit_success']		= '礼品更新成功';
$lang['admin_pointprod_edit_fail']		= '礼品更新失败';
$lang['admin_pointprod_edit_addtime']		= '添加时间';
$lang['admin_pointprod_edit_viewnum']		= '浏览次数';
$lang['admin_pointprod_edit_salenum']		= '售出数量';
return $lang;