<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserPointsGoodsEvaluateService;

/**
 * @OA\Tag(name="api/user/UserPointsGoodsEvaluate", description="用户积分商品评价管理接口")
 */
class UserPointsGoodsEvaluate extends BaseUserController
{
    /**
     * 添加积分商品评价
     * @OA\Post(
     *     path="/api/user/points-goods-evaluates",
     *     tags={"api/user/UserPointsGoodsEvaluate"},
     *     summary="添加积分商品评价",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="integer", description="订单ID"),
     *             @OA\Property(property="goods_id", type="integer", description="商品ID"),
     *             @OA\Property(property="evaluate_content", type="string", description="评价内容"),
     *             @OA\Property(property="evaluate_images", type="string", description="评价图片，多个用逗号分隔"),
     *             @OA\Property(property="evaluate_score", type="integer", description="评价分数 1-5"),
     *             @OA\Property(property="is_anonymous", type="integer", description="是否匿名 0:否 1:是")
     *         )
     *     ),
     *     @OA\Response(response="200", description="成功")
     * )
     */
    public function addPointsGoodsEvaluate()
    {
        $data = array(
            'order_id' => (int) input('param.order_id'),
            'goods_id' => (int) input('param.goods_id'),
            'evaluate_content' => input('param.evaluate_content'),
            'evaluate_images' => input('param.evaluate_images', ''),
            'evaluate_score' => (int) input('param.evaluate_score'),
            'is_anonymous' => (int) input('param.is_anonymous', 0),
        );
        
        $this->validate($data, 'app\api\controller\user\validate\UserPointsGoodsEvaluateValidate.add');
        
        $result = (new UserPointsGoodsEvaluateService())->addPointsGoodsEvaluate($data);
        return ds_json_success('评价成功', $result);
    }

    /**
     * 获取积分商品评价分页列表
     * @OA\Get(
     *     path="/api/user/points-goods-evaluates/pages",
     *     tags={"api/user/UserPointsGoodsEvaluate"},
     *     summary="获取积分商品评价分页列表",
     *     @OA\Parameter(name="page_current", in="query", description="当前页码", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page_size", in="query", description="每页数量", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="goods_id", in="query", description="商品ID", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="evaluate_score", in="query", description="评价分数", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="成功")
     * )
     */
    public function getPointsGoodsEvaluatePages()
    {
        $data = array(
            'goods_id' => input('param.goods_id', ''),
            'evaluate_score' => input('param.evaluate_score', ''),
        );
        
        $this->validate($data, 'app\api\controller\user\validate\UserPointsGoodsEvaluateValidate.pages');
        
        $result = (new UserPointsGoodsEvaluateService())->getPointsGoodsEvaluatePages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取积分商品评价详情
     * @OA\Get(
     *     path="/api/user/points-goods-evaluates/{id}",
     *     tags={"api/user/UserPointsGoodsEvaluate"},
     *     summary="获取积分商品评价详情",
     *     @OA\Parameter(name="id", in="path", description="评价ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="成功")
     * )
     */
    public function getPointsGoodsEvaluateInfo($id)
    {
        $data = array('id' => (int) $id);
        $result = (new UserPointsGoodsEvaluateService())->getPointsGoodsEvaluateInfo((int) $id);
        return ds_json_success('操作成功', $result);
    }

}
