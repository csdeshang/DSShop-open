<?php

namespace app\adminapi\controller\tblOrder;

use app\adminapi\service\order\TblOrderInvoiceService;
use app\deshang\base\controller\BaseAdminController;

/**
 * @OA\Tag(name="admin-api/tblOrder/TblOrderInvoice", description="订单开票申请查询（只读）")
 */
class TblOrderInvoice extends BaseAdminController
{
    /**
     * @OA\Get(
     *     path="/adminapi/tbl-order/invoices/pages",
     *     summary="开票申请分页列表",
     *     tags={"admin-api/tblOrder/TblOrderInvoice"},
     *     description="跨店铺查询，仅查看",
     *     @OA\Parameter(name="platform", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="invoice_status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="order_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="order_sn", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="username", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="goods_name", in="query", required=false, @OA\Schema(type="string")),
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
    public function getTblOrderInvoicePages()
    {
        $data = [
            'platform' => input('param.platform', ''),
            'invoice_status' => input('param.invoice_status', ''),
            'order_id' => input('param.order_id', ''),
            'order_sn' => input('param.order_sn', ''),
            'username' => input('param.username', ''),
            'user_id' => input('param.user_id', ''),
            'goods_name' => input('param.goods_name', ''),
        ];
        $this->validate($data, 'app\adminapi\controller\tblOrder\validate\TblOrderInvoiceValidate.pages');
        $result = (new TblOrderInvoiceService())->getTblOrderInvoicePages($data);

        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/adminapi/tbl-order/invoices/{id}",
     *     summary="开票申请详情",
     *     tags={"admin-api/tblOrder/TblOrderInvoice"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
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
    public function getTblOrderInvoiceInfo($id)
    {
        $this->validate(['invoice_id' => $id], 'app\adminapi\controller\tblOrder\validate\TblOrderInvoiceValidate.info');
        $result = (new TblOrderInvoiceService())->getTblOrderInvoiceInfo((int) $id);

        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Patch(
     *     path="/adminapi/tbl-order/invoices/{id}/processing",
     *     summary="标记处理中",
     *     tags={"admin-api/tblOrder/TblOrderInvoice"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function markProcessing($id)
    {
        $this->validate(['invoice_id' => $id], 'app\adminapi\controller\tblOrder\validate\TblOrderInvoiceValidate.processing');
        (new TblOrderInvoiceService())->markProcessing((int) $id);

        return ds_json_success('操作成功', []);
    }

    /**
     * @OA\Patch(
     *     path="/adminapi/tbl-order/invoices/{id}/reject",
     *     summary="驳回开票申请",
     *     tags={"admin-api/tblOrder/TblOrderInvoice"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="reject_reason", type="string", example="抬头信息有误")
     *     )),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function reject($id)
    {
        $data = [
            'invoice_id' => $id,
            'reject_reason' => input('param.reject_reason', ''),
        ];
        $this->validate($data, 'app\adminapi\controller\tblOrder\validate\TblOrderInvoiceValidate.reject');
        (new TblOrderInvoiceService())->reject((int) $id, (string) $data['reject_reason']);

        return ds_json_success('操作成功', []);
    }

    /**
     * @OA\Patch(
     *     path="/adminapi/tbl-order/invoices/{id}/issue",
     *     summary="完成开票",
     *     tags={"admin-api/tblOrder/TblOrderInvoice"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="invoice_no", type="string", example="031001800111-00001234"),
     *         @OA\Property(property="out_invoice_no", type="string"),
     *         @OA\Property(property="invoice_file_url", type="string"),
     *         @OA\Property(property="issue_remark", type="string")
     *     )),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function issue($id)
    {
        $data = [
            'invoice_id' => $id,
            'invoice_no' => input('param.invoice_no', ''),
            'out_invoice_no' => input('param.out_invoice_no', ''),
            'invoice_file_url' => input('param.invoice_file_url', ''),
            'issue_remark' => input('param.issue_remark', ''),
        ];
        $this->validate($data, 'app\adminapi\controller\tblOrder\validate\TblOrderInvoiceValidate.issue');
        (new TblOrderInvoiceService())->issue((int) $id, [
            'invoice_no' => $data['invoice_no'],
            'out_invoice_no' => $data['out_invoice_no'],
            'invoice_file_url' => $data['invoice_file_url'],
            'issue_remark' => $data['issue_remark'],
        ]);

        return ds_json_success('操作成功', []);
    }

    /**
     * @OA\Patch(
     *     path="/adminapi/tbl-order/invoices/{id}/void",
     *     summary="作废开票申请",
     *     tags={"admin-api/tblOrder/TblOrderInvoice"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="void_reason", type="string", example="红冲后重开")
     *     )),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function void($id)
    {
        $data = [
            'invoice_id' => $id,
            'void_reason' => input('param.void_reason', ''),
        ];
        $this->validate($data, 'app\adminapi\controller\tblOrder\validate\TblOrderInvoiceValidate.void');
        (new TblOrderInvoiceService())->void((int) $id, (string) $data['void_reason']);

        return ds_json_success('操作成功', []);
    }
}
