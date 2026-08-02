<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserPointsGoodsOrderCheckoutService;

/**
 * @OA\Tag(name="api/user/UserPointsGoodsOrderCheckout", description="积分商品订单结算接口")
 */
class UserPointsGoodsOrderCheckout extends BaseUserController
{
    /**
     * @OA\Get(
     *     path="/api/user/points-goods-orders/checkout/check",
     *     summary="获取积分商品下单信息",
     *     tags={"api/user/UserPointsGoodsOrderCheckout"},
     *     @OA\Parameter(
     *         name="goods_id",
     *         in="query",
     *         required=true,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="quantity",
     *         in="query",
     *         required=false,
     *         description="购买数量",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="address_id",
     *         in="query",
     *         required=true,
     *         description="收货地址ID",
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
    public function checkPointsGoodsOrderCheckout()
    {

        $data = array(
            'goods_id' => (int) input('param.goods_id'),
            'quantity' => (int) input('param.quantity', 1),
            'address_id' => (int) input('param.address_id'),
        );


        
        $result = (new UserPointsGoodsOrderCheckoutService())->checkPointsGoodsOrderCheckout($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Post(
     *     path="/api/user/points-goods-orders/checkout/generate",
     *     summary="生成积分商品订单",
     *     tags={"api/user/UserPointsGoodsOrderCheckout"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="积分商品订单生成所需信息",
     *         @OA\JsonContent(
     *             required={"goods_id", "address_id"},
     *             @OA\Property(property="goods_id", type="integer", example=1, description="商品ID"),
     *             @OA\Property(property="quantity", type="integer", example=1, description="购买数量"),
     *             @OA\Property(property="address_id", type="integer", example=1, description="收货地址ID"),
     *             @OA\Property(property="user_remark", type="string", example="请尽快发货", description="用户备注")
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
    public function generatePointsGoodsOrder()
    {
        $data = array(
            'goods_id' => (int) input('param.goods_id'),
            'quantity' => (int) input('param.quantity', 1),
            'address_id' => (int) input('param.address_id'),
            'user_remark' => input('param.user_remark', ''),
        );
        
        $result = (new UserPointsGoodsOrderCheckoutService())->generatePointsGoodsOrder($data);
        return ds_json_success('操作成功', $result);
    }
}
