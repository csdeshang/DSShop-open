<?php

namespace app\platform\mall\app\api\controller\order;

use app\deshang\base\controller\BaseUserController;
use app\platform\mall\app\api\service\order\MallOrderCheckoutService;

/**
 * @OA\Tag(name="platform/mall/order/MallOrderCheckout", description="商城订单结算接口")
 */
//下单流程
class MallOrderCheckout extends BaseUserController
{


    /**
     * @OA\Post(
     *     path="/api/mall/order-checkout/check",
     *     summary="获取商城订单下单信息",
     *     tags={"platform/mall/order/MallOrderCheckout"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="获取下单信息所需参数",
     *         @OA\JsonContent(
     *             required={"cart_ids"},
     *             @OA\Property(property="cart_ids", type="array", @OA\Items(type="integer"), example={1,2,3}, description="购物车ID数组"),
     *             @OA\Property(property="address_id", type="integer", example=1, description="收货地址ID（可选，支持自提）"),
     *             @OA\Property(property="coupon_id", type="integer", example=1, description="已选优惠券ID（可选）")
     *         )
     *     ),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    // 获取下单信息
    public function checkMallOrderCheckout()
    {
        $data = array(
            'cart_ids' => ids_to_array(input('param.cart_ids')),
            'address_id' => (int) input('param.address_id'),
            'coupon_id' => (int) input('param.coupon_id'),
        );

        $this->validate($data, 'app\platform\mall\app\api\controller\order\validate\MallOrderCheckoutValidate.check');

        $result = (new MallOrderCheckoutService())->checkMallOrderCheckout($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * @OA\Post(
     *     path="/api/mall/order-checkout/generate",
     *     summary="生成商城订单",
     *     tags={"platform/mall/order/MallOrderCheckout"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="商城订单生成所需信息",
     *         @OA\JsonContent(
     *             required={"cart_ids"},
     *             @OA\Property(property="cart_ids", type="array", @OA\Items(type="integer"), example={1,2,3}, description="购物车ID数组"),
     *             @OA\Property(property="address_id", type="integer", example=1, description="收货地址ID（可选，支持自提）"),
     *             @OA\Property(property="coupon_id", type="integer", example=1, description="已选优惠券ID（可选）"),
     *             @OA\Property(property="user_remark", type="string", example="请尽快发货", description="用户备注（可选）"),
     *             @OA\Property(property="referrer_id", type="integer", example=1, description="推荐人ID（可选）")
     *         )
     *     ),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    // 生成订单
    public function generateMallOrder()
    {
        $data = array(
            'cart_ids' => ids_to_array(input('param.cart_ids')),
            'address_id' => (int) input('param.address_id'),
            'coupon_id' => (int) input('param.coupon_id'),
            'user_remark' => input('param.user_remark', ''),
            'referrer_id' => (int) input('param.referrer_id'),
        );

        $this->validate($data, 'app\platform\mall\app\api\controller\order\validate\MallOrderCheckoutValidate.generate');

        $result = (new MallOrderCheckoutService())->generateMallOrder($data);
        return ds_json_success('操作成功',$result);
    }
}
