<?php



namespace app\api\service\distributor;

use app\deshang\base\service\BaseUserService;

use think\facade\Db;

use app\common\dao\distributor\DistributorBalanceLogDao;

use app\common\enum\distributor\DistributorBalanceEnum;
use app\common\enum\user\UserBalanceEnum;


use app\deshang\service\distributor\DeshangDistributorBalanceService;
use app\deshang\service\user\DeshangUserBalanceService;



use app\deshang\exceptions\CommonException;

class DistributorBalanceService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();
    }

    // 获取分销商佣金记录
    function getDistributorBalanceLogPages($data)
    {

        $condition = [];
        $condition[] = ['distributor_user_id', '=', $this->user_id];

        if(array_key_exists($data['change_type'], DistributorBalanceEnum::getChangeTypeDict())){
            $condition[] = ['change_type', '=', $data['change_type']];
        }

        $result = (new DistributorBalanceLogDao())->getWithRelDistributorBalanceLogPages($condition);
        return $result;
    }


    // 分销商佣金转至余额
    public function distributorBalanceToUser(array $data)
    {



        // 扣除分销商佣金
        $distributor_balance_data = array(
            'distributor_user_id' => $this->user_id,
            'related_id' => 0,
            'change_mode' => DistributorBalanceEnum::MODE_DECREASE,
            'change_type' => DistributorBalanceEnum::TYPE_DISTRIBUTOR_TO_USER,
            'change_amount' => $data['change_amount'],
            'change_desc' => '转账至用户余额',
        );
        // 增加用户余额
        $user_balance_data = [
            'user_id' => $this->user_id,
            'related_id' => 0,
            'change_mode' => UserBalanceEnum::MODE_INCREASE,
            'change_type' => UserBalanceEnum::TYPE_DISTRIBUTOR_TO_USER,
            'change_amount' => $data['change_amount'],
            'change_desc' => '收到来至分销商的转账',
        ];
        Db::startTrans();
        try {
            (new DeshangDistributorBalanceService())->modifyDistributorBalance($distributor_balance_data);
            (new DeshangUserBalanceService())->modifyUserBalance($user_balance_data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }
    }
}
