<?php

namespace app\api\controller\technician;
use app\deshang\base\controller\BaseApiController;

use app\api\service\technician\TechnicianService;

/**
 * @OA\Tag(name="api/technician/Technician", description="师傅管理接口")
 */
class Technician extends BaseApiController{

    /**
     * 获取师傅列表
     * @OA\Get(
     *     path="/api/technician/technicians/list",
     *     tags={"api/technician/Technician"},
     *     summary="获取师傅列表",
     *     description="获取师傅列表数据，支持按商品和位置筛选",
     *     @OA\Parameter(
     *         name="goods_id",
     *         in="query",
     *         required=false,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         required=false,
     *         description="经度",
     *         @OA\Schema(type="number", example=116.404)
     *     ),
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         required=false,
     *         description="纬度",
     *         @OA\Schema(type="number", example=39.915)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="返回数量限制，默认20",
     *         @OA\Schema(type="integer", example=20)
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
    public function getTechnicianList(){
        $data = array(
            'goods_id' => input('param.goods_id'),
            'longitude' => input('param.longitude'),
            'latitude' => input('param.latitude'),
            'limit' => input('param.limit', 20)
        );

        $this->validate($data, 'app\api\controller\technician\validate\TechnicianValidate.list');

        $result = (new TechnicianService())->getTechnicianList($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取师傅分页列表
     * @OA\Get(
     *     path="/api/technician/technicians/pages",
     *     tags={"api/technician/Technician"},
     *     summary="获取师傅分页列表",
     *     description="获取师傅分页数据，支持按商品和位置筛选",
     *     @OA\Parameter(
     *         name="goods_id",
     *         in="query",
     *         required=false,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         required=false,
     *         description="经度",
     *         @OA\Schema(type="number", example=116.404)
     *     ),
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         required=false,
     *         description="纬度",
     *         @OA\Schema(type="number", example=39.915)
     *     ),
     *     @OA\Parameter(
     *         name="page_current",
     *         in="query",
     *         required=false,
     *         description="当前页码",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         required=false,
     *         description="每页数量",
     *         @OA\Schema(type="integer", example=10)
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
    public function getTechnicianPages(){
        $data = array(
            'goods_id' => input('param.goods_id'),
            'longitude' => input('param.longitude'),
            'latitude' => input('param.latitude'),
            'page_current' => input('param.page_current', 1),
            'page_size' => input('param.page_size', 10),
        );

        $this->validate($data, 'app\api\controller\technician\validate\TechnicianValidate.pages');

        $result = (new TechnicianService())->getTechnicianPages($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取师傅详情
     * @OA\Get(
     *     path="/api/technician/technicians/{id}",
     *     tags={"api/technician/Technician"},
     *     summary="获取师傅详情",
     *     description="获取师傅详细信息，包含关联数据",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="师傅ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         required=false,
     *         description="经度",
     *         @OA\Schema(type="number", example=116.404)
     *     ),
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         required=false,
     *         description="纬度",
     *         @OA\Schema(type="number", example=39.915)
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
    public function getTechnicianDetail($id){
        $data = array(
            'id' => $id,
            'longitude' => input('param.longitude'),
            'latitude' => input('param.latitude'),
        );

        $this->validate($data, 'app\api\controller\technician\validate\TechnicianValidate.detail');

        $result = (new TechnicianService())->getTechnicianDetail($data);
        return ds_json_success('操作成功',$result);
    }

}