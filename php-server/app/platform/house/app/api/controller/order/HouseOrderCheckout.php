<?php

namespace app\platform\house\app\api\controller\order;

use app\deshang\base\controller\BaseUserController;
use app\platform\house\app\api\service\order\HouseOrderCheckoutService;

/**
 * @OA\Tag(name="platform/house/order/HouseOrderCheckout", description="上门服务订单结算接口")
 */
//下单流程
class HouseOrderCheckout extends BaseUserController
{


    /**
     * @OA\Post(
     *     path="/api/house/order-checkout/check",
     *     summary="获取上门服务订单下单信息",
     *     tags={"platform/house/order/HouseOrderCheckout"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="获取下单信息所需参数",
     *         @OA\JsonContent(
     *             required={"cart_ids"},
     *             @OA\Property(property="cart_ids", type="array", @OA\Items(type="integer"), example={1,2,3}, description="购物车ID数组"),
     *             @OA\Property(property="address_id", type="integer", example=1, description="收货地址ID（可选，支持自提）"),
     *             @OA\Property(property="coupon_id", type="integer", example=1, description="已选优惠券ID（可选）"),
     *             @OA\Property(property="appointment_time", type="string", example="2024-01-01 10:00:00", description="师傅服务预约时间（可选）"),
     *             @OA\Property(property="technician_id", type="integer", example=1, description="已选师傅ID（可选，支持系统分配）")
     *         )
     *     ),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    // 获取下单信息
    public function checkHouseOrderCheckout()
    {
        $data = array(
            'cart_ids' => ids_to_array(input('param.cart_ids')),
            'address_id' => (int) input('param.address_id'),
            'coupon_id' => (int) input('param.coupon_id'),
            'appointment_time' => strtotime(input('param.appointment_time') ?: date('Y-m-d H:i:s')),
            'technician_id' => (int) input('param.technician_id'),
        );

        $this->validate($data, 'app\platform\house\app\api\controller\order\validate\HouseOrderCheckoutValidate.check');

        $result = (new HouseOrderCheckoutService())->checkHouseOrderCheckout($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * @OA\Post(
     *     path="/api/house/order-checkout/generate",
     *     summary="生成上门服务订单",
     *     tags={"platform/house/order/HouseOrderCheckout"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="上门服务订单生成所需信息",
     *         @OA\JsonContent(
     *             required={"cart_ids"},
     *             @OA\Property(property="cart_ids", type="array", @OA\Items(type="integer"), example={1,2,3}, description="购物车ID数组"),
     *             @OA\Property(property="address_id", type="integer", example=1, description="收货地址ID（可选，支持自提）"),
     *             @OA\Property(property="coupon_id", type="integer", example=1, description="已选优惠券ID（可选）"),
     *             @OA\Property(property="user_remark", type="string", example="备注", description="用户备注（可选）"),
     *             @OA\Property(property="referrer_id", type="integer", example=1, description="推荐人ID（可选）"),
     *             @OA\Property(property="appointment_time", type="string", example="2024-01-01 10:00:00", description="师傅服务预约时间（可选）"),
     *             @OA\Property(property="technician_id", type="integer", example=1, description="已选师傅ID（可选，支持系统分配）")
     *         )
     *     ),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    // 生成订单
    public function generateHouseOrder()
    {
        $data = array(
            'cart_ids' => ids_to_array(input('param.cart_ids')),
            'address_id' => (int) input('param.address_id'),
            'coupon_id' => (int) input('param.coupon_id'),
            'user_remark' => input('param.user_remark', ''),
            'referrer_id' => (int) input('param.referrer_id'),
            'appointment_time' => strtotime(input('param.appointment_time')),
            'technician_id' => (int) input('param.technician_id'),
        );

        $this->validate($data, 'app\platform\house\app\api\controller\order\validate\HouseOrderCheckoutValidate.generate');

        $result = (new HouseOrderCheckoutService())->generateHouseOrder($data);
        return ds_json_success('操作成功',$result);
    }
}
