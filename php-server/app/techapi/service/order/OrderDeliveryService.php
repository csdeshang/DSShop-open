<?php

namespace app\techapi\service\order;

use app\deshang\base\service\BaseTechnicianService;

use app\common\model\order\TblOrderDeliveryModel;

use app\deshang\service\order\DeshangTblOrderDeliveryService;


use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderLogDao;
use app\common\dao\order\TblOrderDeliveryDao;

use app\common\dao\technician\TechnicianDao;
use app\common\enum\technician\TechnicianEnum;
use app\common\enum\order\TblOrderEnum;
use app\common\enum\order\TblOrderDeliveryEnum;

use app\deshang\exceptions\CommonException;

use think\facade\Db;

class OrderDeliveryService extends BaseTechnicianService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getTechnicianAvailableActions(array $order_delivery_info): array
    {
        $actions = [];
        // 待分配的服务订单，当未选择对应的师傅时， 师傅可以抢单
        if ($order_delivery_info['delivery_status'] == TblOrderDeliveryEnum::DELIVERY_STATUS_PENDING_ALLOCATION || $order_delivery_info['technician_id'] == 0) {
            // 抢单
            $actions[] = 'grab';
        }

        // 待师傅确认
        if ($order_delivery_info['delivery_status'] == TblOrderDeliveryEnum::DELIVERY_STATUS_COURIER_ACCEPTANCE) {
            // 分配的订单， 师傅确认(接单)
            $actions[] = 'technician_accept';
            // 取消接单
            $actions[] = 'cancel';
        }

        // 待师傅出发
        if ($order_delivery_info['delivery_status'] == TblOrderDeliveryEnum::DELIVERY_STATUS_COURIER_DEPARTURE) {
            // 出发 
            $actions[] = 'technician_departure';
            // 取消接单
            $actions[] = 'cancel';
        }

        // 师傅路途中
        if ($order_delivery_info['delivery_status'] == TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVING) {
            // 确认到达
            $actions[] = 'technician_arrived';
        }


        // 已到达
        if ($order_delivery_info['delivery_status'] == TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVED) {
            // 开始服务
            $actions[] = 'start_service';
        }

        // 服务中
        if ($order_delivery_info['delivery_status'] == TblOrderDeliveryEnum::DELIVERY_STATUS_PROCESSING) {
            // 确认完成
            $actions[] = 'complete';
        }



        return $actions;
    }


    // 获取当前师傅 需要交付的订单列表
    public function getOrderDeliveryPages(array $data): array
    {

        if ($data['tab_selected'] == 'grab') {
            // 抢单与当前师傅无关
            $order_delivery_list = $this->getGrabOrderDeliveryList($data);
        } else {
            $order_delivery_list = $this->_getOrderDeliveryPages($data);
        }

        // 获取师傅可操作的订单列表
        if (!empty($order_delivery_list)) {
            foreach ($order_delivery_list['data'] as $key => $value) {
                $order_delivery_list['data'][$key]['technician_available_actions'] = $this->getTechnicianAvailableActions($value);
            }
        }


        return $order_delivery_list;
    }


    // 获取当前师傅 需要交付的订单详情
    public function getOrderDeliveryInfo(int $order_id)
    {
        $condition = [];
        $condition[] = ['order_id', '=', $order_id];
        $condition[] = ['technician_id', '=', $this->technician_id];
        $condition[] = ['delivery_method', '=', TblOrderEnum::DELIVERY_TECHNICIAN];

        $order_delivery_info = (new TblOrderDeliveryDao())->getWithRelOrderDeliveryInfo($condition);
        
        if (empty($order_delivery_info)) {
            throw new CommonException('订单配送信息不存在');
        }

        // 获取师傅可操作的按钮
        $order_delivery_info['technician_available_actions'] = $this->getTechnicianAvailableActions($order_delivery_info);

        return $order_delivery_info;
    }

    // 获取当前可抢单的订单列表
    private function getGrabOrderDeliveryList(array $data)
    {
        // 平台自营：抢单池共享，不再按店铺过滤
        $model = (new TblOrderDeliveryModel)
            ->hasWhere('order', [
                // 交付方式
                ['delivery_method', '=', TblOrderEnum::DELIVERY_TECHNICIAN],
                // 退款状态
                ['refunding_count', '=', 0],
                // 订单状态 已付款
                ['order_status', '=', TblOrderEnum::ORDER_STATUS_PAID]
            ])
            ->where('technician_id', '=', 0)
            ->where('delivery_method', '=', TblOrderEnum::DELIVERY_TECHNICIAN)
            ->append(['delivery_status_desc'])
            ->with(
                [
                    'order' => function ($query) {
                        $query->append(['order_status_desc']);
                    },
                    'orderGoodsList' => function ($query) {
                        $query->field('*');
                    }
                ]
            )
            ->order('id', 'desc');

        $order_delivery_list = $this->getPaginate($model);

        return $order_delivery_list;
    }


    // 获取当前师傅 需要交付的订单列表
    private function _getOrderDeliveryPages(array $data): array
    {
        $condition = [];
        $condition[] = ['technician_id', '=', $this->technician_id];
        $condition[] = ['delivery_method', '=', TblOrderEnum::DELIVERY_TECHNICIAN];

        // 使用映射表优化 tab_selected 条件筛选
        $tabConditions = [
            'courier_accept' => [
                // 待师傅确认
                ['delivery_status', '=', TblOrderDeliveryEnum::DELIVERY_STATUS_COURIER_ACCEPTANCE]
            ],
            'courier_departure' => [
                // 待师傅出发
                ['delivery_status', '=', TblOrderDeliveryEnum::DELIVERY_STATUS_COURIER_DEPARTURE]
            ],
            'courier_service' => [
                // 服务中
                [
                    'delivery_status',
                    'in',
                    [
                        TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVING,
                        TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVED,
                        TblOrderDeliveryEnum::DELIVERY_STATUS_PROCESSING
                    ]
                ]
            ],
            'courier_complete' => [
                // 已完成
                ['delivery_status', '=', TblOrderDeliveryEnum::DELIVERY_STATUS_COMPLETED]
            ],
            'courier_cancel' => [
                // 已取消
                ['delivery_status', '=', TblOrderDeliveryEnum::DELIVERY_STATUS_CANCELLED]
            ]
        ];

        // 根据tab_selected添加对应的条件
        if (isset($data['tab_selected']) && isset($tabConditions[$data['tab_selected']])) {
            $condition = array_merge($condition, $tabConditions[$data['tab_selected']]);
        }

        $model = (new TblOrderDeliveryModel)
            ->where($condition)
            ->append(['delivery_status_desc'])
            ->with(
                [
                    'order' => function ($query) {
                        $query->append(['order_status_desc']);
                    },
                    'orderGoodsList' => function ($query) {
                        $query->field('*');
                    }
                ]
            )
            ->order('id', 'desc');
        $order_delivery_list = $this->getPaginate($model);
        return $order_delivery_list;
    }

    private function checkOrderPermission(int $order_id)
    {
        // 判断订单是否存在
        $order_info = (new TblOrderDao())->getOrderInfo([['id', '=', $order_id]]);
        if (empty($order_info)) {
            throw new CommonException('订单不存在');
        }
        // 判断订单是否有退款，以及订单状态是否可以接单
        if ($order_info['refunding_count'] > 0) {
            throw new CommonException('订单有退款，不能操作');
        }
        if ($order_info['delivery_method'] != TblOrderEnum::DELIVERY_TECHNICIAN) {
            throw new CommonException('订单类型不支持师傅操作');
        }
        // 订单状态 已付款，已接单，已确认,已完成(提前确认完成) 都可操作
        if (!in_array($order_info['order_status'], [
            TblOrderEnum::ORDER_STATUS_PAID,
            TblOrderEnum::ORDER_STATUS_ACCEPTED,
            TblOrderEnum::ORDER_STATUS_CONFIRMED,
            // 当师傅未完成服务，但是用户确认收货后，师傅也可对订单进行操作
            TblOrderEnum::ORDER_STATUS_COMPLETED
        ])) {
            throw new CommonException('订单状态' . TblOrderEnum::getHouseOrderStatusDesc($order_info['order_status']) . ',不支持师傅操作');
        }
        return $order_info;
    }

    private function checkTechnicianPermission(array $order_info)
    {
        $technician_info = (new TechnicianDao())->getTechnicianInfoById($this->technician_id);
        // 判断师傅是否在服务中
        if ($technician_info['is_enabled'] != 1) {
            throw new CommonException('当前师傅被禁用,不能处理订单,请联系管理员');
        }

        // 审核是否通过
        if ($technician_info['apply_status'] != TechnicianEnum::APPLY_STATUS_APPROVED) {
            throw new CommonException('当前师傅未通过审核,不能处理订单,请联系店铺管理员');
        }

        // 判断师傅是否接单状态
        if ($technician_info['technician_status'] != TechnicianEnum::TECHNICIAN_STATUS_AVAILABLE) {
            throw new CommonException('当前是休息状态，请在个人中心设置上线');
        }


        return $technician_info;
    }

    // 师傅抢单
    public function grabOrderDelivery(array $data)
    {

        $order_info = $this->checkOrderPermission($data['order_id']);

        $technician_info = $this->checkTechnicianPermission($order_info);

        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_TECHNICIAN],
            ['order_id', '=', $data['order_id']],
            ['technician_id', '=', 0]
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('服务信息不存在');
        }

        // 判断订单是否可以抢单
        $actions = $this->getTechnicianAvailableActions($order_delivery_info);
        if (!in_array('grab', $actions)) {
            throw new CommonException('订单不能抢单');
        }


        // 事务处理
        Db::startTrans();
        try {



            // 更新订单交付信息
            $update_data = [
                'technician_id' => $this->technician_id,
                // 待师傅确认
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_COURIER_ACCEPTANCE,
                'technician_fee_rate' => $technician_info['technician_fee_rate'],
                'technician_fee' => round(($order_info['pay_amount'] - $order_info['shipping_amount']) * (1 - $technician_info['technician_fee_rate']/100) + $order_info['shipping_amount'], 2),
            ];

            (new TblOrderDeliveryDao())->updateOrderDelivery([['order_id', '=', $data['order_id']]], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_COURIER_ACCEPTANCE,
                'message' => '师傅抢单',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'technician',
                'create_by' => $this->technician_id,
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


    // 师傅确认接单
    public function acceptOrderDelivery(array $data)
    {
        $order_info = $this->checkOrderPermission($data['order_id']);

        $technician_info = $this->checkTechnicianPermission($order_info);

        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_TECHNICIAN],
            ['order_id', '=', $data['order_id']],
            ['technician_id', '=', $this->technician_id]
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('服务信息不存在');
        }

        // 判断订单是否可以接单
        $actions = $this->getTechnicianAvailableActions($order_delivery_info);
        if (!in_array('technician_accept', $actions)) {
            throw new CommonException('订单不能接单');
        }




        // 事务处理
        Db::startTrans();
        try {


            // 更新订单交付信息
            $update_data = [
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_COURIER_DEPARTURE,
            ];
            (new TblOrderDeliveryDao())->updateOrderDelivery([
                ['order_id', '=', $data['order_id']],
                ['technician_id', '=', $this->technician_id]
            ], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_COURIER_DEPARTURE,
                'message' => '师傅确认接单',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'technician',
                'create_by' => $this->technician_id,
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


    // 师傅出发
    public function departureOrderDelivery(array $data)
    {

        $order_info = $this->checkOrderPermission($data['order_id']);

        $technician_info = $this->checkTechnicianPermission($order_info);

        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_TECHNICIAN],
            ['order_id', '=', $data['order_id']],
            ['technician_id', '=', $this->technician_id]
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('服务信息不存在');
        }

        // 判断订单是否可以出发
        $actions = $this->getTechnicianAvailableActions($order_delivery_info);
        if (!in_array('technician_departure', $actions)) {
            throw new CommonException('订单不能出发');
        }




        // 事务处理
        Db::startTrans();
        try {


            // 当师傅出发， 则师傅不可取消， 更新 师傅的佣金比例
            $update_data = [
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVING,
                'technician_fee_rate' => $technician_info['technician_fee_rate'],
                'technician_fee' => round(($order_info['pay_amount'] - $order_info['shipping_amount']) * (1 - $technician_info['technician_fee_rate']/100) + $order_info['shipping_amount'], 2),
            ];
            (new TblOrderDeliveryDao())->updateOrderDelivery([
                ['order_id', '=', $data['order_id']],
                ['technician_id', '=', $this->technician_id]
            ], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVING,
                'message' => '师傅出发',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'technician',
                'create_by' => $this->technician_id,
            );
            (new TblOrderLogDao())->createOrderLog($order_log_data);


            // 当师傅出发， 则用户不能取消订单, 需要走退款流程。
            $result = (new DeshangTblOrderDeliveryService())->addTblOrderDelivery(
                $order_info,
                array('delivery_method' => TblOrderEnum::DELIVERY_TECHNICIAN),
                'technician',
                $this->technician_id
            );


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


    // 师傅确认到达
    public function arrivedOrderDelivery(array $data)
    {
        $order_info = $this->checkOrderPermission($data['order_id']);

        $technician_info = $this->checkTechnicianPermission($order_info);

        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_TECHNICIAN],
            ['order_id', '=', $data['order_id']],
            ['technician_id', '=', $this->technician_id]
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('服务信息不存在');
        }

        // 判断订单是否可以确认到达
        $actions = $this->getTechnicianAvailableActions($order_delivery_info);
        if (!in_array('technician_arrived', $actions)) {
            throw new CommonException('订单不能确认到达');
        }



        // 事务处理
        Db::startTrans();
        try {

            // 更新订单交付信息
            $update_data = [
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVED,
            ];
            (new TblOrderDeliveryDao())->updateOrderDelivery([
                ['order_id', '=', $data['order_id']],
                ['technician_id', '=', $this->technician_id]
            ], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_ARRIVED,
                'message' => '师傅确认到达',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'technician',
                'create_by' => $this->technician_id,
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



    // 师傅开始服务
    public function startServiceOrderDelivery(array $data)
    {
        $order_info = $this->checkOrderPermission($data['order_id']);

        $technician_info = $this->checkTechnicianPermission($order_info);

        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_TECHNICIAN],
            ['order_id', '=', $data['order_id']],
            ['technician_id', '=', $this->technician_id]
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('服务信息不存在');
        }

        // 判断订单是否可以开始服务
        $actions = $this->getTechnicianAvailableActions($order_delivery_info);
        if (!in_array('start_service', $actions)) {
            throw new CommonException('订单不能开始服务');
        }




        // 事务处理
        Db::startTrans();
        try {


            // 更新订单交付信息
            $update_data = [
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_PROCESSING,
            ];
            (new TblOrderDeliveryDao())->updateOrderDelivery([
                ['order_id', '=', $data['order_id']],
                ['technician_id', '=', $this->technician_id]
            ], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_PROCESSING,
                'message' => '师傅开始服务',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'technician',
                'create_by' => $this->technician_id,
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


    // 师傅确认完成
    public function completeOrderDelivery(array $data)
    {
        $order_info = $this->checkOrderPermission($data['order_id']);

        $technician_info = $this->checkTechnicianPermission($order_info);

        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_TECHNICIAN],
            ['order_id', '=', $data['order_id']],
            ['technician_id', '=', $this->technician_id]
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('服务信息不存在');
        }

        // 判断订单是否可以确认完成
        $actions = $this->getTechnicianAvailableActions($order_delivery_info);
        if (!in_array('complete', $actions)) {
            throw new CommonException('订单不能确认完成');
        }




        // 事务处理
        Db::startTrans();
        try {


            // 更新订单交付信息
            $update_data = [
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_COMPLETED,
            ];
            (new TblOrderDeliveryDao())->updateOrderDelivery([
                ['order_id', '=', $data['order_id']],
                ['technician_id', '=', $this->technician_id]
            ], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_COMPLETED,
                'message' => '师傅确认完成',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'technician',
                'create_by' => $this->technician_id,
            );
            (new TblOrderLogDao())->createOrderLog($order_log_data);


            // 服务次数+1
            (new TechnicianDao())->setTechnicianInc([ 'id' => $this->technician_id], 'service_count');


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


    // 师傅取消订单
    public function cancelOrderDelivery(array $data)
    {
        $order_info = $this->checkOrderPermission($data['order_id']);

        $technician_info = $this->checkTechnicianPermission($order_info);

        $order_delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo([
            ['delivery_method', '=', TblOrderEnum::DELIVERY_TECHNICIAN],
            ['order_id', '=', $data['order_id']],
            ['technician_id', '=', $this->technician_id]
        ]);
        if (empty($order_delivery_info)) {
            throw new CommonException('服务信息不存在');
        }

        // 判断订单是否可以取消
        $actions = $this->getTechnicianAvailableActions($order_delivery_info);
        if (!in_array('cancel', $actions)) {
            throw new CommonException('订单不能取消');
        }



        // 事务处理
        Db::startTrans();
        try {


            // 更新订单交付信息
            $update_data = [
                'technician_id' => 0,
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_CANCELLED,
                'technician_fee_rate' => 0,
                'technician_fee' => 0,
            ];
            (new TblOrderDeliveryDao())->updateOrderDelivery([
                ['order_id', '=', $data['order_id']],
                ['technician_id', '=', $this->technician_id],
            ], $update_data);

            // 生成订单日志
            $order_log_data = array(
                'order_id' => $data['order_id'],
                'order_status' => $order_info['order_status'],
                'delivery_status' => TblOrderDeliveryEnum::DELIVERY_STATUS_CANCELLED,
                'message' => '师傅取消订单',
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'create_role' => 'technician',
                'create_by' => $this->technician_id,
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
}
