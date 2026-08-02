<?php

namespace app\techapi\controller\technician;
use app\deshang\base\controller\BaseTechnicianController;

use app\techapi\service\technician\TechnicianTrackService;

/**
 * @OA\Tag(name="techapi/technician/TechnicianTrack", description="师傅轨迹管理接口")
 */
class TechnicianTrack extends BaseTechnicianController{

    /**
     * 获取师傅轨迹列表
     * @OA\Get(
     *     path="/techapi/technician/tracks/list",
     *     tags={"techapi/technician/TechnicianTrack"},
     *     summary="获取师傅轨迹列表",
     *     description="获取师傅在指定时间段内的轨迹列表",
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
    public function getTechnicianTrackList(){
        $data = array(
            'start_time' => input('param.start_time'),
            'end_time' => input('param.end_time'),
        );

        // 验证器
        $this->validate($data, 'app\techapi\controller\technician\validate\TechnicianTrackValidate.list');

        $result = (new TechnicianTrackService())->getTechnicianTrackList($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取师傅轨迹分页列表
     * @OA\Get(
     *     path="/techapi/technician/tracks/pages",
     *     tags={"techapi/technician/TechnicianTrack"},
     *     summary="获取师傅轨迹分页列表",
     *     description="获取师傅在指定时间段内的轨迹分页列表",
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
    public function getTechnicianTrackPages(){
        $data = array(
            'start_time' => input('param.start_time'),
            'end_time' => input('param.end_time'),
        );

        // 验证器
        $this->validate($data, 'app\techapi\controller\technician\validate\TechnicianTrackValidate.pages');

        $result = (new TechnicianTrackService())->getTechnicianTrackPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 上报师傅轨迹
     * @OA\Post(
     *     path="/techapi/technician/tracks",
     *     tags={"techapi/technician/TechnicianTrack"},
     *     summary="上报师傅轨迹",
     *     description="师傅上报当前位置轨迹信息",
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
    public function reportTechnicianTrack(){
        $data = array(
            'address' => input('param.address',''),
            'longitude' => input('param.longitude',0),
            'latitude' => input('param.latitude',0),
        );

        // 验证器
        $this->validate($data, 'app\techapi\controller\technician\validate\TechnicianTrackValidate.report');

        $result = (new TechnicianTrackService())->reportTechnicianTrack($data);
        return ds_json_success('操作成功', $result);
    }
}