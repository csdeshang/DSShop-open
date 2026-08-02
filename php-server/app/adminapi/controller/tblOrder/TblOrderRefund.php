<?php

namespace app\adminapi\controller\tblOrder;

use app\deshang\base\controller\BaseAdminController;
use app\adminapi\service\order\TblOrderRefundService;
use app\common\enum\order\TblOrderRefundEnum;

/**
 * @OA\Tag(name="admin-api/tblOrder/TblOrderRefund", description="订单退款管理接口")
 */
class TblOrderRefund extends BaseAdminController
{

    /**
     * @OA\Get(
     *     path="/adminapi/tbl-order/refunds/pages",
     *     summary="获取订单退款分页列表",
     *     tags={"admin-api/tblOrder/TblOrderRefund"},
     *     @OA\Parameter(
     *         name="platform",
     *         in="query",
     *         required=false,
     *         description="平台类型",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=false,
     *         description="用户ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         required=false,
     *         description="用户名",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="goods_name",
     *         in="query",
     *         required=false,
     *         description="商品名称",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="out_refund_no",
     *         in="query",
     *         required=false,
     *         description="退款单号",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="refund_type",
     *         in="query",
     *         required=false,
     *         description="退款类型",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="refund_status",
     *         in="query",
     *         required=false,
     *         description="退款状态",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="refund_method",
     *         in="query",
     *         required=false,
     *         description="退款方式",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="refund_amount_min",
     *         in="query",
     *         required=false,
     *         description="最小退款金额",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="refund_amount_max",
     *         in="query",
     *         required=false,
     *         description="最大退款金额",
     *         @OA\Schema(type="string")
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
    public function getTblOrderRefundPages()
    {
        $data = array(
            'platform' => input('param.platform'),
            'user_id' => input('param.user_id'),
            'username' => input('param.username'),
            'goods_name' => input('param.goods_name'),
            'out_refund_no' => input('param.out_refund_no'),
            'refund_type' => input('param.refund_type'),
            'refund_status' => input('param.refund_status'),
            'refund_method' => input('param.refund_method'),
            'refund_amount_min' => input('param.refund_amount_min'),
            'refund_amount_max' => input('param.refund_amount_max'),
        );


        $result = (new TblOrderRefundService())->getTblOrderRefundPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/adminapi/tbl-order/refunds/list",
     *     summary="获取订单退款列表",
     *     tags={"admin-api/tblOrder/TblOrderRefund"},
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=true,
     *         description="订单ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getTblOrderRefundList()
    {
        $data = array(
            'order_id' => (int) input('param.order_id'),
        );
        $result = (new TblOrderRefundService())->getTblOrderRefundList($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/adminapi/tbl-order/refunds/{id}",
     *     summary="获取订单退款详情",
     *     tags={"admin-api/tblOrder/TblOrderRefund"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="退款ID",
     *         @OA\Schema(type="integer")
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
        $this->validate(['refund_id' => $id], 'app\adminapi\controller\tblOrder\validate\TblOrderRefundValidate.info');
        $result = (new TblOrderRefundService())->getTblOrderRefundInfo((int)$id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Post(
     *     path="/adminapi/tbl-order/refunds",
     *     summary="后台发起退款",
     *     tags={"admin-api/tblOrder/TblOrderRefund"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="order_id", type="integer", example=1),
     *         @OA\Property(property="order_goods_id", type="integer", example=1),
     *         @OA\Property(property="apply_amount", type="number", example=100),
     *         @OA\Property(property="refund_explain", type="string")
     *     )),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function addTblOrderRefund()
    {
        $data = array(
            'order_id' => input('param.order_id'),
            'order_goods_id' => input('param.order_goods_id'),
            'apply_amount' => round(input('param.apply_amount'), 2),
            'refund_explain' => input('param.refund_explain'),
            'refund_type' => TblOrderRefundEnum::TYPE_ONLY_REFUND,
        );
        $this->validate($data, 'app\adminapi\controller\tblOrder\validate\TblOrderRefundValidate.add');

        $result = (new TblOrderRefundService())->addTblOrderRefund($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/adminapi/tbl-order/refunds/refund-status-dict",
     *     summary="获取订单退款状态字典",
     *     tags={"admin-api/tblOrder/TblOrderRefund"},
     *     @OA\Parameter(name="platform", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function getTblOrderRefundStatusDict()
    {
        $platform = input('param.platform');
        $this->validate(['platform' => $platform], 'app\adminapi\controller\tblOrder\validate\TblOrderRefundValidate.statusDict');

        switch ($platform) {
            case 'mall':
                $result = TblOrderRefundEnum::getMallRefundStatusDict();
                break;
            case 'food':
                $result = TblOrderRefundEnum::getFoodRefundStatusDict();
                break;
            case 'kms':
                $result = TblOrderRefundEnum::getKmsRefundStatusDict();
                break;
            case 'house':
                $result = TblOrderRefundEnum::getHouseRefundStatusDict();
                break;
            default:
                return ds_json_error('平台参数错误');
        }

        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Patch(
     *     path="/adminapi/tbl-order/refunds/{id}/agree",
     *     summary="同意退款",
     *     tags={"admin-api/tblOrder/TblOrderRefund"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(@OA\Property(property="agree_reason", type="string"))),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function agreeTblOrderRefund($id)
    {
        $data = array(
            'refund_id' => $id,
            'agree_reason' => input('param.agree_reason', ''),
        );
        $this->validate($data, 'app\adminapi\controller\tblOrder\validate\TblOrderRefundValidate.agree');

        $result = (new TblOrderRefundService())->agreeTblOrderRefund($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Patch(
     *     path="/adminapi/tbl-order/refunds/{id}/reject",
     *     summary="拒绝退款",
     *     tags={"admin-api/tblOrder/TblOrderRefund"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(@OA\Property(property="reject_reason", type="string"))),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function rejectTblOrderRefund($id)
    {
        $data = array(
            'refund_id' => $id,
            'reject_reason' => input('param.reject_reason'),
        );
        $this->validate($data, 'app\adminapi\controller\tblOrder\validate\TblOrderRefundValidate.reject');

        $result = (new TblOrderRefundService())->rejectTblOrderRefund($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Patch(
     *     path="/adminapi/tbl-order/refunds/{id}/receive-goods",
     *     summary="确认收到退货",
     *     tags={"admin-api/tblOrder/TblOrderRefund"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function receiveGoodsTblOrderRefund($id)
    {
        $this->validate(['refund_id' => $id], 'app\adminapi\controller\tblOrder\validate\TblOrderRefundValidate.receiveGoods');

        $result = (new TblOrderRefundService())->receiveGoodsTblOrderRefund((int) $id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Post(
     *     path="/adminapi/tbl-order/refunds/{id}/retry",
     *     summary="重新发起退款",
     *     tags={"admin-api/tblOrder/TblOrderRefund"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="退款ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="退款ID不能为0",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="msg", type="string", example="退款ID不能为0")
     *         )
     *     )
     * )
     */
    public function retryTblOrderRefund($id)
    {
        $this->validate(['refund_id' => $id], 'app\adminapi\controller\tblOrder\validate\TblOrderRefundValidate.retry');
        $refund_id = (int) $id;
        if ($refund_id <= 0) {
            return ds_json_error('退款ID不能为0');
        }
        $result = (new TblOrderRefundService())->retryTblOrderRefund($refund_id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/adminapi/tbl-order/refunds/{id}/logs",
     *     summary="获取订单退款日志列表",
     *     tags={"admin-api/tblOrder/TblOrderRefund"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="退款ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getTblOrderRefundLogList($id)
    {
        $this->validate(['refund_id' => $id], 'app\adminapi\controller\tblOrder\validate\TblOrderRefundValidate.logList');
        $result = (new TblOrderRefundService())->getTblOrderRefundLogList((int)$id);
        return ds_json_success('操作成功', $result);
    }

}