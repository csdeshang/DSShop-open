<?php

namespace app\adminapi\controller\distributor\validate;

use app\common\enum\distributor\DistributorGoodsEnum;
use app\deshang\base\BaseValidate;

class DistributorGoodsValidate extends BaseValidate
{
    protected $rule = [
        'goods_id' => 'require|integer|gt:0',
        'distributor_goods_type' => 'checkDistributorGoodsType',
        'is_distributor_goods' => 'in:0,1',
        'sku_distributor_list' => 'array',
    ];

    protected $message = [
        'goods_id.require' => '商品ID不能为空',
        'goods_id.integer' => '商品ID必须是整数',
        'goods_id.gt' => '商品ID必须大于0',
        'distributor_goods_type.checkDistributorGoodsType' => '分销商品类型值不正确',
        'is_distributor_goods.in' => '是否分销商品值不正确',
        'sku_distributor_list.array' => 'SKU分销设置必须是数组',
    ];

    protected $scene = [
        'info' => ['goods_id'],
        'update' => ['goods_id', 'distributor_goods_type', 'is_distributor_goods', 'sku_distributor_list'],
        'cancel' => ['goods_id'],
    ];

    /**
     * 验证分销商品类型
     */
    public function checkDistributorGoodsType($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }
        return array_key_exists($value, DistributorGoodsEnum::getDistributorGoodsTypeDict());
    }
}
