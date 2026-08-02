<?php

namespace app\platform\house\app\api\controller\cart\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\goods\TblGoodsPromotionEnum;

class HouseCartValidate extends BaseValidate
{
    // 定义验证规则
    protected $rule = [
        'goods_id' => 'require|integer',
        'quantity' => 'require|integer|gt:0',
        'sku_id' => 'require|integer',
        'promotion_type' => 'checkPromotionType',
        'cart_id' => 'require|integer',
        'cart_ids' => 'require|array|isAllNumbers',
    ];

    // 定义错误信息
    protected $message = [
        'goods_id.require' => '商品ID不能为空',
        'goods_id.integer' => '商品ID必须为整数',
        'quantity.require' => '数量不能为空',
        'quantity.integer' => '数量必须为整数',
        'quantity.gt' => '数量必须大于0',
        'sku_id.require' => 'SKU ID不能为空',
        'sku_id.integer' => 'SKU ID必须为整数',
        'promotion_type.checkPromotionType' => '促销类型不合法',
        'cart_id.require' => '购物车ID不能为空',
        'cart_id.integer' => '购物车ID必须为整数',
        'cart_ids.require' => '购物车ID不能为空',
        'cart_ids.array' => '购物车ID必须为数组',
        'cart_ids.isAllNumbers' => '购物车ID必须为数字',
    ];

    // 定义场景
    protected $scene = [
        'add' => ['goods_id', 'quantity', 'sku_id', 'promotion_type'],
        'update' => ['cart_id', 'quantity'],
        'delete' => ['cart_ids'],
    ];

    protected function checkPromotionType($value, $rule, $data = [])
    {
        $promotion_type_dict = TblGoodsPromotionEnum::getPromotionTypeDict();
        if (!array_key_exists($value, $promotion_type_dict)) {
            return false;
        }
        return true;
    }
}
