<?php



namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;



use app\common\dao\user\UserRechargeLogDao;

class UserRechargeService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();

    }




    // 获取用户充值记录
    function getUserRechargeLogPages($data){

        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];

        $result = (new UserRechargeLogDao())->getWithRelRechargeLogPages($condition);
        return $result;

    }






}