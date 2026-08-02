<?php

namespace app\adminapi\controller\tblGoods\validate;

use app\deshang\base\BaseValidate;

class TblGoodsWholesaleValidate extends BaseValidate
{
    protected $rule = [
        'goods_id' => 'require|integer|gt:0',
        'start_time' => 'dateFormat:Y-m-d H:i:s',
        'end_time' => 'dateFormat:Y-m-d H:i:s',
        'is_wholesale_goods' => 'in:0,1',
        'sku_wholesale_list' => 'array',
    ];

    protected $message = [
        'goods_id.require' => '商品ID不能为空',
        'goods_id.integer' => '商品ID必须是整数',
        'goods_id.gt' => '商品ID必须大于0',
        'start_time.dateFormat' => '开始时间格式不正确',
        'end_time.dateFormat' => '结束时间格式不正确',
        'is_wholesale_goods.in' => '是否批发商品值不正确',
        'sku_wholesale_list.array' => 'SKU批发设置必须是数组',
    ];

    protected $scene = [
        'info' => ['goods_id'],
        'update' => ['start_time', 'end_time', 'is_wholesale_goods', 'sku_wholesale_list'],
        'delete' => ['goods_id'],
    ];
}
