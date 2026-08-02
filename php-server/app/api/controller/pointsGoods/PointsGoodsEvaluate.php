<?php

namespace app\api\controller\pointsGoods;

use app\deshang\base\controller\BaseApiController;
use app\api\service\pointsGoods\PointsGoodsEvaluateService;

/**
 * @OA\Tag(name="api/pointsGoods/PointsGoodsEvaluate", description="积分商品评价管理接口")
 */
class PointsGoodsEvaluate extends BaseApiController
{
    /**
     * 获取积分商品评价列表
     * @OA\Get(
     *     path="/api/points-goods/evaluates/list",
     *     tags={"api/pointsGoods/PointsGoodsEvaluate"},
     *     summary="获取积分商品评价列表",
     *     @OA\Parameter(name="goods_id", in="query", description="商品ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", description="获取数量", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="成功")
     * )
     */
    public function getPointsGoodsEvaluateList()
    {
        $data = array(
            'goods_id' => (int) input('param.goods_id'),
            'limit' => (int) input('param.limit', 10),
        );
        
        $this->validate($data, 'app\api\controller\pointsGoods\validate\PointsGoodsEvaluateValidate.list');
        
        $result = (new PointsGoodsEvaluateService())->getPointsGoodsEvaluateList($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取积分商品评价分页列表
     * @OA\Get(
     *     path="/api/points-goods/evaluates/pages",
     *     tags={"api/pointsGoods/PointsGoodsEvaluate"},
     *     summary="获取积分商品评价分页列表",
     *     @OA\Parameter(name="goods_id", in="query", description="商品ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="evaluate_score", in="query", description="评价分数", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="filter_type", in="query", description="筛选类型 all:全部 image:有图 good:好评 medium:中评 bad:差评", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="page_current", in="query", description="当前页码", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="page_size", in="query", description="每页数量", required=false, @OA\Schema(type="integer", example=10)),
     *     @OA\Response(response="200", description="成功")
     * )
     */
    public function getPointsGoodsEvaluatePages()
    {
        $data = array(
            'goods_id' => (int) input('param.goods_id'),
            'evaluate_score' => input('param.evaluate_score'),
            'filter_type' => input('param.filter_type', 'all'),
            'page_current' => input('param.page_current', 1),
            'page_size' => input('param.page_size', 10),
        );
        
        $this->validate($data, 'app\api\controller\pointsGoods\validate\PointsGoodsEvaluateValidate.pages');
        
        $result = (new PointsGoodsEvaluateService())->getPointsGoodsEvaluatePages($data);
        return ds_json_success('操作成功', $result);
    }
}
