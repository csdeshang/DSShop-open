<?php

namespace app\adminapi\controller\tblOrder\validate;

use app\common\enum\order\TblOrderInvoiceEnum;
use app\deshang\base\BaseValidate;

class TblOrderInvoiceValidate extends BaseValidate
{
    protected $rule = [
        'invoice_id' => 'require|integer|gt:0',
        'platform' => 'max:20',
        'invoice_status' => 'checkInvoiceStatus',
        'order_id' => 'integer|gt:0',
        'order_sn' => 'max:64',
        'username' => 'max:64',
        'user_id' => 'integer|gt:0',
        'goods_name' => 'max:128',
        'reject_reason' => 'require|max:255',
        'void_reason' => 'require|max:255',
        'invoice_no' => 'require|max:64',
        'out_invoice_no' => 'max:64',
        'invoice_file_url' => 'max:512',
        'issue_remark' => 'max:255',
    ];

    protected $message = [
        'invoice_id.require' => '开票申请ID不能为空',
        'invoice_status.checkInvoiceStatus' => '开票状态值不正确',
        'reject_reason.require' => '驳回原因不能为空',
        'reject_reason.max' => '驳回原因不能超过255个字符',
        'void_reason.require' => '作废原因不能为空',
        'void_reason.max' => '作废原因不能超过255个字符',
        'invoice_no.require' => '发票号码不能为空',
        'invoice_no.max' => '发票号码不能超过64个字符',
        'issue_remark.max' => '完成开票备注不能超过255个字符',
    ];

    protected $scene = [
        'pages' => [
            'platform',
            'invoice_status',
            'order_id',
            'order_sn',
            'username',
            'user_id',
            'goods_name',
        ],
        'info' => ['invoice_id'],
        'processing' => ['invoice_id'],
        'reject' => ['invoice_id', 'reject_reason'],
        'issue' => ['invoice_id', 'invoice_no', 'out_invoice_no', 'invoice_file_url', 'issue_remark'],
        'void' => ['invoice_id', 'void_reason'],
    ];

    public function checkInvoiceStatus($value, $rule, $data): bool
    {
        if ($value === '' || $value === null) {
            return true;
        }
        if (!is_numeric($value)) {
            return false;
        }

        return array_key_exists((int) $value, TblOrderInvoiceEnum::getAllInvoiceStatusDict());
    }
}
