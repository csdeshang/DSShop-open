<?php

namespace app\adminapi\service\order;

use app\common\dao\order\TblOrderGoodsDao;
use app\common\dao\order\TblOrderInvoiceDao;
use app\deshang\base\service\BaseAdminService;
use app\deshang\exceptions\NotFoundException;
use app\deshang\service\order\DeshangTblOrderInvoiceService;
use app\deshang\utils\SearchHelper;
use think\facade\Db;

class TblOrderInvoiceService extends BaseAdminService
{
    /**
     * 开票申请分页
     *
     * @param array $data 筛选：platform、invoice_status、order_id、order_sn、username、user_id、goods_name
     */
    public function getTblOrderInvoicePages(array $data): array
    {
        $condition = [];

        if (isset($data['platform']) && $data['platform'] !== '') {
            $condition['platform'] = $data['platform'];
        }
        if (isset($data['user_id']) && $data['user_id'] !== '' && (int) $data['user_id'] > 0) {
            $condition[] = ['user_id', '=', (int) $data['user_id']];
        }
        if (isset($data['username']) && $data['username'] !== '') {
            $userIds = SearchHelper::getUserIdsByUsername((string) $data['username']);
            if (!empty($userIds)) {
                $condition[] = ['user_id', 'in', $userIds];
            } else {
                $condition[] = ['id', '=', 0];
            }
        }
        if (isset($data['goods_name']) && $data['goods_name'] !== '') {
            $goodsIds = SearchHelper::getGoodsIdsByGoodsName((string) $data['goods_name']);
            if (!empty($goodsIds)) {
                $orderIds = (new TblOrderGoodsDao())->getOrderGoodsColumn([
                    ['goods_id', 'in', $goodsIds],
                ], 'order_id');
                if (!empty($orderIds)) {
                    $condition[] = ['order_id', 'in', $orderIds];
                } else {
                    $condition[] = ['id', '=', 0];
                }
            } else {
                $condition[] = ['id', '=', 0];
            }
        }
        if (isset($data['invoice_status']) && $data['invoice_status'] !== '' && $data['invoice_status'] !== null) {
            $condition[] = ['invoice_status', '=', (int) $data['invoice_status']];
        }
        if (isset($data['order_id']) && $data['order_id'] !== '' && (int) $data['order_id'] > 0) {
            $condition[] = ['order_id', '=', (int) $data['order_id']];
        }
        if (isset($data['order_sn']) && $data['order_sn'] !== '') {
            $condition[] = ['order_sn', 'like', '%' . $data['order_sn'] . '%'];
        }

        return (new TblOrderInvoiceDao())->getWithRelOrderInvoicePages($condition);
    }

    /**
     * 开票申请详情
     */
    public function getTblOrderInvoiceInfo(int $id): array
    {
        $result = (new TblOrderInvoiceDao())->getWithRelOrderInvoiceInfo(['id' => $id]);
        if (empty($result['id'])) {
            throw new NotFoundException('开票申请不存在');
        }

        $result['store_invoice_actions'] = DeshangTblOrderInvoiceService::getStoreInvoiceActions(
            $result['invoice_status'] ?? 0
        );

        return $result;
    }

    /**
     * 待处理 → 处理中
     */
    public function markProcessing(int $invoiceId): void
    {
        Db::transaction(function () use ($invoiceId) {
            (new DeshangTblOrderInvoiceService())->storeMarkProcessing(
                $invoiceId,
                (int) $this->user_id
            );
        });
    }

    /**
     * 驳回开票申请
     */
    public function reject(int $invoiceId, string $rejectReason): void
    {
        Db::transaction(function () use ($invoiceId, $rejectReason) {
            (new DeshangTblOrderInvoiceService())->storeReject(
                $invoiceId,
                (int) $this->user_id,
                $rejectReason
            );
        });
    }

    /**
     * 完成开票
     */
    public function issue(int $invoiceId, array $payload): void
    {
        Db::transaction(function () use ($invoiceId, $payload) {
            (new DeshangTblOrderInvoiceService())->storeIssue(
                $invoiceId,
                (int) $this->user_id,
                $payload
            );
        });
    }

    /**
     * 作废开票
     */
    public function void(int $invoiceId, string $voidReason): void
    {
        Db::transaction(function () use ($invoiceId, $voidReason) {
            (new DeshangTblOrderInvoiceService())->storeVoid(
                $invoiceId,
                (int) $this->user_id,
                $voidReason
            );
        });
    }
}
