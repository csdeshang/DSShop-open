<?php

namespace app\api\controller\tblGoods;
use app\deshang\base\controller\BaseApiController;

use app\api\service\goods\TblGoodsCommentService;

/**
 * @OA\Tag(name="api/tblGoods/TblGoodsComment", description="商品评论接口")
 */
class TblGoodsComment extends BaseApiController{

    /**
     * 获取商品评论分页列表
     * @OA\Get(
     *     path="/api/tbl-goods/comments/pages",
     *     tags={"api/tblGoods/TblGoodsComment"},
     *     summary="获取商品评论分页列表",
     *     description="获取指定商品的评论分页数据",
     *     @OA\Parameter(
     *         name="goods_id",
     *         in="query",
     *         required=true,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="goods_score",
     *         in="query",
     *         required=false,
     *         description="商品评分",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Parameter(
     *         name="filter_type",
     *         in="query",
     *         required=false,
     *         description="筛选类型 all:全部 image:有图 good:好评 medium:中评 bad:差评",
     *         @OA\Schema(type="string", example="all")
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
    public function getTblGoodsCommentPages(){
        $data = array(
            'goods_id' => (int) input('param.goods_id'),
            'goods_score' => input('param.goods_score'),
            'filter_type' => input('param.filter_type', 'all'),
            'page_current' => input('param.page_current', 1),
            'page_size' => input('param.page_size', 10),
        );

        $this->validate($data, 'app\api\controller\tblGoods\validate\TblGoodsCommentValidate.pages');

        $result = (new TblGoodsCommentService())->getTblGoodsCommentPages($data);
        return ds_json_success('操作成功',$result);
    }

    

}