<?php



namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;



use app\common\dao\user\UserPointsLogDao;

class UserPointsService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();

    }




    // 获取用户积分记录
    function getUserPointsLogPages($data){

        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];

        $result = (new UserPointsLogDao())->getWithRelPointsLogPages($condition);
        return $result;

    }






}