<?php

namespace app\api\controller\tblGoods\validate;

use app\deshang\base\BaseValidate;

class TblGoodsValidate extends BaseValidate
{
    protected $rule = [
        'platform' => 'checkPlatform',
        'limit' => 'integer|between:1,100',
        'category_id' => 'integer|egt:0',
        'goods_name' => 'max:30',
        'is_flashsale_goods' => 'in:0,1',
        'is_wholesale_goods' => 'in:0,1',
        'is_userdiscount_goods' => 'in:0,1',
        'is_distributor_goods' => 'in:0,1',
        'min_price' => 'float|egt:0',
        'max_price' => 'float|egt:0',
        'min_goods_avg_score' => 'float|between:0,5',
        'max_goods_avg_score' => 'float|between:0,5',
        'sort_field' => 'in:sales_num,avg_goods_score,create_at,click_num,goods_minprice',
        'sort_type' => 'in:asc,desc',
        'page_current' => 'integer|egt:1',
        'page_size' => 'integer|between:1,100',
    ];

    protected $message = [
        'platform.checkPlatform' => '平台标识不正确',
        'limit.integer' => '数量限制必须为整数',
        'limit.between' => '数量限制必须在1-100之间',
        'category_id.integer' => '系统分类ID必须为整数',
        'category_id.egt' => '系统分类ID不能小于0',
        'goods_name.max' => '商品名称不能超过30个字符',
        'is_flashsale_goods.in' => '是否限时促销值不正确',
        'is_wholesale_goods.in' => '是否批发值不正确',
        'is_userdiscount_goods.in' => '是否会员价值不正确',
        'is_distributor_goods.in' => '是否分销商品值不正确',
        'min_price.float' => '最低价格格式不正确',
        'min_price.egt' => '最低价格不能小于0',
        'max_price.float' => '最高价格格式不正确',
        'max_price.egt' => '最高价格不能小于0',
        'min_goods_avg_score.float' => '最低评分格式不正确',
        'min_goods_avg_score.between' => '最低评分需在0-5之间',
        'max_goods_avg_score.float' => '最高评分格式不正确',
        'max_goods_avg_score.between' => '最高评分需在0-5之间',
        'sort_field.in' => '排序字段只能是：sales_num,avg_goods_score,create_at,click_num,goods_minprice',
        'sort_type.in' => '排序类型只能是：asc,desc',
        'page_current.integer' => '当前页码必须为整数',
        'page_current.egt' => '当前页码必须大于等于1',
        'page_size.integer' => '每页数量必须为整数',
        'page_size.between' => '每页数量必须在1-100之间',
    ];

    protected $scene = [
        'list' => ['platform', 'limit', 'category_id', 'sort_field', 'sort_type'],
        'pages' => ['platform', 'category_id', 'goods_name', 'is_flashsale_goods', 'is_wholesale_goods', 'is_userdiscount_goods', 'is_distributor_goods', 'min_price', 'max_price', 'min_goods_avg_score', 'max_goods_avg_score', 'sort_field', 'sort_type', 'page_current', 'page_size'],
        'random' => ['platform', 'limit'],
    ];
} 