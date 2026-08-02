<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserPointsGoodsOrderService;

/**
 * @OA\Tag(name="api/user/UserPointsGoodsOrder", description="用户积分商品订单管理接口")
 */
class UserPointsGoodsOrder extends BaseUserController
{
    /**
     * 获取积分商品订单分页列表
     * @OA\Get(
     *     path="/api/user/points-goods-orders/pages",
     *     tags={"api/user/UserPointsGoodsOrder"},
     *     summary="获取积分商品订单分页列表",
     *     @OA\Parameter(name="page_current", in="query", description="当前页码", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page_size", in="query", description="每页数量", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="order_status", in="query", description="订单状态", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="成功")
     * )
     */
    public function getPointsGoodsOrderPages()
    {
        $data = array(
            'order_status' => input('param.order_status', ''),
        );
        $data = (new UserPointsGoodsOrderService())->getPointsGoodsOrderPages($data);
        return ds_json_success('操作成功', $data);
    }

    /**
     * 获取积分商品订单详情
     * @OA\Get(
     *     path="/api/user/points-goods-orders/{id}",
     *     tags={"api/user/UserPointsGoodsOrder"},
     *     summary="获取积分商品订单详情",
     *     @OA\Parameter(name="id", in="path", description="订单ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="成功")
     * )
     */
    public function getPointsGoodsOrderDetail($orderId)
    {
        $data = (new UserPointsGoodsOrderService())->getPointsGoodsOrderDetail((int) $orderId);
        return ds_json_success('操作成功', $data);
    }

    /**
     * 取消积分商品订单
     * @OA\Patch(
     *     path="/api/user/points-goods-orders/{id}/cancel",
     *     tags={"api/user/UserPointsGoodsOrder"},
     *     summary="取消积分商品订单",
     *     @OA\Parameter(name="id", in="path", description="订单ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="成功")
     * )
     */
    public function cancelPointsGoodsOrder($orderId)
    {
        $result = (new UserPointsGoodsOrderService())->cancelPointsGoodsOrder((int) $orderId);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 确认收货
     * @OA\Patch(
     *     path="/api/user/points-goods-orders/{id}/confirm",
     *     tags={"api/user/UserPointsGoodsOrder"},
     *     summary="确认收货",
     *     @OA\Parameter(name="id", in="path", description="订单ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="成功")
     * )
     */
    public function confirmPointsGoodsOrder($orderId)
    {
        $result = (new UserPointsGoodsOrderService())->confirmPointsGoodsOrder((int) $orderId);
        return ds_json_success('操作成功', $result);
    }
}
