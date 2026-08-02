<?php

namespace app\api\service\order;

use app\deshang\base\service\BaseUserService;

use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderRefundDao;
use app\common\dao\order\TblOrderDeliveryDao;
use app\common\dao\rider\RiderDao;
use app\common\dao\technician\TechnicianDao;

use app\deshang\service\order\DeshangTblOrderService;
use app\deshang\service\order\DeshangTblOrderRefundService;

use app\deshang\exceptions\CommonException;
use app\deshang\exceptions\NotFoundException;
use app\common\enum\order\TblOrderEnum;
use app\common\enum\order\TblOrderRefundEnum;


use think\facade\Db;


class TblOrderService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();
    }

    // 获取订单列表
    public function getTblOrderPages($data)
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];

        if (isset($data['order_sn']) && $data['order_sn'] != '') {
            $condition[] = ['order_sn', 'like', '%' . $data['order_sn'] . '%'];
        }

        // 使用映射表优化 tab_selected 条件筛选（参考技师端实现）
        $tabConditions = [
            'pending' => [
                // 待付款
                ['order_status', '=', TblOrderEnum::ORDER_STATUS_PENDING]
            ],
            'accepted' => [
                // 待收货/使用（已付款、已发货或已确认）
                [
                    'order_status',
                    'in',
                    [
                        TblOrderEnum::ORDER_STATUS_PAID,
                        TblOrderEnum::ORDER_STATUS_ACCEPTED,
                        TblOrderEnum::ORDER_STATUS_CONFIRMED
                    ]
                ]
            ],
            'comment' => [
                // 待评价（已完成、未评价、无退款中、非全额退款，与 evaluate action 一致）
                ['order_status', '=', TblOrderEnum::ORDER_STATUS_COMPLETED],
                ['is_evaluate', '=', 0],
                ['refunding_count', '=', 0],
                ['refund_status', '<>', TblOrderEnum::REFUND_STATUS_FULL_REFUNDED],
            ],
            'refund' => [
                // 退款（有退款中的订单）
                ['refunding_count', '>', 0]
            ]
        ];

        // 根据tab_selected添加对应的条件（空字符串不在映射表中，会自动跳过，表示查询全部）
        if (isset($data['tab_selected']) && isset($tabConditions[$data['tab_selected']])) {
            $condition = array_merge($condition, $tabConditions[$data['tab_selected']]);
        }

        $result = (new TblOrderDao)->getWithRelOrderPages($condition);


        if (!empty($result)) {
            foreach ($result['data'] as $key => $order_info) {
                $result['data'][$key]['user_available_actions'] = (new DeshangTblOrderService)->getUserAvailableActions($order_info);
                $result['data'][$key]['store_available_actions'] = (new DeshangTblOrderService)->getStoreAvailableActions($order_info);
                $result['data'][$key]['display_status_desc'] = (new DeshangTblOrderService)->getDisplayStatusDesc($order_info);
            }
        }

        return $result;
    }


    // 获取订单详情
    public function getTblOrderInfo($id)
    {

        $condition = [];
        $condition[] = ['id', '=', $id];
        $condition[] = ['user_id', '=', $this->user_id];

        $result = (new TblOrderDao)->getWithRelOrderInfo($condition);


        if (!empty($result)) {
            $result['user_available_actions'] = (new DeshangTblOrderService)->getUserAvailableActions($result);
            $result['store_available_actions'] = (new DeshangTblOrderService)->getStoreAvailableActions($result);
            $result['display_status_desc'] = (new DeshangTblOrderService)->getDisplayStatusDesc($result);
        }

        return $result;
    }


    // 用户关闭订单  未付款则可关闭订单
    public function closeTblOrder($data)
    {
        // 事务处理
        Db::startTrans();
        try {
            // 获取订单信息[加锁]
            $order_info = (new TblOrderDao)->getOrderInfo([
                ['id', '=', $data['order_id']],
                ['user_id', '=', $this->user_id]
            ], '*', true);
            // 订单是否存在
            if (empty($order_info)) {
                throw new NotFoundException('订单不存在');
            }
            // 取消订单
            $result = (new DeshangTblOrderService)->changeTblOrderClose($order_info, 'user', $this->user_id);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }

        return $result;
    }


    // 用户取消订单  已付款，未发货 则可取消订单，涉及到退款
    public function cancelTblOrder($data)
    {
        // 事务处理
        Db::startTrans();
        try {
            // 获取订单信息(取消和退款分两个流程处理)
            $order_info = (new TblOrderDao)->getOrderInfo([
                ['id', '=', $data['order_id']],
                ['user_id', '=', $this->user_id]
            ], '*', true);
            // 订单是否存在
            if (empty($order_info)) {
                throw new NotFoundException('订单不存在');
            }
            // 取消订单
            $refund_id = (new DeshangTblOrderService)->changeTblOrderCancel($order_info, 'user', $this->user_id);


            // 考虑数据一致性， 获取最新退款信息
            $refund_info = (new TblOrderRefundDao())->getOrderRefundInfo(['id' => $refund_id, 'user_id' => $this->user_id]);
            if (empty($refund_info)) {
                throw new NotFoundException('退款信息不存在');
            }
            // 处理退款 
            $result = (new DeshangTblOrderRefundService)->processRefund($refund_info, 'user', $this->user_id);

            // 触发订单取消事件 [PHP事件为同步执行，没有线程池]
            event('OrderCancelListener', [
                'order_info' => $order_info,
            ]);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }





        return $result;
    }




    // 用户确认收货
    public function confirmTblOrder($data)
    {
        // 事务处理
        Db::startTrans();
        try {
            // 获取订单信息
            $order_info = (new TblOrderDao)->getOrderInfo([
                ['id', '=', $data['order_id']],
                ['user_id', '=', $this->user_id]
            ], '*', true);
            // 订单是否存在
            if (empty($order_info)) {
                throw new NotFoundException('订单不存在');
            }
            // 确认订单
            $result = (new DeshangTblOrderService)->changeTblOrderConfirm($order_info, 'user', $this->user_id);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }
        return $result;
    }


    // 获取订单状态数量
    public function getTblOrderStatusCount()
    {
        // 待付款
        $result['pending_count'] = (new TblOrderDao)->getOrderCount([['user_id', '=', $this->user_id], ['order_status', '=', TblOrderEnum::ORDER_STATUS_PENDING]]);



        // 待发货
        $result['paid_count'] = (new TblOrderDao)->getOrderCount([['user_id', '=', $this->user_id], ['order_status', '=', TblOrderEnum::ORDER_STATUS_PAID]]);

        // 待收货
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = [
            [
                'order_status',
                '=',
                TblOrderEnum::ORDER_STATUS_ACCEPTED
            ],
            [
                'order_status',
                '=',
                TblOrderEnum::ORDER_STATUS_CONFIRMED
            ]
        ];
        $result['accepted_count'] = (new TblOrderDao)->getOrderCount($condition);

        // 待评价（与评价入口一致：已完成、未评价、无退款中、非全额退款）
        $result['comment_count'] = (new TblOrderDao)->getOrderCount([
            ['user_id', '=', $this->user_id],
            ['order_status', '=', TblOrderEnum::ORDER_STATUS_COMPLETED],
            ['is_evaluate', '=', 0],
            ['refunding_count', '=', 0],
            ['refund_status', '<>', TblOrderEnum::REFUND_STATUS_FULL_REFUNDED],
        ]);

        // 退货退款
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = [
            'refund_status',
            'in',
            [
                TblOrderRefundEnum::STATUS_USER_APPLYING,
                TblOrderRefundEnum::STATUS_STORE_APPLYING,
                TblOrderRefundEnum::STATUS_STORE_AGREED,
                TblOrderRefundEnum::STATUS_STORE_REJECTED,
                TblOrderRefundEnum::STATUS_USER_RETURNED,
                TblOrderRefundEnum::STATUS_STORE_RECEIVED,
                TblOrderRefundEnum::STATUS_REFUND_PROCESSING,
            ]
        ];
        $result['refund_count'] = (new TblOrderRefundDao)->getOrderRefundCount($condition);


        return $result;
    }

    // 获取订单配送员信息（包含最新位置）
    public function getTblOrderRiderInfo($orderId)
    {
        // 验证订单是否属于当前用户
        $order_info = (new TblOrderDao)->getOrderInfoById($orderId);
        if (!$order_info) {
            throw new NotFoundException('订单不存在');
        }

        if ($order_info['user_id'] != $this->user_id) {
            throw new CommonException('订单不属于当前用户');
        }

        // 从订单交付表获取配送员信息
        $delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([['order_id', '=', $orderId]]);

        if (!$delivery_info) {
            throw new NotFoundException('订单交付信息不存在');
        }

        // 检查订单是否有配送员
        if (empty($delivery_info['rider_id'])) {
            throw new CommonException('该订单暂无配送员');
        }

        // 获取配送员信息（包含最新位置）
        $rider_info = (new RiderDao())->getRiderInfo([['id', '=', $delivery_info['rider_id']]], '*');
        unset($rider_info['balance']);
        unset($rider_info['balance_in']);
        unset($rider_info['balance_out']);

        if (!$rider_info) {
            throw new NotFoundException('配送员信息不存在');
        }

        return $rider_info;
    }

    // 获取订单师傅信息（包含最新位置）
    public function getTblOrderTechnicianInfo($orderId)
    {
        // 验证订单是否属于当前用户
        $order_info = (new TblOrderDao)->getOrderInfoById($orderId);
        if (!$order_info) {
            throw new NotFoundException('订单不存在');
        }

        if ($order_info['user_id'] != $this->user_id) {
            throw new CommonException('订单不属于当前用户');
        }

        // 从订单交付表获取师傅信息
        $delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([['order_id', '=', $orderId]]);

        if (!$delivery_info) {
            throw new NotFoundException('订单交付信息不存在');
        }

        // 检查订单是否有师傅
        if (empty($delivery_info['technician_id'])) {
            throw new CommonException('该订单暂无师傅');
        }

        // 获取师傅信息（包含最新位置）
        $technician_info = (new TechnicianDao())->getTechnicianInfo([['id', '=', $delivery_info['technician_id']]], '*');
        unset($technician_info['balance']);
        unset($technician_info['balance_in']);
        unset($technician_info['balance_out']);


        if (!$technician_info) {
            throw new NotFoundException('师傅信息不存在');
        }

        return $technician_info;
    }
}
