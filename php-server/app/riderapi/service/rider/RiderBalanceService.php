<?php

namespace app\riderapi\service\rider;

use app\deshang\base\service\BaseRiderService;

use app\common\dao\rider\RiderBalanceLogDao;


use app\deshang\exceptions\CommonException;

use app\common\enum\rider\RiderBalanceEnum;
use app\common\enum\user\UserBalanceEnum;

use app\deshang\service\rider\DeshangRiderBalanceService;
use app\deshang\service\user\DeshangUserBalanceService;

use think\facade\Db;



class RiderBalanceService extends BaseRiderService
{

    public function __construct()
    {
        parent::__construct();
    }


    // 获取骑手余额流水
    public function getRiderBalanceLogPages(array $data)
    {
        $condition = array();
        $condition['rider_id'] = $this->rider_id;


        if (array_key_exists($data['change_type'], RiderBalanceEnum::getChangeTypeDict())) {
            $condition['change_type'] = $data['change_type'];
        }

        if (isset($data['change_mode']) && $data['change_mode'] != '') {
            $condition['change_mode'] = $data['change_mode'];
        }


        $res = (new RiderBalanceLogDao())->getRiderBalanceLogPages($condition);
        return $res;
    }


    // 骑手余额转至用户余额
    public function riderBalanceToUser(array $data)
    {
        // 扣除骑手余额
        $rider_balance_data = array(
            'rider_id' => $this->rider_id,
            'related_id' => 0,
            'change_mode' => RiderBalanceEnum::MODE_DECREASE,
            'change_type' => RiderBalanceEnum::TYPE_RIDER_TO_USER,
            'change_amount' => $data['change_amount'],
            'change_desc' => '转账至用户余额',
        );
        // 增加用户余额
        $user_balance_data = [
            'user_id' => $this->user_id,
            'related_id' => 0,
            'change_mode' => UserBalanceEnum::MODE_INCREASE,
            'change_type' => UserBalanceEnum::TYPE_RIDER_TO_USER,
            'change_amount' => $data['change_amount'],
            'change_desc' => '收到来至骑手的转账',
        ];
        Db::startTrans();
        try {
            (new DeshangRiderBalanceService())->modifyRiderBalance($rider_balance_data);
            (new DeshangUserBalanceService())->modifyUserBalance($user_balance_data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }
    }
}
