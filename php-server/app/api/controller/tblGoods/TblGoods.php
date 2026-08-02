<?php

namespace app\api\controller\tblGoods;
use app\deshang\base\controller\BaseApiController;

use app\api\service\goods\TblGoodsService;

/**
 * @OA\Tag(name="api/tblGoods/TblGoods", description="商品信息接口")
 */
class TblGoods extends BaseApiController{

    /**
     * 获取商品列表
     * @OA\Get(
     *     path="/api/tbl-goods/goods/list",
     *     tags={"api/tblGoods/TblGoods"},
     *     summary="获取商品列表",
     *     description="获取商品列表数据",
     *     @OA\Parameter(
     *         name="platform",
     *         in="query",
     *         required=false,
     *         description="平台标识",
     *         @OA\Schema(type="string", example="mall")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="数量限制",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         description="系统分类ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="sort_field",
     *         in="query",
     *         required=false,
     *         description="排序字段：sales_num(销量), avg_goods_score(评分), create_at(创建时间), click_num(点击量), goods_minprice(价格)",
     *         @OA\Schema(type="string", example="sales_num")
     *     ),
     *     @OA\Parameter(
     *         name="sort_type",
     *         in="query",
     *         required=false,
     *         description="排序类型：asc(升序), desc(降序)",
     *         @OA\Schema(type="string", example="desc")
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
    public function getTblGoodsList(){
        $data = array(
            'platform' => input('param.platform', ''),
            'limit' => input('param.limit', 10),
            // 系统分类ID
            'category_id' => input('param.category_id', 0),
            'sort_field' => input('param.sort_field', 'sales_num'),
            'sort_type' => input('param.sort_type', 'desc'),
        );

        $this->validate($data, 'app\api\controller\tblGoods\validate\TblGoodsValidate.list');

        $result = (new TblGoodsService())->getTblGoodsList($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取商品分页列表
     * @OA\Get(
     *     path="/api/tbl-goods/goods/pages",
     *     tags={"api/tblGoods/TblGoods"},
     *     summary="获取商品分页列表",
     *     description="获取商品分页数据",
     *     @OA\Parameter(
     *         name="platform",
     *         in="query",
     *         required=false,
     *         description="平台标识",
     *         @OA\Schema(type="string", example="mall")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         description="系统分类ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="goods_name",
     *         in="query",
     *         required=false,
     *         description="商品名称",
     *         @OA\Schema(type="string", example="苹果手机")
     *     ),
     *     @OA\Parameter(
     *         name="sort_field",
     *         in="query",
     *         required=false,
     *         description="排序字段：sales_num(销量), avg_goods_score(评分), create_at(创建时间), click_num(点击量), goods_minprice(价格)",
     *         @OA\Schema(type="string", example="sales_num")
     *     ),
     *     @OA\Parameter(
     *         name="sort_type",
     *         in="query",
     *         required=false,
     *         description="排序类型：asc(升序), desc(降序)",
     *         @OA\Schema(type="string", example="desc")
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
    public function getTblGoodsPages(){
        $data = array(
            'platform' => input('param.platform', ''),
            // 系统分类ID
            'category_id' => input('param.category_id', 0),
            // 商品名称
            'goods_name' => input('param.goods_name', ''),
            // 是否限时促销
            'is_flashsale_goods' => input('param.is_flashsale_goods', ''),
            // 是否批发
            'is_wholesale_goods' => input('param.is_wholesale_goods', ''),
            // 是否会员价
            'is_userdiscount_goods' => input('param.is_userdiscount_goods', ''),
            // 是否分销商品
            'is_distributor_goods' => input('param.is_distributor_goods', ''),
            // 最低价格
            'min_price' => input('param.min_price', ''),
            // 最高价格
            'max_price' => input('param.max_price', ''),
            // 最低评分
            'min_goods_avg_score' => input('param.min_goods_avg_score', ''),
            // 最高评分
            'max_goods_avg_score' => input('param.max_goods_avg_score', ''),
            'sort_field' => input('param.sort_field', 'sales_num'),
            'sort_type' => input('param.sort_type', 'desc'),
            'page_current' => input('param.page_current', 1),
            'page_size' => input('param.page_size', 10),
        );

        $this->validate($data, 'app\api\controller\tblGoods\validate\TblGoodsValidate.pages');

        $result = (new TblGoodsService())->getTblGoodsPages($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 猜你喜欢商品列表
     * @OA\Get(
     *     path="/api/tbl-goods/goods/random",
     *     tags={"api/tblGoods/TblGoods"},
     *     summary="猜你喜欢商品列表",
     *     description="获取推荐商品列表",
     *     @OA\Parameter(
     *         name="platform",
     *         in="query",
     *         required=false,
     *         description="平台标识",
     *         @OA\Schema(type="string", example="mall")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="数量限制",
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
    public function getRandomGoods(){
        $data = array(
            'platform' => input('param.platform', ''),
            'limit' => input('param.limit', 10),
        );

        $this->validate($data, 'app\api\controller\tblGoods\validate\TblGoodsValidate.random');

        $result = (new TblGoodsService())->getRandomGoods($data);
        return ds_json_success('操作成功',$result);
    }
}