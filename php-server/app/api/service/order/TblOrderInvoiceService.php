<?php

namespace app\api\service\order;

use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderInvoiceDao;
use app\deshang\base\service\BaseUserService;
use app\deshang\exceptions\NotFoundException;
use app\deshang\service\order\DeshangTblOrderInvoiceService;
use think\facade\Db;

class TblOrderInvoiceService extends BaseUserService
{
    public function getTblOrderInvoicePages(array $data): array
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        if (isset($data['invoice_status']) && $data['invoice_status'] !== '') {
            $condition[] = ['invoice_status', '=', (int) $data['invoice_status']];
        }
        if (isset($data['order_id']) && $data['order_id'] !== '' && (int) $data['order_id'] > 0) {
            $condition[] = ['order_id', '=', (int) $data['order_id']];
        }
        return (new TblOrderInvoiceDao())->getWithRelOrderInvoicePages($condition);
    }

    public function getTblOrderInvoiceInfo(int $id): array
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = ['id', '=', $id];
        $result = (new TblOrderInvoiceDao())->getWithRelOrderInvoiceInfo($condition);
        if (empty($result)) {
            throw new NotFoundException('开票申请不存在');
        }
        return $result;
    }

    public function applyTblOrderInvoice(array $data): array
    {
        Db::startTrans();
        try {
            $order_info = (new TblOrderDao())->getOrderInfo([
                ['id', '=', (int) $data['order_id']],
                ['user_id', '=', $this->user_id],
            ], '*', true);
            if (empty($order_info)) {
                throw new NotFoundException('订单不存在');
            }
            $result = (new DeshangTblOrderInvoiceService())->applyInvoice($order_info, $data, $this->user_id);
            Db::commit();
            return $result;
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }
}
