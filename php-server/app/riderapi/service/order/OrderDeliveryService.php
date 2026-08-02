<?php

namespace app\riderapi\service\order;

use app\deshang\base\service\BaseRiderService;

use app\common\model\order\TblOrderDeliveryModel;
use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderLogDao;
use app\common\dao\order\TblOrderDeliveryDao;
use app\common\enum\order\TblOrderEnum;
use app\common\enum\order\TblOrderDeliveryEnum;
use app\deshang\exceptions\CommonException;

use think\facade\Db;

class OrderDeliveryService extends BaseRiderService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getRiderAvailableActions(array $order_delivery_info): array
    {
        $actions = [];
        // 待分配的交付订单
        if ($order_delivery_info['delivery_status'] == TblOrderDeliveryEnum::DELIVERY_STATUS_PENDING_ALLOCATION) {
            // 抢单
            $actions[] = 'grab';
        }

        // 到达中 骑手接单后，是到达中 状态
        if ($order_delivery_info['delivery_status'] == TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVING) {
            // 已到店
            $actions[] = 'arrived';
            // 取消接单
            $actions[] = 'cancel';
        }

        // 已到店
        if ($order_delivery_info['delivery_status'] == TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVED) {
            // 取货 
            $actions[] = 'pickup';
            // 取消接单
            $actions[] = 'cancel';
        }

        // 进行中(配送中/服务中)
        if ($order_delivery_info['delivery_status'] == TblOrderDeliveryEnum::DELIVERY_STATUS_PROCESSING) {
            // 确认送达
            $actions[] = 'complete';
        }

        return $actions;
    }



    // 骑手接单列表(order_delivery未分配 rider_id 的订单)
    public function getWorkingOrderDeliveryList(array $data)
    {
        // 验证配送员位置参数
        if (empty($data['longitude']) || empty($data['latitude'])) {
            throw new CommonException('经纬度参数错误');
        }

        $where = [];
        // 交付方式为骑手配送
        $where[] = ['o.delivery_method', '=', TblOrderEnum::DELIVERY_RIDER];

        // 计算店铺距离的SQL表达式(单位：米)
        $store_distance = ',(2 * 6378137* ASIN(SQRT(POW(SIN(PI()*(' . $data['latitude'] . '-oa.shipper_latitude)/360),2)+COS(PI()*' . $data['latitude'] . '/180)* COS(oa.shipper_latitude * PI()/180)*POW(SIN(PI()*(' . $data['longitude'] . '-oa.shipper_longitude)/360),2)))) as store_distance';

        // 计算用户距离的SQL表达式(单位：米)
        $buyer_distance = ',(2 * 6378137* ASIN(SQRT(POW(SIN(PI()*(' . $data['latitude'] . '-oa.reciver_latitude)/360),2)+COS(PI()*' . $data['latitude'] . '/180)* COS(oa.reciver_latitude * PI()/180)*POW(SIN(PI()*(' . $data['longitude'] . '-oa.reciver_longitude)/360),2)))) as buyer_distance';


        switch ($data['tab_selected']) {
            case 'grab':
                // 抢单列表
                $where = [
                    ['od.rider_id', '=', 0],
                    ['od.delivery_status', '=', TblOrderDeliveryEnum::DELIVERY_STATUS_PENDING_ALLOCATION],
                    ['o.refunding_count', '=', 0],
                    ['o.delivery_method', '=', TblOrderEnum::DELIVERY_RIDER],
                    ['o.order_status', 'in', [TblOrderEnum::ORDER_STATUS_ACCEPTED, TblOrderEnum::ORDER_STATUS_CONFIRMED]],
                    // ['oa.shipper_longitude', '<>', ''],
                    // ['oa.shipper_latitude', '<>', '']
                ];
                $order_by = 'store_distance asc';
                break;
            case 'pickup':
                // 待取货列表
                $where = [
                    ['od.rider_id', '=', $this->rider_id],
                    ['od.delivery_status', 'in', [TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVING, TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVED]],
                ];
                $order_by = 'store_distance asc';
                break;
            case 'delivery':
                // 待送达列表
                $where = [
                    ['od.rider_id', '=', $this->rider_id],
                    ['od.delivery_status', '=', TblOrderDeliveryEnum::DELIVERY_STATUS_PROCESSING],
                ];
                $order_by = 'buyer_distance asc';
                break;
            default:
                throw new CommonException('状态参数错误');
        }

        // 查询订单配送表和关联表
        $model = (new TblOrderDeliveryModel)->alias('od')
            ->join('tbl_order o', 'o.id = od.order_id')
            ->join('tbl_order_address oa', 'o.id = oa.order_id')
            ->field('od.*, oa.* '  . $store_distance . $buyer_distance)
            ->where($where)
            ->order($order_by); // 按距离排序，最近的优先
        $order_delivery_list = $this->getPaginate($model);


        if (!empty($order_delivery_list)) {
            foreach ($order_delivery_list['data'] as $key => $value) {
                $order_delivery_list['data'][$key]['rider_available_actions'] = $this->getRiderAvailableActions($value);
            }
        }

        return $order_delivery_list;
    }


    // 骑手接单
    public function grabOrderDelivery(array $data)
    {
        $order_info = $this->checkOrderPermission($data['order_id']);


        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_RIDER],
            ['order_id', '=', $data['order_id']],
            ['rider_id', '=', 0]
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('配送信息不存在');
        }
        // 判断订单是否可以接单
        $actions = $this->getRiderAvailableActions($order_delivery_info);
        if (!in_array('grab', $actions)) {
            throw new CommonException('订单不能接单');
        }


        // 事务处理
        Db::startTrans();
        try {


            // 更新配送信息
            $update_data = [
                'rider_id' => $this->rider_id,
                // 到达中
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVING,
            ];
            (new TblOrderDeliveryDao())->updateOrderDelivery([['order_id', '=', $data['order_id']]], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVING,
                'message' => '骑手接单',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'rider',
                'create_by' => $this->rider_id,
            );
            (new TblOrderLogDao())->createOrderLog($order_log_data);


            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }



        return true;
    }


    // 骑手取消接单
    public function cancelOrderDelivery(array $data)
    {
        $order_info = $this->checkOrderPermission($data['order_id']);


        // 判断订单是否可以取消
        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_RIDER],
            ['rider_id', '=', $this->rider_id],
            ['order_id', '=', $data['order_id']],
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('订单交付信息不存在');
        }


        $actions = $this->getRiderAvailableActions($order_delivery_info);
        if (!in_array('cancel', $actions)) {
            throw new CommonException('订单不能取消');
        }



        // 事务处理
        Db::startTrans();
        try {



            // 更新配送信息
            $update_data = [
                'rider_id' => 0,
                // 待分配
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_PENDING_ALLOCATION,
            ];
            (new TblOrderDeliveryDao())->updateOrderDelivery([['rider_id', '=', $this->rider_id], ['order_id', '=', $data['order_id']]], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_PENDING_ALLOCATION,
                'message' => '骑手取消接单',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'rider',
                'create_by' => $this->rider_id,
            );
            (new TblOrderLogDao())->createOrderLog($order_log_data);



            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }





        return true;
    }


    // 骑手到达
    public function arrivedOrderDelivery(array $data)
    {
        $order_info = $this->checkOrderPermission($data['order_id']);


        // 判断订单是否可以到达
        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_RIDER],
            ['rider_id', '=', $this->rider_id],
            ['order_id', '=', $data['order_id']],
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('订单交付信息不存在');
        }
        // 判断订单是否可以到达
        $actions = $this->getRiderAvailableActions($order_delivery_info);
        if (!in_array('arrived', $actions)) {
            throw new CommonException('订单不能到达');
        }





        // 事务处理
        Db::startTrans();
        try {


            // 更新配送信息
            $update_data = [
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVED,
            ];
            (new TblOrderDeliveryDao())->updateOrderDelivery([['rider_id', '=', $this->rider_id], ['order_id', '=', $data['order_id']]], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVED,
                'message' => '骑手已到店',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'rider',
                'create_by' => $this->rider_id,
            );
            (new TblOrderLogDao())->createOrderLog($order_log_data);



            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }




        return true;
    }


    // 骑手取货
    public function pickupOrderDelivery(array $data)
    {
        $order_info = $this->checkOrderPermission($data['order_id']);
        // 判断订单是否可以到达
        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_RIDER],
            ['rider_id', '=', $this->rider_id],
            ['order_id', '=', $data['order_id']],
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('订单交付信息不存在');
        }
        // 判断订单是否可以到达
        $actions = $this->getRiderAvailableActions($order_delivery_info);
        if (!in_array('pickup', $actions)) {
            throw new CommonException('订单不能取货');
        }



        // 事务处理
        Db::startTrans();
        try {

            // 更新配送信息
            $update_data = [
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_PROCESSING,
            ];
            (new TblOrderDeliveryDao())->updateOrderDelivery([['rider_id', '=', $this->rider_id], ['order_id', '=', $data['order_id']]], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_PROCESSING,
                'message' => '骑手已取货',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'rider',
                'create_by' => $this->rider_id,
            );
            (new TblOrderLogDao())->createOrderLog($order_log_data);



            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }




        return true;
    }


    // 骑手确认送达
    public function completeOrderDelivery(array $data)
    {
        $order_info = $this->checkOrderPermission($data['order_id']);
        // 判断订单是否可以到达
        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_RIDER],
            ['rider_id', '=', $this->rider_id],
            ['order_id', '=', $data['order_id']],
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('订单交付信息不存在');
        }
        // 判断订单是否可以到达
        $actions = $this->getRiderAvailableActions($order_delivery_info);
        if (!in_array('complete', $actions)) {
            throw new CommonException('订单不能确认送达');
        }




        // 事务处理
        Db::startTrans();
        try {


            // 更新配送信息
            $update_data = [
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_COMPLETED,
            ];
            (new TblOrderDeliveryDao())->updateOrderDelivery([['rider_id', '=', $this->rider_id], ['order_id', '=', $data['order_id']]], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_COMPLETED,
                'message' => '骑手确认送达',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'rider',
                'create_by' => $this->rider_id,
            );
            (new TblOrderLogDao())->createOrderLog($order_log_data);


            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }



        return true;
    }


    private function checkOrderPermission(int $order_id)
    {
        // 判断订单是否存在
        $order_info = (new TblOrderDao())->getOrderInfo([['id', '=', $order_id]]);
        if (empty($order_info)) {
            throw new CommonException('订单不存在');
        }
        // 判断订单是否有退款，以及订单状态是否可以操作
        if ($order_info['refunding_count'] > 0) {
            throw new CommonException('订单有退款，不能操作');
        }
        if ($order_info['delivery_method'] != TblOrderEnum::DELIVERY_RIDER) {
            throw new CommonException('订单类型不支持操作');
        }
        // 订单状态 已接单，已确认 都可操作，需店铺确认后才能操作
        if (!in_array($order_info['order_status'], [
            TblOrderEnum::ORDER_STATUS_ACCEPTED,
            TblOrderEnum::ORDER_STATUS_CONFIRMED,
            // 当骑手未派送完成，但是用户确认收货后，骑手也可对订单进行操作
            TblOrderEnum::ORDER_STATUS_COMPLETED
        ])) {
            throw new CommonException('当前订单状态' . TblOrderEnum::getFoodOrderStatusDesc($order_info['order_status']) . ',不支持骑手操作');
        }

        return $order_info;
    }



    // 骑手订单列表，获取已结束的配送订单，不需经纬度
    public function getOrderDeliveryPages(array $data): array
    {

        $condition = [];
        $condition[] = ['delivery_status', '=', TblOrderDeliveryEnum::DELIVERY_STATUS_COMPLETED];
        $condition[] = ['rider_id', '=', $this->rider_id];

        $order_delivery_list = (new TblOrderDeliveryDao())->getWithRelOrderDeliveryPages($condition);

        if (!empty($order_delivery_list)) {
            foreach ($order_delivery_list['data'] as $key => $value) {
                $order_delivery_list['data'][$key]['rider_available_actions'] = $this->getRiderAvailableActions($value);
            }
        }

        return $order_delivery_list;
    }

    // 骑手订单详情不需经纬度
    public function getOrderDeliveryInfo(int $order_id)
    {
        $condition = [];
        $condition[] = ['order_id', '=', $order_id];
        $condition[] = ['rider_id', '=', $this->rider_id];
        $condition[] = ['delivery_method', '=', TblOrderEnum::DELIVERY_RIDER];

        $order_delivery_info = (new TblOrderDeliveryDao())->getWithRelOrderDeliveryInfo($condition);

        if (empty($order_delivery_info)) {
            throw new CommonException('订单配送信息不存在');
        }

        //获取可操作按钮
        $order_delivery_info['rider_available_actions'] = $this->getRiderAvailableActions($order_delivery_info);

        return $order_delivery_info;
    }
}
