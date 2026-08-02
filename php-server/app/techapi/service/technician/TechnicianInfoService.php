<?php

namespace app\techapi\service\technician;

use app\deshang\base\service\BaseTechnicianService;

use app\common\dao\technician\TechnicianDao;

use app\common\enum\technician\TechnicianEnum;
use app\deshang\exceptions\CommonException;

class TechnicianInfoService extends BaseTechnicianService
{

    public function __construct()
    {
        parent::__construct();
    }


    // 获取师傅信息
    public function getTechnicianInfo(){
        $result = (new TechnicianDao())->getTechnicianInfo([['id', '=', $this->technician_id]]);

        if (!empty($result['slide_image'])) $result['slide_image'] = explode(',', $result['slide_image']);


        return $result;
    }

    // 更新师傅信息
    public function updateTechnicianInfo($data){

        $data['slide_image'] = implode(',', $data['slide_image']);

        $result = (new TechnicianDao())->updateTechnician([['id', '=', $this->technician_id]], $data);
        return $result;
    }



    // 设置师傅状态
    public function setTechnicianStatus($data)
    {
        if (!array_key_exists($data['technician_status'], TechnicianEnum::getTechnicianStatusDict())) {
            throw new CommonException('状态错误');
        }
        $update_data = [
            'technician_status' => $data['technician_status'],
        ];
        $result = (new TechnicianDao())->updateTechnician([['id', '=', $this->technician_id]], $update_data);
        return $result;
    }
}
