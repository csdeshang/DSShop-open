<?php

namespace app\adminapi\controller\tblGoods;

use app\deshang\base\controller\BaseAdminController;
use app\adminapi\service\goods\TblGoodsWholesaleService;

/**
 * @OA\Tag(name="admin-api/tblGoods/TblGoodsWholesale", description="商品批发管理接口")
 */
class TblGoodsWholesale extends BaseAdminController
{
    /**
     * 获取商品批发信息
     * @OA\Get(
     *     path="/adminapi/tbl-goods/wholesales/{goods_id}",
     *     tags={"admin-api/tblGoods/TblGoodsWholesale"},
     *     summary="获取商品批发信息",
     *     description="获取指定商品的批发设置信息",
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
    public function getTblGoodsWholesaleInfo($goods_id)
    {
        $this->validate(['goods_id' => $goods_id], 'app\adminapi\controller\tblGoods\validate\TblGoodsWholesaleValidate.info');

        $result = (new TblGoodsWholesaleService())->getTblGoodsWholesaleInfo($goods_id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 更新商品批发设置
     * @OA\Put(
     *     path="/adminapi/tbl-goods/wholesales/{goods_id}",
     *     tags={"admin-api/tblGoods/TblGoodsWholesale"},
     *     summary="更新商品批发设置",
     *     description="设置或更新商品的批发配置",
     *     @OA\Parameter(
     *         name="goods_id",
     *         in="path",
     *         required=true,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="start_time", type="string", description="开始时间", example="2024-01-01 00:00:00"),
     *             @OA\Property(property="end_time", type="string", description="结束时间", example="2024-12-31 23:59:59"),
     *             @OA\Property(property="is_wholesale_goods", type="integer", description="是否批发商品 0否 1是", example=1),
     *             @OA\Property(property="sku_wholesale_list", type="array", description="SKU批发设置列表", @OA\Items(type="object"))
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
    public function updateTblGoodsWholesale($goods_id)
    {
        $data = array(
            'start_time' => input('param.start_time'),
            'end_time' => input('param.end_time'),
            'is_wholesale_goods' => input('param.is_wholesale_goods'),
            'sku_wholesale_list' => input('param.sku_wholesale_list/a'),
        );

        $this->validate($data, 'app\adminapi\controller\tblGoods\validate\TblGoodsWholesaleValidate.update');

        $result = (new TblGoodsWholesaleService())->updateTblGoodsWholesale((int)$goods_id, $data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 删除商品批发设置
     * @OA\Delete(
     *     path="/adminapi/tbl-goods/wholesales/{goods_id}",
     *     tags={"admin-api/tblGoods/TblGoodsWholesale"},
     *     summary="删除商品批发设置",
     *     description="删除指定商品的批发设置",
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
    public function deleteTblGoodsWholesale($goods_id)
    {
        $this->validate(['goods_id' => $goods_id], 'app\adminapi\controller\tblGoods\validate\TblGoodsWholesaleValidate.delete');

        $result = (new TblGoodsWholesaleService())->deleteTblGoodsWholesale($goods_id);
        return ds_json_success('操作成功', $result);
    }
}
