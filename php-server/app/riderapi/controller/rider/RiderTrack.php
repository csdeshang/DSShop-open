<?php

namespace app\riderapi\controller\rider;
use app\deshang\base\controller\BaseRiderController;

use app\riderapi\service\rider\RiderTrackService;

/**
 * @OA\Tag(name="riderapi/rider/RiderTrack", description="骑手轨迹管理接口")
 */
class RiderTrack extends BaseRiderController{

    /**
     * 获取骑手轨迹列表
     * @OA\Get(
     *     path="/riderapi/rider/tracks/list",
     *     tags={"riderapi/rider/RiderTrack"},
     *     summary="获取骑手轨迹列表",
     *     description="获取骑手在指定时间段内的轨迹列表",
     *     @OA\Parameter(
     *         name="start_time",
     *         in="query",
     *         required=false,
     *         description="开始时间",
     *         @OA\Schema(type="string", example="2024-01-01 00:00:00")
     *     ),
     *     @OA\Parameter(
     *         name="end_time",
     *         in="query",
     *         required=false,
     *         description="结束时间",
     *         @OA\Schema(type="string", example="2024-01-01 23:59:59")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getRiderTrackList(){
        $data = array(
            'start_time' => input('param.start_time'),
            'end_time' => input('param.end_time'),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\rider\validate\RiderTrackValidate.list');

        $result = (new RiderTrackService())->getRiderTrackList($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取骑手轨迹分页列表
     * @OA\Get(
     *     path="/riderapi/rider/tracks/pages",
     *     tags={"riderapi/rider/RiderTrack"},
     *     summary="获取骑手轨迹分页列表",
     *     description="获取骑手在指定时间段内的轨迹分页列表",
     *     @OA\Parameter(
     *         name="start_time",
     *         in="query",
     *         required=false,
     *         description="开始时间",
     *         @OA\Schema(type="string", example="2024-01-01 00:00:00")
     *     ),
     *     @OA\Parameter(
     *         name="end_time",
     *         in="query",
     *         required=false,
     *         description="结束时间",
     *         @OA\Schema(type="string", example="2024-01-01 23:59:59")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getRiderTrackPages(){
        $data = array(
            'start_time' => input('param.start_time'),
            'end_time' => input('param.end_time'),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\rider\validate\RiderTrackValidate.pages');

        $result = (new RiderTrackService())->getRiderTrackPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 上报骑手轨迹
     * @OA\Post(
     *     path="/riderapi/rider/tracks",
     *     tags={"riderapi/rider/RiderTrack"},
     *     summary="上报骑手轨迹",
     *     description="骑手上报当前位置轨迹信息",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"longitude", "latitude"},
     *             @OA\Property(property="address", type="string", description="位置地址", example="北京市朝阳区"),
     *             @OA\Property(property="longitude", type="number", description="经度", example=116.397128),
     *             @OA\Property(property="latitude", type="number", description="纬度", example=39.916527)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function reportRiderTrack(){
        $data = array(
            'address' => input('param.address',''),
            'longitude' => input('param.longitude',0),
            'latitude' => input('param.latitude',0),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\rider\validate\RiderTrackValidate.report');

        $result = (new RiderTrackService())->reportRiderTrack($data);
        return ds_json_success('操作成功', $result);
    }
}
