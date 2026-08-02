<?php

namespace app\api\service\distributor;

use app\deshang\base\service\BaseUserService;
use app\common\dao\distributor\DistributorOrderDao;
use app\common\enum\distributor\DistributorOrderEnum;

use app\common\dao\user\UserDao;


class DistributorService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();
    }

    // 获取分销商统计信息
    public function getDistributorStatistics()
    {

        // 分销订单数量
        $order_count = (new DistributorOrderDao)->getDistributorOrderCount([['distributor_user_id', '=', $this->user_id]]);

        // 分销订单金额
        $order_amount = (new DistributorOrderDao)->getDistributorOrderSum([['distributor_user_id', '=', $this->user_id]], 'pay_price');

        // 待结算订单
        $order_wait_count = (new DistributorOrderDao)->getDistributorOrderCount([['distributor_user_id', '=', $this->user_id], ['commission_status', '=', DistributorOrderEnum::COMMISSION_STATUS_WAIT]]);

        // 待结算佣金
        $order_wait_commission = (new DistributorOrderDao)->getDistributorOrderSum([['distributor_user_id', '=', $this->user_id], ['commission_status', '=', DistributorOrderEnum::COMMISSION_STATUS_WAIT]], 'commission_amount');

        // 一级分销商数量
        $parent1_count = (new UserDao)->getUserCount([['inviter_id', '=', $this->user_id]]);

        // 二级分销商数量
        $ids = (new UserDao)->getUserColumn([['inviter_id', '=', $this->user_id]], 'id');
        $parent2_count = (new UserDao)->getUserCount([['inviter_id', 'in', $ids]]);

        return [
            'order_count' => $order_count,
            'order_amount' => $order_amount,
            'order_wait_count' => $order_wait_count,
            'order_wait_commission' => $order_wait_commission,
            'parent1_count' => $parent1_count,
            'parent2_count' => $parent2_count,
        ];
    }



    // 获取分销商关系 1级分销商 2级分销商 1级和2级分销商
    public function getDistributorRelationPages(array $data)
    {
        $condition = [];
        // 默认1级分销商 
        switch ($data['level']) {
            case 1:
                $condition[] = ['inviter_id', '=', $this->user_id];
                break;
            case 2:
                // 先获取所有一级分销商ID
                $level1_ids = (new UserDao())->getUserColumn([['inviter_id', '=', $this->user_id]], 'id');
                $condition[] = ['inviter_id', 'in', $level1_ids];
                break;
            default:
                $condition[] = ['inviter_id', '=', $this->user_id];
                break;
        }

        $result = (new UserDao)->getUserPages($condition, 'id,username,avatar,is_distributor,distributor_balance_in,distributor_balance_out');

        return $result;
    }
}
