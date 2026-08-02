<?php

namespace app\api\controller\tblOrder;

use app\deshang\base\controller\BaseUserController;
use app\api\service\order\TblOrderService;

/**
 * @OA\Tag(name="api/tblOrder/TblOrder", description="订单管理接口")
 */
class TblOrder extends BaseUserController
{
    /**
     * 获取订单分页列表
     * @OA\Get(
     *     path="/api/tbl-order/orders/pages",
     *     tags={"api/tblOrder/TblOrder"},
     *     summary="获取订单分页列表",
     *     description="获取用户订单分页数据",
     *     @OA\Parameter(
     *         name="order_sn",
     *         in="query",
     *         required=false,
     *         description="订单编号",
     *         @OA\Schema(type="string", example="202412010001")
     *     ),
     *     @OA\Parameter(
     *         name="tab_selected",
     *         in="query",
     *         required=false,
     *         description="订单状态选项卡：pending(待付款)、accepted(待收货/使用，包含已付款、已发货、已确认状态)、comment(待评价)、refund(退款)，空字符串表示全部",
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
    public function getTblOrderPages()
    {
        $data = array(
            'order_sn' => input('param.order_sn'),
            'tab_selected' => input('param.tab_selected', ''),
        );

        $this->validate($data, 'app\api\controller\tblOrder\validate\TblOrderValidate.pages');

        $result = (new TblOrderService())->getTblOrderPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取订单详情
     * @OA\Get(
     *     path="/api/tbl-order/orders/{id}",
     *     tags={"api/tblOrder/TblOrder"},
     *     summary="获取订单详情",
     *     description="获取指定订单的详细信息",
     *     @OA\Parameter(
     *         name="id",
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
    public function getTblOrderInfo($id)
    {


        $this->validate(['id' => $id], 'app\api\controller\tblOrder\validate\TblOrderValidate.info');

        $result = (new TblOrderService())->getTblOrderInfo($id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 用户取消订单
     * @OA\Patch(
     *     path="/api/tbl-order/orders/{id}/cancel",
     *     tags={"api/tblOrder/TblOrder"},
     *     summary="用户取消订单",
     *     description="用户取消未支付或未发货的订单",
     *     @OA\Parameter(
     *         name="id",
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
    public function cancelTblOrder($id)
    {
        $data = array(
            'order_id' => $id,
        );

        $this->validate($data, 'app\api\controller\tblOrder\validate\TblOrderValidate.cancel');

        $result = (new TblOrderService())->cancelTblOrder($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 用户关闭订单
     * @OA\Patch(
     *     path="/api/tbl-order/orders/{id}/close",
     *     tags={"api/tblOrder/TblOrder"},
     *     summary="用户关闭订单",
     *     description="用户关闭已完成或已取消的订单",
     *     @OA\Parameter(
     *         name="id",
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
    public function closeTblOrder($id)
    {
        $data = array(
            'order_id' => $id,
        );

        $this->validate($data, 'app\api\controller\tblOrder\validate\TblOrderValidate.close');

        $result = (new TblOrderService())->closeTblOrder($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 用户确认收货
     * @OA\Patch(
     *     path="/api/tbl-order/orders/{id}/confirm",
     *     tags={"api/tblOrder/TblOrder"},
     *     summary="用户确认收货",
     *     description="用户确认收到商品，订单完成",
     *     @OA\Parameter(
     *         name="id",
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
    public function confirmTblOrder($id)
    {
        $data = array(
            'order_id' => $id,
        );
        
        $this->validate($data, 'app\api\controller\tblOrder\validate\TblOrderValidate.confirm');

        $result = (new TblOrderService())->confirmTblOrder($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取订单状态数量
     * @OA\Get(
     *     path="/api/tbl-order/orders/status/count",
     *     tags={"api/tblOrder/TblOrder"},
     *     summary="获取订单状态数量",
     *     description="获取用户各状态订单的数量统计",
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
    public function getTblOrderStatusCount(){
        $result = (new TblOrderService())->getTblOrderStatusCount();
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取订单配送员信息
     * @OA\Get(
     *     path="/api/tbl-order/orders/{id}/rider",
     *     tags={"api/tblOrder/TblOrder"},
     *     summary="获取订单配送员信息",
     *     description="获取指定订单的配送员信息（包含最新位置）",
     *     @OA\Parameter(
     *         name="id",
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
    public function getTblOrderRiderInfo($id)
    {
        $this->validate(['id' => $id], 'app\api\controller\tblOrder\validate\TblOrderValidate.info');

        $result = (new TblOrderService())->getTblOrderRiderInfo($id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取订单师傅信息
     * @OA\Get(
     *     path="/api/tbl-order/orders/{id}/technician",
     *     tags={"api/tblOrder/TblOrder"},
     *     summary="获取订单师傅信息",
     *     description="获取指定订单的师傅信息（包含最新位置）",
     *     @OA\Parameter(
     *         name="id",
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
    public function getTblOrderTechnicianInfo($id)
    {
        $this->validate(['id' => $id], 'app\api\controller\tblOrder\validate\TblOrderValidate.info');

        $result = (new TblOrderService())->getTblOrderTechnicianInfo($id);
        return ds_json_success('操作成功', $result);
    }
}
