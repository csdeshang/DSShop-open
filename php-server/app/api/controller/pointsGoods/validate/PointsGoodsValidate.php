<?php

namespace app\api\controller\pointsGoods\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\pointsGoods\PointsGoodsEnum;

class PointsGoodsValidate extends BaseValidate
{
    // 定义验证规则
    protected $rule = [
        'id' => 'require|integer|gt:0', // 积分商品ID，必填，必须是大于0的整数
        'limit' => 'integer|between:1,100', // 数量限制，必须是1-100之间的整数
        'category_id' => 'integer|egt:0', // 积分商品分类ID，必须是大于等于0的整数
        'goods_status' => 'checkGoodsStatus', // 商品状态，使用枚举验证
        'goods_name' => 'max:128', // 商品名称，最大长度128
        'page_current' => 'integer|egt:1', // 当前页码，必须是大于等于1的整数
        'page_size' => 'integer|between:1,100', // 每页数量，必须是1-100之间的整数
    ];

    // 定义验证提示
    protected $message = [
        'id.require' => '积分商品ID不能为空',
        'id.integer' => '积分商品ID必须是整数',
        'id.gt' => '积分商品ID必须大于0',
        'limit.integer' => '数量限制必须是整数',
        'limit.between' => '数量限制必须在1-100之间',
        'category_id.integer' => '积分商品分类ID必须是整数',
        'category_id.egt' => '积分商品分类ID必须大于等于0',
        'goods_status.checkGoodsStatus' => '商品状态值无效（0:下架 1:上架）',
        'goods_name.max' => '商品名称不能超过128个字符',
        'page_current.integer' => '当前页码必须是整数',
        'page_current.egt' => '当前页码必须大于等于1',
        'page_size.integer' => '每页数量必须是整数',
        'page_size.between' => '每页数量必须在1-100之间',
    ];

    // 定义场景
    protected $scene = [
        'list' => ['limit', 'category_id', 'goods_status'], // 列表场景
        'pages' => ['category_id', 'goods_status', 'goods_name', 'page_current', 'page_size'], // 分页场景
        'detail' => ['id'], // 详情场景
    ];

    // 验证商品状态
    public function checkGoodsStatus($value, $rule, $data)
    {
        if (empty($value)) {
            return true; // 空值允许
        }
        return array_key_exists($value, PointsGoodsEnum::getGoodsStatusDict());
    }
}
