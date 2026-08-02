<?php

namespace app\api\service\user;

use think\facade\Db;
use app\deshang\base\service\BaseUserService;
use app\deshang\exceptions\CommonException;
use app\common\dao\pointsGoods\PointsGoodsOrderDao;
use app\common\dao\pointsGoods\PointsGoodsOrderLogDao;
use app\deshang\service\pointsGoods\DeshangPointsGoodsOrderService;


/**
 * 用户积分商品订单服务
 */
class UserPointsGoodsOrderService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取积分商品订单分页列表
     * @param array $data 请求数据
     * @return array 分页数据
     */
    public function getPointsGoodsOrderPages(array $data): array
    {
        $orderStatus = $data['order_status'] ?? '';

        // 构建查询条件
        $condition = [
            ['user_id', '=', $this->user_id],
            ['is_deleted', '=', 0]
        ];

        if (!empty($orderStatus)) {
            $condition[] = ['order_status', '=', $orderStatus];
        }

        // 获取分页数据
        $result = (new PointsGoodsOrderDao())->getPointsGoodsOrderPages($condition);

        // 处理订单数据
        foreach ($result['data'] as &$order) {
            $order['user_available_actions'] = (new DeshangPointsGoodsOrderService())->getUserAvailableActions($order);
        }

        return $result;
    }

    /**
     * 获取积分商品订单详情
     * @param int $orderId 订单ID
     * @return array 订单详情
     */
    public function getPointsGoodsOrderDetail(int $orderId): array
    {
        $order = (new PointsGoodsOrderDao())->getPointsGoodsOrderInfoById($orderId);
        if (empty($order)) {
            throw new CommonException('订单不存在');
        }

        // 验证订单归属
        if ($order['user_id'] != $this->user_id) {
            throw new CommonException('无权限访问该订单');
        }

        $order['user_available_actions'] = (new DeshangPointsGoodsOrderService())->getUserAvailableActions($order);
        
        // 获取订单日志
        $orderLogs = (new PointsGoodsOrderLogDao())->getPointsGoodsOrderLogList(
            [['order_id', '=', $orderId]],
            '*',
            'create_at asc'
        );
        $order['order_log_list'] = $orderLogs;
        
        return $order;
    }

    /**
     * 取消积分商品订单
     * @param int $orderId 订单ID
     * @return bool 结果
     */
    public function cancelPointsGoodsOrder(int $orderId): bool
    {
        $order = (new PointsGoodsOrderDao())->getPointsGoodsOrderInfoById($orderId);
        if (empty($order)) {
            throw new CommonException('订单不存在');
        }

        // 验证订单归属
        if ($order['user_id'] != $this->user_id) {
            throw new CommonException('无权限操作该订单');
        }

        // 使用通用服务处理取消订单
        Db::startTrans();
        try {
            $result = (new DeshangPointsGoodsOrderService())->cancelPointsGoodsOrder($order, 'user', $this->user_id);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }

        
        return $result;
    }

    /**
     * 确认收货
     * @param int $orderId 订单ID
     * @return bool 结果
     */
    public function confirmPointsGoodsOrder(int $orderId): bool
    {
        $order = (new PointsGoodsOrderDao())->getPointsGoodsOrderInfoById($orderId);
        if (empty($order)) {
            throw new CommonException('订单不存在');
        }

        // 验证订单归属
        if ($order['user_id'] != $this->user_id) {
            throw new CommonException('无权限操作该订单');
        }

        Db::startTrans();
        try {
            // 使用通用服务处理确认收货
            $result = (new DeshangPointsGoodsOrderService())->confirmPointsGoodsOrder($order, 'user', $this->user_id);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }

        return $result;
    }


}
