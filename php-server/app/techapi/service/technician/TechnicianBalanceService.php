<?php

namespace app\techapi\service\technician;

use app\deshang\base\service\BaseTechnicianService;

use app\common\dao\technician\TechnicianBalanceLogDao;


use app\deshang\exceptions\CommonException;

use app\common\enum\technician\TechnicianBalanceEnum;
use app\common\enum\user\UserBalanceEnum;

use app\deshang\service\technician\DeshangTechnicianBalanceService;
use app\deshang\service\user\DeshangUserBalanceService;

use think\facade\Db;



class TechnicianBalanceService extends BaseTechnicianService
{

    public function __construct()
    {
        parent::__construct();
    }


    // 获取师傅余额流水
    public function getTechnicianBalanceLogPages(array $data)
    {
        $condition = array();
        $condition['technician_id'] = $this->technician_id;


        if (array_key_exists($data['change_type'], TechnicianBalanceEnum::getChangeTypeDict())) {
            $condition['change_type'] = $data['change_type'];
        }

        if (isset($data['change_mode']) && $data['change_mode'] != '') {
            $condition['change_mode'] = $data['change_mode'];
        }


        $res = (new TechnicianBalanceLogDao())->getTechnicianBalanceLogPages($condition);
        return $res;
    }


    // 师傅余额转至用户余额
    public function technicianBalanceToUser(array $data)
    {
        // 扣除师傅余额
        $technician_balance_data = array(
            'technician_id' => $this->technician_id,
            'related_id' => 0,
            'change_mode' => TechnicianBalanceEnum::MODE_DECREASE,
            'change_type' => TechnicianBalanceEnum::TYPE_TECHNICIAN_TO_USER,
            'change_amount' => $data['change_amount'],
            'change_desc' => '转账至用户余额',
        );
        // 增加用户余额
        $user_balance_data = [
            'user_id' => $this->user_id,
            'related_id' => 0,
            'change_mode' => UserBalanceEnum::MODE_INCREASE,
            'change_type' => UserBalanceEnum::TYPE_TECHNICIAN_TO_USER,
            'change_amount' => $data['change_amount'],
            'change_desc' => '收到来至师傅的转账',
        ];
        Db::startTrans();
        try {
            (new DeshangTechnicianBalanceService())->modifyTechnicianBalance($technician_balance_data);
            (new DeshangUserBalanceService())->modifyUserBalance($user_balance_data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }
    }
}
