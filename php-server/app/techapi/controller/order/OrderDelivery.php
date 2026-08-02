<?php

namespace app\techapi\controller\order;
use app\deshang\base\controller\BaseTechnicianController;

use app\techapi\service\order\OrderDeliveryService;

/**
 * @OA\Tag(name="techapi/order/OrderDelivery", description="师傅订单服务接口")
 */
class OrderDelivery extends BaseTechnicianController{

    /**
     * 获取师傅订单分页列表
     * @OA\Get(
     *     path="/techapi/order/deliveries/pages",
     *     tags={"techapi/order/OrderDelivery"},
     *     summary="获取师傅订单分页列表",
     *     description="获取师傅的订单分页列表",
     *     @OA\Parameter(
     *         name="tab_selected",
     *         in="query",
     *         required=false,
     *         description="选项卡类型",
     *         @OA\Schema(type="string", example="pending")
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
    public function getOrderDeliveryPages(){
        $data = array(
            'tab_selected' => input('param.tab_selected'),
        );

        // 验证器
        $this->validate($data, 'app\techapi\controller\order\validate\OrderDeliveryValidate.pages');

        $result = (new OrderDeliveryService())->getOrderDeliveryPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取师傅订单详情
     * @OA\Get(
     *     path="/techapi/order/deliveries/{order_id}",
     *     tags={"techapi/order/OrderDelivery"},
     *     summary="获取师傅订单详情",
     *     description="获取指定订单的详细信息",
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
        $this->validate(['order_id' => $order_id], 'app\techapi\controller\order\validate\OrderDeliveryValidate.info');

        $result = (new OrderDeliveryService())->getOrderDeliveryInfo($order_id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 师傅抢单
     * @OA\Post(
     *     path="/techapi/order/deliveries/{order_id}/grab",
     *     tags={"techapi/order/OrderDelivery"},
     *     summary="师傅抢单",
     *     description="师傅抢接未分配师傅的订单",
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
     *             required={"longitude", "latitude"},
     *             @OA\Property(property="longitude", type="number", description="当前经度", example=116.397128),
     *             @OA\Property(property="latitude", type="number", description="当前纬度", example=39.916527)
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
        $this->validate($data, 'app\techapi\controller\order\validate\OrderDeliveryValidate.grab');

        $result = (new OrderDeliveryService())->grabOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 师傅接单
     * @OA\Post(
     *     path="/techapi/order/deliveries/{order_id}/accept",
     *     tags={"techapi/order/OrderDelivery"},
     *     summary="师傅接单",
     *     description="师傅确认接受已分配的订单",
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
     *             required={"longitude", "latitude"},
     *             @OA\Property(property="longitude", type="number", description="当前经度", example=116.397128),
     *             @OA\Property(property="latitude", type="number", description="当前纬度", example=39.916527)
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
    public function acceptOrderDelivery($order_id){
        $data = array(
            'order_id' => (int)$order_id,
            'longitude' => input('param.longitude',0),
            'latitude' => input('param.latitude',0),
        );

        // 验证器
        $this->validate($data, 'app\techapi\controller\order\validate\OrderDeliveryValidate.accept');

        $result = (new OrderDeliveryService())->acceptOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 师傅出发
     * @OA\Post(
     *     path="/techapi/order/deliveries/{order_id}/departure",
     *     tags={"techapi/order/OrderDelivery"},
     *     summary="师傅出发",
     *     description="师傅标记已出发前往服务地点",
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
     *             required={"longitude", "latitude"},
     *             @OA\Property(property="longitude", type="number", description="当前经度", example=116.397128),
     *             @OA\Property(property="latitude", type="number", description="当前纬度", example=39.916527)
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
    public function departureOrderDelivery($order_id){
        $data = array(
            'order_id' => (int)$order_id,
            'longitude' => input('param.longitude',0),
            'latitude' => input('param.latitude',0),
        );

        // 验证器
        $this->validate($data, 'app\techapi\controller\order\validate\OrderDeliveryValidate.departure');

        $result = (new OrderDeliveryService())->departureOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 师傅确认到达
     * @OA\Post(
     *     path="/techapi/order/deliveries/{order_id}/arrived",
     *     tags={"techapi/order/OrderDelivery"},
     *     summary="师傅确认到达",
     *     description="师傅确认已到达服务地点",
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
     *             required={"longitude", "latitude"},
     *             @OA\Property(property="longitude", type="number", description="当前经度", example=116.397128),
     *             @OA\Property(property="latitude", type="number", description="当前纬度", example=39.916527)
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
        $this->validate($data, 'app\techapi\controller\order\validate\OrderDeliveryValidate.arrived');

        $result = (new OrderDeliveryService())->arrivedOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 师傅开始服务
     * @OA\Post(
     *     path="/techapi/order/deliveries/{order_id}/start-service",
     *     tags={"techapi/order/OrderDelivery"},
     *     summary="师傅开始服务",
     *     description="师傅标记开始提供服务",
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
     *             required={"longitude", "latitude"},
     *             @OA\Property(property="longitude", type="number", description="当前经度", example=116.397128),
     *             @OA\Property(property="latitude", type="number", description="当前纬度", example=39.916527)
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
    public function startServiceOrderDelivery($order_id){
        $data = array(
            'order_id' => (int)$order_id,
            'longitude' => input('param.longitude',0),
            'latitude' => input('param.latitude',0),
        );

        // 验证器
        $this->validate($data, 'app\techapi\controller\order\validate\OrderDeliveryValidate.startService');

        $result = (new OrderDeliveryService())->startServiceOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 师傅确认完成
     * @OA\Post(
     *     path="/techapi/order/deliveries/{order_id}/complete",
     *     tags={"techapi/order/OrderDelivery"},
     *     summary="师傅确认完成",
     *     description="师傅确认服务完成",
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
     *             required={"longitude", "latitude"},
     *             @OA\Property(property="longitude", type="number", description="当前经度", example=116.397128),
     *             @OA\Property(property="latitude", type="number", description="当前纬度", example=39.916527)
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
        $this->validate($data, 'app\techapi\controller\order\validate\OrderDeliveryValidate.complete');

        $result = (new OrderDeliveryService())->completeOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 师傅取消接单
     * @OA\Post(
     *     path="/techapi/order/deliveries/{order_id}/cancel",
     *     tags={"techapi/order/OrderDelivery"},
     *     summary="师傅取消接单",
     *     description="师傅取消已接受的订单",
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
     *             required={"longitude", "latitude"},
     *             @OA\Property(property="longitude", type="number", description="当前经度", example=116.397128),
     *             @OA\Property(property="latitude", type="number", description="当前纬度", example=39.916527)
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
        $this->validate($data, 'app\techapi\controller\order\validate\OrderDeliveryValidate.cancel');

        $result = (new OrderDeliveryService())->cancelOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }
}