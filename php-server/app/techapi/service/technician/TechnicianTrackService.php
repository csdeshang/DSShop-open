<?php

namespace app\techapi\service\technician;

use app\deshang\base\service\BaseTechnicianService;
use app\common\dao\technician\TechnicianDao;
use app\common\dao\technician\TechnicianTrackDao;


use app\deshang\exceptions\CommonException;


// 师傅轨迹服务
class TechnicianTrackService extends BaseTechnicianService
{
    protected $technicianTrackDao;

    public function __construct()
    {
        parent::__construct();
        $this->technicianTrackDao = new TechnicianTrackDao();
    }

    // 获取师傅轨迹列表
    public function getTechnicianTrackList($data)
    {
        // 构建查询条件
        $condition = [];
        $condition[] = ['technician_id', '=', $this->technician_id];

        // 设置时间范围
        $startTime = isset($data['start_time']) && !empty($data['start_time'])
            ? intval($data['start_time'])
            : (time() - 3600 * 24 * 30); // 默认查询最近30天

        $endTime = isset($data['end_time']) && !empty($data['end_time'])
            ? intval($data['end_time'])
            : time();

        $condition[] = ['create_at', '>=', $startTime];
        $condition[] = ['create_at', '<=', $endTime];


        // 查询数据
        $result = $this->technicianTrackDao->getTechnicianTrackList($condition, '*', 'create_at desc', 1000);



        return $result;
    }

    // 获取师傅轨迹分页列表
    public function getTechnicianTrackPages($data)
    {
        // 构建查询条件
        $condition = [];
        $condition[] = ['technician_id', '=', $this->technician_id];


        // 开始时间条件
        if(isset($data['start_time']) && !empty($data['start_time'])){
            $startTime = strtotime($data['start_time']);
            $condition[] = ['create_at', '>=', $startTime];
        }

            
        // 结束时间条件：有值才添加条件，没有值则不添加
        if (isset($data['end_time']) && !empty($data['end_time'])) {
            $endTime = strtotime($data['end_time']);
            $condition[] = ['create_at', '<=', $endTime];
        }

        // 查询数据
        $result = $this->technicianTrackDao->getTechnicianTrackPages($condition, '*', 'create_at desc');

        return $result;
    }

    // 上报轨迹
    public function reportTechnicianTrack($data)
    {
        // 添加师傅ID
        $data['technician_id'] = $this->technician_id;

        // 防止频繁上报
        $lastTrack = $this->technicianTrackDao->getTechnicianTrackInfo([['technician_id', '=', $this->technician_id]], '*', 'create_at desc');

        // 如果上次上报时间在 1分钟内，则不再记录  create_at 需要转成时间戳
        if (!empty($lastTrack) && (time() - strtotime($lastTrack['create_at']) < 60 * 1)) {
            throw new CommonException('上报过于频繁，请稍后再试');
        }

        // 创建轨迹记录
        $result = $this->technicianTrackDao->createTechnicianTrack($data);

        // 更新师傅最后上报时间
        $updateData = [
            'technician_latitude' => $data['latitude'],
            'technician_longitude' => $data['longitude'],
            'technician_loc_time' => time()
        ];
        (new TechnicianDao())->updateTechnician([['id', '=', $this->technician_id]], $updateData);

        return $result;
    }



}
