<?php

namespace app\adminapi\controller\tblGoods;

use app\deshang\base\controller\BaseAdminController;
use app\adminapi\service\goods\TblGoodsFlashsaleService;

/**
 * @OA\Tag(name="admin-api/tblGoods/TblGoodsFlashsale", description="商品限时折扣管理接口")
 */
class TblGoodsFlashsale extends BaseAdminController
{
    /**
     * 获取商品限时折扣信息
     * @OA\Get(
     *     path="/adminapi/tbl-goods/flashsales/{goods_id}",
     *     tags={"admin-api/tblGoods/TblGoodsFlashsale"},
     *     summary="获取商品限时折扣信息",
     *     description="获取指定商品的限时折扣设置信息",
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
    public function getTblGoodsFlashsaleInfo($goods_id)
    {
        $this->validate(['goods_id' => $goods_id], 'app\adminapi\controller\tblGoods\validate\TblGoodsFlashsaleValidate.info');

        $result = (new TblGoodsFlashsaleService())->getTblGoodsFlashsaleInfo($goods_id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 更新商品限时折扣设置
     * @OA\Put(
     *     path="/adminapi/tbl-goods/flashsales/{goods_id}",
     *     tags={"admin-api/tblGoods/TblGoodsFlashsale"},
     *     summary="更新商品限时折扣设置",
     *     description="设置或更新商品的限时折扣配置",
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
     *             @OA\Property(property="is_flashsale_goods", type="integer", description="是否限时折扣商品 0否 1是", example=1),
     *             @OA\Property(property="sku_flashsale_list", type="array", description="SKU限时折扣设置列表", @OA\Items(type="object"))
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
    public function updateTblGoodsFlashsale($goods_id)
    {
        $data = array(
            'start_time' => input('param.start_time'),
            'end_time' => input('param.end_time'),
            'is_flashsale_goods' => input('param.is_flashsale_goods'),
            'sku_flashsale_list' => input('param.sku_flashsale_list/a'),
        );

        $this->validate($data, 'app\adminapi\controller\tblGoods\validate\TblGoodsFlashsaleValidate.update');

        $result = (new TblGoodsFlashsaleService())->updateTblGoodsFlashsale((int)$goods_id, $data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 删除商品限时折扣
     * @OA\Delete(
     *     path="/adminapi/tbl-goods/flashsales/{goods_id}",
     *     tags={"admin-api/tblGoods/TblGoodsFlashsale"},
     *     summary="删除商品限时折扣",
     *     description="删除指定商品的限时折扣设置",
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
    public function deleteTblGoodsFlashsale($goods_id)
    {
        $this->validate(['goods_id' => $goods_id], 'app\adminapi\controller\tblGoods\validate\TblGoodsFlashsaleValidate.delete');

        $result = (new TblGoodsFlashsaleService())->deleteTblGoodsFlashsale((int)$goods_id);
        return ds_json_success('操作成功', $result);
    }
}
