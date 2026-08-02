<?php

namespace app\adminapi\controller\tblGoods;

use app\deshang\base\controller\BaseAdminController;
use app\adminapi\service\goods\TblGoodsUserdiscountService;

/**
 * @OA\Tag(name="admin-api/tblGoods/TblGoodsUserdiscount", description="商品平台会员折扣管理接口")
 */
class TblGoodsUserdiscount extends BaseAdminController
{
    /**
     * 获取商品平台会员折扣信息
     * @OA\Get(
     *     path="/adminapi/tbl-goods/userdiscounts/{goods_id}",
     *     tags={"admin-api/tblGoods/TblGoodsUserdiscount"},
     *     summary="获取商品平台会员折扣信息",
     *     description="获取指定商品的平台会员折扣设置信息",
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
    public function getTblGoodsUserdiscountInfo($goods_id)
    {
        $this->validate(['goods_id' => $goods_id], 'app\adminapi\controller\tblGoods\validate\TblGoodsUserdiscountValidate.info');

        $result = (new TblGoodsUserdiscountService())->getTblGoodsUserdiscountInfo($goods_id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 更新商品平台会员折扣
     * @OA\Put(
     *     path="/adminapi/tbl-goods/userdiscounts/{goods_id}",
     *     tags={"admin-api/tblGoods/TblGoodsUserdiscount"},
     *     summary="更新商品平台会员折扣",
     *     description="设置或更新商品的平台会员折扣配置",
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
     *             @OA\Property(property="is_userdiscount_goods", type="integer", description="是否平台会员折扣商品 0否 1是", example=1),
     *             @OA\Property(property="sku_userdiscount_list", type="array", description="SKU会员折扣设置列表", @OA\Items(type="object"))
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
    public function updateTblGoodsUserdiscount($goods_id)
    {
        $data = array(
            'start_time' => input('param.start_time'),
            'end_time' => input('param.end_time'),
            'is_userdiscount_goods' => input('param.is_userdiscount_goods'),
            'sku_userdiscount_list' => input('param.sku_userdiscount_list/a'),
        );

        $this->validate($data, 'app\adminapi\controller\tblGoods\validate\TblGoodsUserdiscountValidate.update');

        $result = (new TblGoodsUserdiscountService())->updateTblGoodsUserdiscount((int)$goods_id, $data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 删除商品平台会员折扣
     * @OA\Delete(
     *     path="/adminapi/tbl-goods/userdiscounts/{goods_id}",
     *     tags={"admin-api/tblGoods/TblGoodsUserdiscount"},
     *     summary="删除商品平台会员折扣",
     *     description="删除指定商品的平台会员折扣设置",
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
    public function deleteTblGoodsUserdiscount($goods_id)
    {
        $this->validate(['goods_id' => $goods_id], 'app\adminapi\controller\tblGoods\validate\TblGoodsUserdiscountValidate.delete');

        $result = (new TblGoodsUserdiscountService())->deleteTblGoodsUserdiscount($goods_id);
        return ds_json_success('操作成功', $result);
    }
}
