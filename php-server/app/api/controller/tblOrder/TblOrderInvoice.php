<?php

namespace app\api\controller\tblOrder;

use app\api\service\order\TblOrderInvoiceService;
use app\deshang\base\controller\BaseUserController;

/**
 * @OA\Tag(name="api/tblOrder/TblOrderInvoice", description="订单开票申请接口")
 */
class TblOrderInvoice extends BaseUserController
{
    /**
     * @OA\Get(
     *     path="/api/tbl-order/invoices/pages",
     *     tags={"api/tblOrder/TblOrderInvoice"},
     *     summary="开票申请分页列表",
     *     @OA\Response(response=200, description="操作成功")
     * )
     */
    public function getTblOrderInvoicePages()
    {
        $data = [
            'invoice_status' => input('param.invoice_status', ''),
            'order_id' => input('param.order_id', ''),
        ];
        $result = (new TblOrderInvoiceService())->getTblOrderInvoicePages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Post(
     *     path="/api/tbl-order/invoices",
     *     tags={"api/tblOrder/TblOrderInvoice"},
     *     summary="提交开票申请",
     *     @OA\Response(response=200, description="操作成功")
     * )
     */
    public function applyTblOrderInvoice()
    {
        $data = [
            'order_id' => (int) input('param.order_id'),
            'invoice_type' => (int) input('param.invoice_type', 1),
            'invoice_kind' => (int) input('param.invoice_kind', 1),
            'invoice_title' => input('param.invoice_title'),
            'tax_number' => input('param.tax_number', ''),
            'register_address' => input('param.register_address', ''),
            'register_phone' => input('param.register_phone', ''),
            'bank_name' => input('param.bank_name', ''),
            'bank_account' => input('param.bank_account', ''),
            'receiver_email' => input('param.receiver_email', ''),
            'receiver_mobile' => input('param.receiver_mobile', ''),
            'apply_remark' => input('param.apply_remark', ''),
        ];
        $this->validate($data, 'app\api\controller\tblOrder\validate\TblOrderInvoiceValidate.apply');
        $result = (new TblOrderInvoiceService())->applyTblOrderInvoice($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/api/tbl-order/invoices/{id}",
     *     tags={"api/tblOrder/TblOrderInvoice"},
     *     summary="开票申请详情",
     *     @OA\Response(response=200, description="操作成功")
     * )
     */
    public function getTblOrderInvoiceInfo($id)
    {
        $this->validate(['id' => $id], 'app\api\controller\tblOrder\validate\TblOrderInvoiceValidate.info');
        $result = (new TblOrderInvoiceService())->getTblOrderInvoiceInfo((int) $id);
        return ds_json_success('操作成功', $result);
    }
}
