<?php

namespace app\api\service\distributor;

use app\deshang\base\service\BaseUserService;

use app\deshang\exceptions\CommonException;

use app\common\dao\distributor\DistributorApplyDao;
use app\common\dao\order\TblOrderDao;
use app\common\dao\user\UserDao;

use app\common\enum\order\TblOrderEnum;
use app\common\enum\distributor\DistributorApplyEnum;
use app\common\enum\distributor\DistributorEnum;

class DistributorApplyService extends BaseUserService
{


    public function __construct()
    {
        parent::__construct();

        $distributor_is_enabled = sysConfig('distributor:distributor_is_enabled');

        if(!$distributor_is_enabled){
            throw new CommonException('分销商功能未开启');
        }
    }


    // 获取分销商申请信息
    public function getDistributorApplyInfo()
    {
        $condition = array();
        $condition[] = ['user_id', '=', $this->user_id];
        $apply_info = (new DistributorApplyDao)->getDistributorApplyInfo($condition);
        return $apply_info;
    }


    // 获取分销商申请权限
    public function getDistributorApplyPermission()
    {


        // 获取成为分销商的权限
        $distributor_conditions = sysConfig('distributor:distributor_conditions');


        if ($distributor_conditions == 'none') {
            // 无需申请
            return array(
                'allow' => true,
                'reason' => '无条件可申请分销商'
            );
        } else if ($distributor_conditions == 'amount') {
            // 获取申请分销商的金额
            $distributor_apply_amount = sysConfig('distributor:distributor_apply_amount');

            // 获取当前用户总订单金额
            $condition = [];
            $condition[] = ['user_id', '=', $this->user_id];
            $condition[] = ['order_status', 'in', [TblOrderEnum::ORDER_STATUS_PAID, TblOrderEnum::ORDER_STATUS_ACCEPTED, TblOrderEnum::ORDER_STATUS_CONFIRMED, TblOrderEnum::ORDER_STATUS_COMPLETED]];

            $total_pay_amount = (new TblOrderDao)->getOrderColumnSum($condition, 'pay_amount');

            if ($total_pay_amount >= $distributor_apply_amount) {
                return array(
                    'allow' => true,
                    'reason' => '满足申请条件'
                );
            } else {
                return array(
                    'allow' => false,
                    'reason' => '不满足申请条件，还需支付' . ($distributor_apply_amount - $total_pay_amount) . '元'
                );
            }
        } else if ($distributor_conditions == 'count') {
            // 获取申请分销商的订单数量
            $distributor_apply_count = sysConfig('distributor:distributor_apply_count');

            // 获取当前用户总订单数量
            $condition = [];
            $condition[] = ['user_id', '=', $this->user_id];
            $condition[] = ['order_status', 'in', [TblOrderEnum::ORDER_STATUS_PAID, TblOrderEnum::ORDER_STATUS_ACCEPTED, TblOrderEnum::ORDER_STATUS_CONFIRMED, TblOrderEnum::ORDER_STATUS_COMPLETED]];
            $total_order_count = (new TblOrderDao)->getOrderCount($condition);

            if($total_order_count >= $distributor_apply_count){
                return array(
                    'allow'=> true,
                    'reason'=> '满足申请条件'
                );
            }else{
                return array(
                    'allow'=> false,
                    'reason'=> '不满足申请条件，还需支付'.($distributor_apply_count - $total_order_count).'次'
                );
            }
        }else{
            throw new CommonException('分销商申请条件配置错误');
        }
    }


    // 创建分销商申请
    public function createDistributorApply()
    {



        // 获取申请分销商的权限
        $apply_permission = $this->getDistributorApplyPermission();
        if(!$apply_permission['allow']){
            throw new CommonException($apply_permission['reason']);
        }

        // 创建分销商申请
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $apply_info = (new DistributorApplyDao)->getDistributorApplyInfo($condition);
        
        if($apply_info){
            // 申请通过
            if($apply_info['apply_status'] == DistributorApplyEnum::APPLY_STATUS_APPROVED){
                throw new CommonException('您已申请通过，请勿重复申请');
            }

            // 更新申请状态
            $data = array(
                'apply_status' => DistributorApplyEnum::APPLY_STATUS_PENDING,
                'apply_time' => time(),
                'apply_remark' => '分销商申请',
            );
            (new DistributorApplyDao)->updateDistributorApply($condition, $data);

            
        }else{
            // 创建申请
            $data = array(
                'user_id' => $this->user_id,
                'apply_status' => DistributorApplyEnum::APPLY_STATUS_PENDING,
                'apply_time' => time(),
                'apply_remark' => '分销商申请',
            );
            (new DistributorApplyDao)->createDistributorApply($data);
        }


        // 分销商审核
        $distributor_apply_type = sysConfig('distributor:distributor_apply_type');
        if($distributor_apply_type == 'auto'){
            // 无需审核，直接修改申请状态
            $data = array(
                'is_distributor' => 1,
                'distributor_status' => DistributorEnum::STATUS_NORMAL,
                'distributor_level_id' => 1,
                'distributor_addtime' => time(),
            );
            (new UserDao)->updateUser([['id', '=', $this->user_id]], $data);

            return array(
                'is_distributor' => true,
                'notice' => '申请成功'
            );
            
        }else if($distributor_apply_type == 'audit'){
            // 需要后台审核
            return array(
                'is_distributor' => false,
                'notice' => '提交成功等待管理员审核'
            );


        }else if($distributor_apply_type == 'manual'){
            // 需要手动添加
            throw new CommonException('分销商需要后台手动添加');
        }else{
            throw new CommonException('分销商申请类型配置错误');
        }
    }
}
