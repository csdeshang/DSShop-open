<?php

namespace app\adminapi\service\order;

use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderGoodsDao;
use app\common\dao\order\TblOrderLogDao;
use app\common\enum\trade\TradePayEnum;
use app\common\model\trade\TradePayLogModel;
use app\deshang\base\service\BaseAdminService;
use app\deshang\exceptions\CommonException;
use app\deshang\service\order\DeshangTblOrderService;
use app\deshang\utils\SearchHelper;


class TblOrderService extends BaseAdminService
{


    public function __construct()
    {
        parent::__construct();
        $this->dao = new TblOrderDao();
    }

    /**
     * 获取订单分页
     * @param array $params 查询参数
     * @return array
     */
    public function getTblOrderPages(array $data): array
    {
        $condition = [];
        if (isset($data['platform']) && $data['platform'] != '') {
            $condition['platform'] = $data['platform'];
        }
        if (isset($data['order_sn']) && $data['order_sn'] != '') {
            $condition['order_sn'] = $data['order_sn'];
        }
        if (isset($data['order_id']) && $data['order_id'] != '') {
            $condition['id'] = (int)$data['order_id'];
        }
        if (isset($data['order_status']) && $data['order_status'] != '') {
            $condition['order_status'] = $data['order_status'];
        }
        if (isset($data['user_id']) && $data['user_id'] != '') {
            $condition['user_id'] = $data['user_id'];
        }
        
        // 用户名搜索
        if (isset($data['username']) && $data['username'] != '') {
            $userIds = SearchHelper::getUserIdsByUsername($data['username']);
            $condition[] = ['user_id', 'in', $userIds];
        }
        
        // 商品名搜索（通过订单商品表关联）
        if (isset($data['goods_name']) && $data['goods_name'] != '') {
            $goodsIds = SearchHelper::getGoodsIdsByGoodsName($data['goods_name']);
            if (!empty($goodsIds)) {
                // 通过订单商品表获取包含该商品的订单ID列表
                $orderGoodsDao = new TblOrderGoodsDao();
                $orderIds = $orderGoodsDao->getOrderGoodsColumn([
                    ['goods_id', 'in', $goodsIds]
                ], 'order_id');
                $condition[] = ['id', 'in', $orderIds];
            }else{
                $condition[] = ['id', '=', -1];
            }
        }
        
        // 支付单号搜索
        if (isset($data['out_trade_no']) && $data['out_trade_no'] != '') {
            $condition[] = ['out_trade_no', 'like', '%' . $data['out_trade_no'] . '%'];
        }
        
        // 交易号搜索
        if (isset($data['trade_no']) && $data['trade_no'] != '') {
            $condition[] = ['trade_no', 'like', '%' . $data['trade_no'] . '%'];
        }
        
        // 交付方式搜索
        if (isset($data['delivery_method']) && $data['delivery_method'] !== '') {
            $condition[] = ['delivery_method', '=', $data['delivery_method']];
        }
        
        // 是否评价搜索
        if (isset($data['is_evaluate']) && $data['is_evaluate'] !== '') {
            $condition[] = ['is_evaluate', '=', $data['is_evaluate']];
        }
        
        // 退款状态搜索
        if (isset($data['refund_status']) && $data['refund_status'] !== '') {
            $condition[] = ['refund_status', '=', $data['refund_status']];
        }
        
        // 是否退款中搜索（根据refunding_count判断）
        if (isset($data['is_refunding']) && $data['is_refunding'] !== '') {
            if ($data['is_refunding'] == '1') {
                // 退款中：refunding_count > 0
                $condition[] = ['refunding_count', '>', 0];
            } else {
                // 非退款中：refunding_count = 0
                $condition[] = ['refunding_count', '=', 0];
            }
        }
        
        // 支付金额区间搜索
        if (isset($data['pay_amount_min']) && $data['pay_amount_min'] !== '') {
            $condition[] = ['pay_amount', '>=', $data['pay_amount_min']];
        }
        if (isset($data['pay_amount_max']) && $data['pay_amount_max'] !== '') {
            $condition[] = ['pay_amount', '<=', $data['pay_amount_max']];
        }

        $result = $this->dao->getWithRelOrderPages($condition);

        if (!empty($result['data'])) {
            foreach ($result['data'] as $key => $value) {
                $result['data'][$key]['user_available_actions'] = (new DeshangTblOrderService())->getUserAvailableActions($value);
                $result['data'][$key]['store_available_actions'] = (new DeshangTblOrderService())->getStoreAvailableActions($value);
                $result['data'][$key]['display_status_desc'] = (new DeshangTblOrderService())->getDisplayStatusDesc($value);
            }
        }

        return $result;
    }


    public function getTblOrderInfo($id): array
    {
        $result = $this->dao->getWithRelOrderInfo(['id' => $id]);

        if (!empty($result)) {
            $result['user_available_actions'] = (new DeshangTblOrderService())->getUserAvailableActions($result);
            $result['store_available_actions'] = (new DeshangTblOrderService())->getStoreAvailableActions($result);
            $result['display_status_desc'] = (new DeshangTblOrderService())->getDisplayStatusDesc($result);
        }

        return $result;
    }

    /**
     * 修改订单金额
     */
    public function modifyTblOrderAmount(array $data)
    {
        $order = $this->dao->getOrderInfoById($data['order_id']);
        if (!$order) {
            throw new CommonException('订单不存在');
        }

        return (new DeshangTblOrderService())->changeTblOrderAmount($order, 'store', (int) $this->user_id, $data);
    }

    public function getTblOrderGoodsList($data): array
    {
        $condition = [];
        if ($data['order_id'] > 0) {
            $condition['order_id'] = $data['order_id'];
        }
        if ($data['goods_id'] > 0) {
            $condition['goods_id'] = $data['goods_id'];
        }
        $result = (new TblOrderGoodsDao())->getOrderGoodsList($condition);
        return $result;
    }
    public function getTblOrderGoodsPages($data): array
    {
        $condition = [];
        if ($data['order_id'] > 0) {
            $condition['order_id'] = $data['order_id'];
        }
        if ($data['goods_id'] > 0) {
            $condition['goods_id'] = $data['goods_id'];
        }
        
        // 用户名搜索
        if (isset($data['username']) && $data['username'] != '') {
            $userIds = SearchHelper::getUserIdsByUsername($data['username']);
            $condition[] = ['user_id', 'in', $userIds];
        }
        
        $result = (new TblOrderGoodsDao())->getOrderGoodsPages($condition);
        return $result;
    }

    public function getTblOrderLogList($data): array
    {
        $result = (new TblOrderLogDao())->getOrderLogList(['order_id' => $data['order_id']]);
        return $result;
    }
    // 获取订单支付记录列表 
    // 订单id 和 合并支付订单id 二选一
    public function getTblOrderPayLogList($data): array
    {


        $condition = [];
        if ($data['order_id']) {
            $condition = [
                'source_id' => $data['order_id'],
                'source_type' => TradePayEnum::SOURCE_TYPE_ORDER,
            ];
        }

        if ($data['order_merge_id']) {
            $condition = [
                'source_id' => $data['order_merge_id'],
                'source_type' => TradePayEnum::SOURCE_TYPE_ORDER_MERGE,
            ];
        }



        $result = (new TradePayLogModel())->where($condition)->select()->toArray();
        return $result;
    }
}
