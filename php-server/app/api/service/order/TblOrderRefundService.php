<?php

namespace app\api\service\order;

use app\deshang\base\service\BaseUserService;

use app\deshang\service\order\DeshangTblOrderRefundService;



use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderRefundDao;

use app\deshang\exceptions\CommonException;
use app\deshang\exceptions\NotFoundException;


use think\facade\Db;



// 订单退款 公共模块  处理多种平台订单退款管理 , 如需针对不同平台 订单退款 进行处理， 可以重写相关方法
class TblOrderRefundService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();
        $this->dao = new TblOrderRefundDao();
    }
    // 获取订单退款列表
    public function getTblOrderRefundPages(array $data)
    {
        $condition = [];

        if (isset($data['refund_status']) && $data['refund_status'] != '') {
            $condition['refund_status'] = $data['refund_status'];
        }
        $condition['user_id'] = $this->user_id;
        $result = $this->dao->getOrderRefundPages($condition);
        return $result;
    }


    // 获取订单退款详情
    public function getTblOrderRefundInfo(int $id): array
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = ['id', '=', $id];
        $result = $this->dao->getWithRelOrderRefundInfo($condition);
        if (!$result) {
            throw new NotFoundException('退款不存在');
        }

        $result['store_refund_actions'] = (new DeshangTblOrderRefundService())->getStoreRefundActions($result);
        $result['user_refund_actions'] = (new DeshangTblOrderRefundService())->getUserRefundActions($result);
        return $result;
    }


    // 申请退款
    public function applyTblOrderRefund(array $data)
    {
        $order_info = (new TblOrderDao())->getOrderInfoById($data['order_id']);
        if (!$order_info) {
            throw new NotFoundException('订单不存在');
        }
        if ($order_info['user_id'] != $this->user_id) {
            throw new CommonException('订单不属于当前用户');
        }
        // 事务处理
        Db::startTrans();
        try {
            // 创建退款
            $result = (new DeshangTblOrderRefundService())->applyRefund($order_info, $data, 'user', $this->user_id);
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

    // 修改退款信息
    public function editTblOrderRefund(array $data)
    {
        $condition = [];
        $condition[] = ['id', '=', $data['id']];
        $condition[] = ['user_id', '=', $this->user_id];


        $refund_info = (new TblOrderRefundDao())->getOrderRefundInfo($condition);
        if (!$refund_info) {
            throw new NotFoundException('退款不存在');
        }

        // 事务处理
        Db::startTrans();
        try {
            // 修改退款信息
            $result = (new DeshangTblOrderRefundService())->editRefund($refund_info, $data, 'user', $this->user_id);
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }





        return $result;
    }

    // 用户退货
    public function userReturnGoods(array $data)
    {
        $condition = [];
        $condition[] = ['id', '=', $data['id']];
        $condition[] = ['user_id', '=', $this->user_id];
        $refund_info = (new TblOrderRefundDao())->getOrderRefundInfo($condition);
        if (!$refund_info) {
            throw new NotFoundException('退款不存在');
        }


        // 事务处理
        Db::startTrans();
        try {
            // 用户退货
            $result = (new DeshangTblOrderRefundService())->userReturnGoods($refund_info, $data, $this->user_id);
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }


        return $result;
    }

    // 取消退款
    public function cancelTblOrderRefund(int $id)
    {
        $condition = [];
        $condition[] = ['id', '=', $id];
        $condition[] = ['user_id', '=', $this->user_id];
        $refund_info = (new TblOrderRefundDao())->getOrderRefundInfo($condition);
        if (!$refund_info) {
            throw new NotFoundException('退款不存在');
        }


        // 事务处理
        Db::startTrans();
        try {
            // 取消退款
            $result = (new DeshangTblOrderRefundService())->cancelRefund($refund_info, 'user', $this->user_id);
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }


        return $result;
    }
}
