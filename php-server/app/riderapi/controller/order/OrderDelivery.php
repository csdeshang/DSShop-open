<?php

namespace app\riderapi\controller\order;
use app\deshang\base\controller\BaseRiderController;

use app\riderapi\service\order\OrderDeliveryService;

/**
 * @OA\Tag(name="riderapi/order/OrderDelivery", description="骑手订单配送接口")
 */
class OrderDelivery extends BaseRiderController{

    /**
     * 获取正在工作的配送订单列表
     * @OA\Get(
     *     path="/riderapi/order/deliveries/working",
     *     tags={"riderapi/order/OrderDelivery"},
     *     summary="获取正在工作的配送订单列表",
     *     description="获取正在工作的配送订单，包含抢单列表，待取货，待送达",
     *     @OA\Parameter(
     *         name="tab_selected",
     *         in="query",
     *         required=false,
     *         description="选中的标签页",
     *         @OA\Schema(type="string", example="grab")
     *     ),
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         required=false,
     *         description="经度",
     *         @OA\Schema(type="number", format="float", example=116.404)
     *     ),
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         required=false,
     *         description="纬度",
     *         @OA\Schema(type="number", format="float", example=39.915)
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
    public function getWorkingOrderDeliveryList(){
        $data = array(
            'tab_selected' => input('param.tab_selected',''),
            'longitude' => input('param.longitude',0),
            'latitude' => input('param.latitude',0),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\order\validate\OrderDeliveryValidate.workingList');

        $result = (new OrderDeliveryService())->getWorkingOrderDeliveryList($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 骑手接单
     * @OA\Post(
     *     path="/riderapi/order/deliveries/{order_id}/grab",
     *     tags={"riderapi/order/OrderDelivery"},
     *     summary="骑手接单",
     *     description="骑手抢单接单",
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="订单ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="longitude", type="number", format="float", description="经度", example=116.404),
     *             @OA\Property(property="latitude", type="number", format="float", description="纬度", example=39.915)
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
    public function grabOrderDelivery($order_id){

        $data = array(
            'order_id' => (int)$order_id,
            'longitude' => input('param.longitude',0),
            'latitude' => input('param.latitude',0),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\order\validate\OrderDeliveryValidate.grab');

        $result = (new OrderDeliveryService())->grabOrderDelivery($data);
        return ds_json_success('操作成功', $result);

    }

    /**
     * 骑手取消接单
     * @OA\Post(
     *     path="/riderapi/order/deliveries/{order_id}/cancel",
     *     tags={"riderapi/order/OrderDelivery"},
     *     summary="骑手取消接单",
     *     description="骑手取消已接的订单",
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="订单ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="longitude", type="number", format="float", description="经度", example=116.404),
     *             @OA\Property(property="latitude", type="number", format="float", description="纬度", example=39.915)
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
    public function cancelOrderDelivery($order_id){
        $data = array(
            'order_id' => (int)$order_id,
            'longitude' => input('param.longitude',0),
            'latitude' => input('param.latitude',0),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\order\validate\OrderDeliveryValidate.cancel');

        $result = (new OrderDeliveryService())->cancelOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 骑手到达
     * @OA\Post(
     *     path="/riderapi/order/deliveries/{order_id}/arrived",
     *     tags={"riderapi/order/OrderDelivery"},
     *     summary="骑手到达",
     *     description="骑手到达取货地点",
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="订单ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="longitude", type="number", format="float", description="经度", example=116.404),
     *             @OA\Property(property="latitude", type="number", format="float", description="纬度", example=39.915)
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
    public function arrivedOrderDelivery($order_id){
        $data = array(
            'order_id' => (int)$order_id,
            'longitude' => input('param.longitude',0),
            'latitude' => input('param.latitude',0),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\order\validate\OrderDeliveryValidate.arrived');

        $result = (new OrderDeliveryService())->arrivedOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 骑手取货
     * @OA\Post(
     *     path="/riderapi/order/deliveries/{order_id}/pickup",
     *     tags={"riderapi/order/OrderDelivery"},
     *     summary="骑手取货",
     *     description="骑手确认取货",
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="订单ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="longitude", type="number", format="float", description="经度", example=116.404),
     *             @OA\Property(property="latitude", type="number", format="float", description="纬度", example=39.915)
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
    public function pickupOrderDelivery($order_id){
        $data = array(
            'order_id' => (int)$order_id,
            'longitude' => input('param.longitude',0),
            'latitude' => input('param.latitude',0),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\order\validate\OrderDeliveryValidate.pickup');

        $result = (new OrderDeliveryService())->pickupOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 骑手确认送达
     * @OA\Post(
     *     path="/riderapi/order/deliveries/{order_id}/complete",
     *     tags={"riderapi/order/OrderDelivery"},
     *     summary="骑手确认送达",
     *     description="骑手确认配送完成",
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="订单ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="longitude", type="number", format="float", description="经度", example=116.404),
     *             @OA\Property(property="latitude", type="number", format="float", description="纬度", example=39.915)
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
    public function completeOrderDelivery($order_id){
        $data = array(
            'order_id' => (int)$order_id,
            'longitude' => input('param.longitude',0),
            'latitude' => input('param.latitude',0),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\order\validate\OrderDeliveryValidate.complete');

        $result = (new OrderDeliveryService())->completeOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }
    
    /**
     * 骑手订单列表
     * @OA\Get(
     *     path="/riderapi/order/deliveries/pages",
     *     tags={"riderapi/order/OrderDelivery"},
     *     summary="获取骑手订单分页列表",
     *     description="获取已结束的配送订单分页列表",
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
    public function getOrderDeliveryPages(){
        $data = array(
        );

        $result = (new OrderDeliveryService())->getOrderDeliveryPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 骑手订单详情
     * @OA\Get(
     *     path="/riderapi/order/deliveries/{order_id}",
     *     tags={"riderapi/order/OrderDelivery"},
     *     summary="获取骑手订单详情",
     *     description="获取指定订单的详细配送信息",
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="订单ID",
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
    public function getOrderDeliveryInfo($order_id){


        // 验证器
        $this->validate(['order_id' => $order_id], 'app\riderapi\controller\order\validate\OrderDeliveryValidate.info');

        $result = (new OrderDeliveryService())->getOrderDeliveryInfo($order_id);
        return ds_json_success('操作成功', $result);
    }

}