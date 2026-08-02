<?php

namespace app\api\controller\tblOrder;

use app\deshang\base\controller\BaseUserController;
use app\api\service\order\TblOrderRefundService;

/**
 * @OA\Tag(name="api/tblOrder/TblOrderRefund", description="订单退款管理接口")
 */
class TblOrderRefund extends BaseUserController
{



    /**
     * @OA\Get(
     *     path="/api/tbl-order/refunds/pages",
     *     tags={"api/tblOrder/TblOrderRefund"},
     *     summary="获取订单退款分页列表",
     *     description="获取当前用户的订单退款记录列表",
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
    public function getTblOrderRefundPages()
    {
        $data = array(
            
        );
        $result = (new TblOrderRefundService())->getTblOrderRefundPages($data);
        return ds_json_success('操作成功', $result);
    }


    /**
     * @OA\Post(
     *     path="/api/tbl-order/refunds",
     *     tags={"api/tblOrder/TblOrderRefund"},
     *     summary="申请退款",
     *     description="用户申请订单退款",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="integer", example=1, description="订单ID"),
     *             @OA\Property(property="order_goods_id", type="integer", example=1, description="订单商品ID"),
     *             @OA\Property(property="apply_amount", type="number", example=100.00, description="退款金额"),
     *             @OA\Property(property="refund_explain", type="string", example="商品质量问题", description="退款说明"),
     *             @OA\Property(property="refund_type", type="integer", example=1, description="退款类型"),
     *             @OA\Property(property="refund_images", type="string", example="image1.jpg,image2.jpg", description="退款凭证图片，多张图片用逗号分隔")
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
    public function applyTblOrderRefund()
    {
        $data = array(
            'order_id' => input('param.order_id'),
            'order_goods_id' => input('param.order_goods_id'),
            'apply_amount' => round(input('param.apply_amount'), 2),
            'refund_explain' => input('param.refund_explain'),
            'refund_type' =>input('param.refund_type'),
            'refund_images' => input('param.refund_images'),
        );

        $this->validate($data, 'app\api\controller\tblOrder\validate\TblOrderRefundValidate.apply');

        $result = (new TblOrderRefundService())->applyTblOrderRefund($data);
        return ds_json_success('操作成功', $result);
    }


    /**
     * @OA\Put(
     *     path="/api/tbl-order/refunds/{id}",
     *     tags={"api/tblOrder/TblOrderRefund"},
     *     summary="修改退款信息",
     *     description="修改退款申请的信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="退款申请ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="apply_amount", type="number", example=100.00, description="退款金额"),
     *             @OA\Property(property="refund_explain", type="string", example="商品质量问题", description="退款说明")
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
    public function editTblOrderRefund($id)
    {
        $data = array(
            'id' => $id,
            'apply_amount' => round(input('param.apply_amount'), 2),
            'refund_explain' => input('param.refund_explain'),
        );

        $this->validate($data, 'app\api\controller\tblOrder\validate\TblOrderRefundValidate.edit');

        $result = (new TblOrderRefundService())->editTblOrderRefund($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Patch(
     *     path="/api/tbl-order/refunds/{id}/cancel",
     *     tags={"api/tblOrder/TblOrderRefund"},
     *     summary="取消退款",
     *     description="取消退款申请",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="退款申请ID",
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
    public function cancelTblOrderRefund($id)
    {


        $this->validate(['id' => $id], 'app\api\controller\tblOrder\validate\TblOrderRefundValidate.cancel');

        $result = (new TblOrderRefundService())->cancelTblOrderRefund($id);
        return ds_json_success('操作成功', $result);
    }


    /**
     * @OA\Post(
     *     path="/api/tbl-order/refunds/{id}/return-goods",
     *     tags={"api/tblOrder/TblOrderRefund"},
     *     summary="用户退货",
     *     description="用户填写退货物流信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="退款申请ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="express_company", type="string", example="顺丰速运", description="快递公司"),
     *             @OA\Property(property="express_number", type="string", example="SF1234567890", description="快递单号")
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
    public function userReturnGoods($id)
    {
        $data = array(
            'id' => $id,
            'express_company' => input('param.express_company'),
            'express_number' => input('param.express_number'),
        );

        $this->validate($data, 'app\api\controller\tblOrder\validate\TblOrderRefundValidate.returnGoods');

        $result = (new TblOrderRefundService())->userReturnGoods($data);
        return ds_json_success('操作成功', $result);
    }



    /**
     * @OA\Get(
     *     path="/api/tbl-order/refunds/{id}",
     *     tags={"api/tblOrder/TblOrderRefund"},
     *     summary="获取退款详情",
     *     description="获取指定退款申请的详细信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="退款申请ID",
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
    public function getTblOrderRefundInfo($id)
    {

        $this->validate(['id' => $id], 'app\api\controller\tblOrder\validate\TblOrderRefundValidate.info');

        $result = (new TblOrderRefundService())->getTblOrderRefundInfo($id);
        return ds_json_success('操作成功', $result);
    }






}