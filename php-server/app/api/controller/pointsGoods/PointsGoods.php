<?php

namespace app\api\controller\pointsGoods;
use app\deshang\base\controller\BaseApiController;

use app\api\service\pointsGoods\PointsGoodsService;

/**
 * @OA\Tag(name="api/pointsGoods/PointsGoods", description="积分商品信息接口")
 */
class PointsGoods extends BaseApiController{

    /**
     * 获取积分商品列表
     * @OA\Get(
     *     path="/api/points-goods/goods/list",
     *     tags={"api/pointsGoods/PointsGoods"},
     *     summary="获取积分商品列表",
     *     description="获取积分商品列表数据",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="数量限制，默认10",
     *         @OA\Schema(type="integer", default=10, example=10)
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         description="积分商品分类ID",
     *         @OA\Schema(type="integer", default=0, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="goods_status",
     *         in="query",
     *         required=false,
     *         description="商品状态 0:下架 1:上架",
     *         @OA\Schema(type="integer", default=1, example=1)
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
    public function getPointsGoodsList(){
        $data = array(
            'limit' => input('param.limit', 10),
            'category_id' => input('param.category_id', 0),
            'goods_status' => input('param.goods_status', 1),
        );

        $this->validate($data, 'app\api\controller\pointsGoods\validate\PointsGoodsValidate.list');

        $result = (new PointsGoodsService())->getPointsGoodsList($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取积分商品分页列表
     * @OA\Get(
     *     path="/api/points-goods/goods/pages",
     *     tags={"api/pointsGoods/PointsGoods"},
     *     summary="获取积分商品分页列表",
     *     description="获取积分商品分页数据",
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         description="积分商品分类ID",
     *         @OA\Schema(type="integer", default=0, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="goods_status",
     *         in="query",
     *         required=false,
     *         description="商品状态 0:下架 1:上架",
     *         @OA\Schema(type="integer", default=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="goods_name",
     *         in="query",
     *         required=false,
     *         description="商品名称",
     *         @OA\Schema(type="string", example="积分商品")
     *     ),
     *     @OA\Parameter(
     *         name="page_current",
     *         in="query",
     *         required=false,
     *         description="当前页码",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         required=false,
     *         description="每页数量",
     *         @OA\Schema(type="integer", example=10)
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
    public function getPointsGoodsPages(){
        $data = array(
            'category_id' => input('param.category_id', 0),
            'goods_status' => input('param.goods_status', 1),
            'goods_name' => input('param.goods_name', ''),
            'page_current' => input('param.page_current', 1),
            'page_size' => input('param.page_size', 10),
        );

        $this->validate($data, 'app\api\controller\pointsGoods\validate\PointsGoodsValidate.pages');

        $result = (new PointsGoodsService())->getPointsGoodsPages($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取积分商品详情
     * @OA\Get(
     *     path="/api/points-goods/goods/{id}",
     *     tags={"api/pointsGoods/PointsGoods"},
     *     summary="获取积分商品详情",
     *     description="根据ID获取积分商品详情信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="积分商品ID",
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
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="积分商品不存在"
     *     )
     * )
     */
    public function getPointsGoodsDetail($id){
        $data = array(
            'id' => (int)$id,
        );

        $this->validate($data, 'app\api\controller\pointsGoods\validate\PointsGoodsValidate.detail');

        $result = (new PointsGoodsService())->getPointsGoodsDetail((int)$id);
        if (empty($result)) {
            return ds_json_error('积分商品不存在');
        }
        return ds_json_success('操作成功', $result);
    }
}
