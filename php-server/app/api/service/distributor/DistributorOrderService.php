<?php



namespace app\api\service\distributor;

use app\deshang\base\service\BaseUserService;
use app\common\dao\distributor\DistributorOrderDao;
use app\common\enum\distributor\DistributorOrderEnum;

class DistributorOrderService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();
    }


    // 获取分销商订单列表
    function getDistributorOrderPages($data)
    {

        $condition = [];
        $condition[] = ['distributor_user_id', '=', $this->user_id];

        if(array_key_exists($data['commission_status'], DistributorOrderEnum::getCommissionStatusDict())){
            $condition[] = ['commission_status', '=', $data['commission_status']];
        }

        $result = (new DistributorOrderDao())->getDistributorOrderPages($condition);
        return $result;
    }
}
