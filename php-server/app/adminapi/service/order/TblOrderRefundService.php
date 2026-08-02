<?php

namespace app\adminapi\service\order;

use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderGoodsDao;
use app\common\dao\order\TblOrderRefundDao;
use app\common\dao\order\TblOrderRefundLogDao;
use app\common\enum\order\TblOrderRefundEnum;
use app\deshang\base\service\BaseAdminService;
use app\deshang\exceptions\CommonException;
use app\deshang\service\order\DeshangTblOrderRefundService;
use app\deshang\utils\SearchHelper;
use think\facade\Db;

class TblOrderRefundService extends BaseAdminService
{
    protected $dao;

    public function __construct()
    {
        parent::__construct();
        $this->dao = new TblOrderRefundDao();
    }

    public function getTblOrderRefundPages(array $data): array
    {
        $condition = [];
        if (isset($data['platform']) && $data['platform'] != '') {
            $condition['platform'] = $data['platform'];
        }

        if (isset($data['username']) && $data['username'] != '') {
            $userIds = SearchHelper::getUserIdsByUsername($data['username']);
            $condition[] = ['user_id', 'in', $userIds];
        }

        if (isset($data['goods_name']) && $data['goods_name'] != '') {
            $goodsIds = SearchHelper::getGoodsIdsByGoodsName($data['goods_name']);
            if (!empty($goodsIds)) {
                $orderGoodsDao = new TblOrderGoodsDao();
                $orderIds = $orderGoodsDao->getOrderGoodsColumn([
                    ['goods_id', 'in', $goodsIds]
                ], 'order_id');
                $condition[] = ['order_id', 'in', $orderIds];
            } else {
                $condition[] = ['order_id', '=', -1];
            }
        }

        if (isset($data['out_refund_no']) && $data['out_refund_no'] != '') {
            $condition[] = ['out_refund_no', 'like', '%' . $data['out_refund_no'] . '%'];
        }

        if (isset($data['refund_type']) && $data['refund_type'] !== '') {
            $condition[] = ['refund_type', '=', $data['refund_type']];
        }

        if (isset($data['refund_status']) && $data['refund_status'] !== '') {
            $condition[] = ['refund_status', '=', $data['refund_status']];
        }

        if (isset($data['refund_method']) && $data['refund_method'] !== '') {
            $condition[] = ['refund_method', '=', $data['refund_method']];
        }

        if (isset($data['refund_amount_min']) && $data['refund_amount_min'] !== '') {
            $condition[] = ['refund_amount', '>=', $data['refund_amount_min']];
        }
        if (isset($data['refund_amount_max']) && $data['refund_amount_max'] !== '') {
            $condition[] = ['refund_amount', '<=', $data['refund_amount_max']];
        }

        return $this->dao->getWithRelOrderRefundPages($condition);
    }

    public function getTblOrderRefundList(array $data): array
    {
        $condition = [];
        $condition['order_id'] = $data['order_id'];

        return $this->dao->getOrderRefundList($condition);
    }

    public function getTblOrderRefundInfo(int $refund_id): array
    {
        $result = $this->dao->getWithRelOrderRefundInfo(['id' => $refund_id]);
        $result['store_refund_actions'] = (new DeshangTblOrderRefundService())->getStoreRefundActions($result);
        $result['user_refund_actions'] = (new DeshangTblOrderRefundService())->getUserRefundActions($result);

        return $result;
    }

    public function addTblOrderRefund(array $data)
    {
        Db::startTrans();
        try {
            $order_info = (new TblOrderDao())->getOrderInfoById($data['order_id']);
            if (!$order_info) {
                throw new CommonException('订单不存在');
            }
            $result = (new DeshangTblOrderRefundService())->applyRefund($order_info, $data, 'store', (int) $this->user_id);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }

        return $result;
    }

    public function agreeTblOrderRefund(array $data)
    {
        Db::startTrans();
        try {
            $refund_info = $this->dao->getOrderRefundInfo([['id', '=', $data['refund_id']]]);
            if (!$refund_info) {
                throw new CommonException('退款信息不存在');
            }

            (new DeshangTblOrderRefundService())->storeAgreeRefund($refund_info, $data, (int) $this->user_id);

            $refund_info = $this->dao->getOrderRefundInfo([['id', '=', $data['refund_id']]]);
            if (empty($refund_info)) {
                throw new CommonException('退款信息不存在');
            }

            if ($refund_info['refund_type'] == TblOrderRefundEnum::TYPE_ONLY_REFUND) {
                (new DeshangTblOrderRefundService())->processRefund($refund_info, 'admin', (int) $this->user_id);
                event('OrderRefundSuccessListener', [
                    'refund_info' => $refund_info,
                ]);
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }

        return true;
    }

    public function rejectTblOrderRefund(array $data)
    {
        Db::startTrans();
        try {
            $refund_info = $this->dao->getOrderRefundInfo([['id', '=', $data['refund_id']]]);
            if (!$refund_info) {
                throw new CommonException('退款信息不存在');
            }
            $result = (new DeshangTblOrderRefundService())->storeRejectRefund($refund_info, $data, (int) $this->user_id);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }

        return $result;
    }

    public function receiveGoodsTblOrderRefund(int $refund_id)
    {
        Db::startTrans();
        try {
            $refund_info = $this->dao->getOrderRefundInfo([['id', '=', $refund_id]]);
            if (!$refund_info) {
                throw new CommonException('退款信息不存在');
            }

            (new DeshangTblOrderRefundService())->storeReceiveGoods($refund_info, (int) $this->user_id);

            $refund_info = $this->dao->getOrderRefundInfo([['id', '=', $refund_id]]);
            if (empty($refund_info)) {
                throw new CommonException('退款信息不存在');
            }

            (new DeshangTblOrderRefundService())->processRefund($refund_info, 'admin', (int) $this->user_id);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }

        event('OrderRefundSuccessListener', [
            'refund_info' => $refund_info,
        ]);

        return true;
    }

    public function retryTblOrderRefund(int $refund_id): array
    {
        Db::startTrans();
        try {
            $refund_info = $this->dao->getOrderRefundInfoById($refund_id);
            if (empty($refund_info)) {
                throw new CommonException('退款信息不存在');
            }
            (new DeshangTblOrderRefundService())->processRefund($refund_info, 'admin', (int) $this->user_id);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }

        return [];
    }

    public function getTblOrderRefundLogList(int $refund_id): array
    {
        return (new TblOrderRefundLogDao())->getOrderRefundLogList(['refund_id' => $refund_id]);
    }
}
