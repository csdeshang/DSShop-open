<?php

namespace app\adminapi\service\order;

use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderDeliveryDao;
use app\common\enum\order\TblOrderEnum;
use app\deshang\base\service\BaseAdminService;
use app\deshang\exceptions\CommonException;
use app\deshang\service\order\DeshangTblOrderDeliveryService;
use think\facade\Db;

class TblOrderDeliveryService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建订单交付
     */
    public function addTblOrderDelivery(array $data)
    {
        Db::startTrans();
        try {
            $order_info = (new TblOrderDao())->getOrderInfoById($data['order_id']);
            if (empty($order_info)) {
                throw new CommonException('订单不存在');
            }
            if ($order_info['delivery_method'] == TblOrderEnum::DELIVERY_TECHNICIAN) {
                throw new CommonException('上门服务交付由师傅进行交付，后台不对订单流程进行处理');
            }
            $result = (new DeshangTblOrderDeliveryService())->addTblOrderDelivery(
                $order_info,
                $data,
                'admin',
                (int) $this->user_id
            );
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }

        event('SysNoticeListener', [
            'key' => 'user_order_delivery',
            'receiver_params' => [
                'user_id' => $order_info['user_id'],
            ],
            'template_params' => [
                'store_name' => '平台商城',
                'order_sn' => $order_info['order_sn'],
                'order_amount' => $order_info['pay_amount'],
                'delivery_time' => date('Y年m月d日 H:i', time()),
            ],
        ]);

        return $result;
    }

    public function getTblOrderDeliveryPages(array $data): array
    {
        $condition = [];
        if (isset($data['rider_id']) && $data['rider_id'] != '') {
            $condition['rider_id'] = $data['rider_id'];
        }
        if (isset($data['technician_id']) && $data['technician_id'] != '') {
            $condition['technician_id'] = $data['technician_id'];
        }
        if (isset($data['delivery_method']) && $data['delivery_method'] !== ''
            && array_key_exists($data['delivery_method'], TblOrderEnum::getAllOrderDeliveryDict())) {
            $condition['delivery_method'] = $data['delivery_method'];
        }

        return (new TblOrderDeliveryDao())->getWithRelOrderDeliveryPages($condition);
    }
}
