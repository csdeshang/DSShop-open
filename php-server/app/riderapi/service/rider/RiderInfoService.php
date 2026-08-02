<?php

namespace app\riderapi\service\rider;

use app\deshang\base\service\BaseRiderService;

use app\common\dao\rider\RiderDao;

use app\common\enum\rider\RiderEnum;
use app\deshang\exceptions\CommonException;

class RiderInfoService extends BaseRiderService
{

    public function __construct()
    {
        parent::__construct();
    }

    // 获取骑手信息
    public function getRiderInfo(){
        $result = (new RiderDao())->getRiderInfo([['id', '=', $this->rider_id]]);
        return $result;
    }

    // 更新骑手信息
    public function updateRiderInfo($data){
        $result = (new RiderDao())->updateRider([['id', '=', $this->rider_id]], $data);
        return $result;
    }

    // 设置骑手状态
    public function setRiderStatus($data)
    {
        $status = $data['status'];

        if(!array_key_exists($data['status'], RiderEnum::getRiderStatusDict())){
            throw new CommonException('状态错误');
        }

        $update_data = [
            'status' => $data['status'],
        ];
        (new RiderDao())->updateRider([['id', '=', $this->rider_id]], $update_data);
        return true;

    }

    
    
}