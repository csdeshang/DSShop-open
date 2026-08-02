<?php

namespace app\riderapi\service\rider;

use app\deshang\base\service\BaseRiderService;
use app\common\dao\rider\RiderDao;
use app\common\dao\rider\RiderTrackDao;

use app\deshang\exceptions\CommonException;

// 骑手轨迹服务
class RiderTrackService extends BaseRiderService
{
    protected $riderTrackDao;

    public function __construct()
    {
        parent::__construct();
        $this->riderTrackDao = new RiderTrackDao();
    }

    // 获取骑手轨迹列表
    public function getRiderTrackList($data)
    {
        // 构建查询条件
        $condition = [];
        $condition[] = ['rider_id', '=', $this->rider_id];

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
        $result = $this->riderTrackDao->getRiderTrackList($condition, '*', 'create_at desc', 1000);

        return $result;
    }

    // 获取骑手轨迹分页列表
    public function getRiderTrackPages($data)
    {
        // 构建查询条件
        $condition = [];
        $condition[] = ['rider_id', '=', $this->rider_id];

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
        $result = $this->riderTrackDao->getRiderTrackPages($condition, '*', 'create_at desc');

        return $result;
    }

    // 上报轨迹
    public function reportRiderTrack($data)
    {
        // 添加骑手ID
        $data['rider_id'] = $this->rider_id;

        // 防止频繁上报
        $lastTrack = $this->riderTrackDao->getRiderTrackInfo([['rider_id', '=', $this->rider_id]], '*', 'create_at desc');

        // 如果上次上报时间在 1分钟内，则不再记录  create_at 需要转成时间戳
        if (!empty($lastTrack) && (time() - strtotime($lastTrack['create_at']) < 60 * 1)) {
            throw new CommonException('上报过于频繁，请稍后再试');
        }

        // 创建轨迹记录
        $result = $this->riderTrackDao->createRiderTrack($data);

        // 更新骑手最后上报时间
        $updateData = [
            'rider_latitude' => $data['latitude'],
            'rider_longitude' => $data['longitude'],
            'rider_loc_time' => time()
        ];
        (new RiderDao())->updateRider([['id', '=', $this->rider_id]], $updateData);

        return $result;
    }
}
