<?php

namespace app\api\service\system;

use app\deshang\base\service\BaseApiService;
use app\common\dao\system\SysPlatformDao;





class SysPlatformService extends BaseApiService
{

    public function __construct()
    {
        parent::__construct();
    }
    public function getSysPlatformList($data){

        // 参数预留
        $condition = [];
        if(isset($data['scene']) && $data['scene'] != ''){
            $condition['scene'] = $data['scene'];
        }

        $result = (new SysPlatformDao)->getSysPlatformList($condition);
        return $result;
    }

}