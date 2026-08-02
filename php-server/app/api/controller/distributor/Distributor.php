<?php

namespace app\api\controller\distributor;
use app\deshang\base\controller\BaseUserController;
use app\api\service\distributor\DistributorService;

/**
 * @OA\Tag(name="api/distributor/Distributor", description="分销商管理接口")
 */
class Distributor extends BaseUserController
{

    /**
     * 获取分销商统计信息
     * @OA\Get(
     *     path="/api/distributor/statistics",
     *     tags={"api/distributor/Distributor"},
     *     summary="获取分销商统计信息",
     *     description="获取当前分销商的统计数据",
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
    public function getDistributorStatistics(){
        $result = (new DistributorService())->getDistributorStatistics();
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取分销商关系分页
     * @OA\Get(
     *     path="/api/distributor/relations/pages",
     *     tags={"api/distributor/Distributor"},
     *     summary="获取分销商关系分页",
     *     description="获取分销商关系的分页列表",
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         required=false,
     *         description="层级",
     *         @OA\Schema(type="integer", example=1)
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
    public function getDistributorRelationPages(){

        $data = array(
            // 层级
            'level' => input('param.level'),
        );

        $result = (new DistributorService())->getDistributorRelationPages($data);
        return ds_json_success('操作成功',$result);
    }

}