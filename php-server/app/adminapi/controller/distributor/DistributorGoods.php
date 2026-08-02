<?php

namespace app\adminapi\controller\distributor;

use app\adminapi\service\distributor\DistributorGoodsService;
use app\deshang\base\controller\BaseAdminController;

/**
 * @OA\Tag(name="admin-api/distributor/DistributorGoods", description="分销商品管理接口")
 */
class DistributorGoods extends BaseAdminController
{
    /**
     * 获取分销商品信息
     * @OA\Get(
     *     path="/adminapi/distributor/goods/{goods_id}",
     *     tags={"admin-api/distributor/DistributorGoods"},
     *     summary="获取分销商品信息",
     *     description="获取指定商品的分销设置信息",
     *     @OA\Parameter(
     *         name="goods_id",
     *         in="path",
     *         required=true,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getDistributorGoodsInfo($goods_id)
    {
        $this->validate(['goods_id' => (int)$goods_id], 'app\adminapi\controller\distributor\validate\DistributorGoodsValidate.info');

        $result = (new DistributorGoodsService())->getDistributorGoodsInfo((int)$goods_id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 更新分销商品设置
     * @OA\Put(
     *     path="/adminapi/distributor/goods/{goods_id}",
     *     tags={"admin-api/distributor/DistributorGoods"},
     *     summary="更新分销商品设置",
     *     description="设置商品的分销佣金和参数",
     *     @OA\Parameter(
     *         name="goods_id",
     *         in="path",
     *         required=true,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="请求数据",
     *         @OA\JsonContent(
     *             required={"distributor_goods_type"},
     *             @OA\Property(property="distributor_goods_type", type="integer", description="分销商品类型", example=1),
     *             @OA\Property(property="is_distributor_goods", type="integer", description="是否分销商品 0否 1是", example=1),
     *             @OA\Property(property="sku_distributor_list", type="array", description="SKU分销设置列表", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function updateDistributorGoods($goods_id)
    {
        $data = array(
            'distributor_goods_type' => input('param.distributor_goods_type'),
            'is_distributor_goods' => input('param.is_distributor_goods'),
            'sku_distributor_list' => input('param.sku_distributor_list/a'),
        );

        $this->validate(array_merge($data, ['goods_id' => (int)$goods_id]), 'app\adminapi\controller\distributor\validate\DistributorGoodsValidate.update');

        $result = (new DistributorGoodsService())->updateDistributorGoods((int)$goods_id, $data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 取消商品分销佣金
     * @OA\Delete(
     *     path="/adminapi/distributor/goods/{goods_id}",
     *     tags={"admin-api/distributor/DistributorGoods"},
     *     summary="取消商品分销佣金",
     *     description="取消指定商品的分销佣金设置",
     *     @OA\Parameter(
     *         name="goods_id",
     *         in="path",
     *         required=true,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function cancelDistributorGoods($goods_id)
    {
        $this->validate(['goods_id' => $goods_id], 'app\adminapi\controller\distributor\validate\DistributorGoodsValidate.cancel');

        $result = (new DistributorGoodsService())->cancelDistributorGoods($goods_id);
        return ds_json_success('操作成功', $result);
    }
}
