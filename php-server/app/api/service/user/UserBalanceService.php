<?php



namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;



use app\common\dao\user\UserBalanceLogDao;

class UserBalanceService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();

    }




    // 获取用户余额记录
    function getUserBalanceLogPages($data){

        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];

        $result = (new UserBalanceLogDao())->getWithRelBalanceLogPages($condition);
        return $result;

    }






}