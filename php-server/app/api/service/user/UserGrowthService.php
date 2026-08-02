<?php



namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;



use app\common\dao\user\UserGrowthLogDao;

class UserGrowthService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();

    }




    // 获取用户成长记录
    function getUserGrowthLogPages($data){

        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];

        $result = (new UserGrowthLogDao())->getWithRelGrowthLogPages($condition);
        return $result;

    }






}