<?php

namespace app\api\controller\distributor;
use app\deshang\base\controller\BaseUserController;
use app\api\service\distributor\DistributorApplyService;

/**
 * @OA\Tag(name="api/distributor/DistributorApply", description="分销商申请接口")
 */
class DistributorApply extends BaseUserController
{

    /**
     * 获取分销商申请信息
     * @OA\Get(
     *     path="/api/distributor/apply/info",
     *     tags={"api/distributor/DistributorApply"},
     *     summary="获取分销商申请信息",
     *     description="获取当前用户的分销商申请状态和信息",
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
    public function getDistributorApplyInfo(){
        $result = (new DistributorApplyService())->getDistributorApplyInfo();
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取分销商申请权限
     * @OA\Get(
     *     path="/api/distributor/apply/permission",
     *     tags={"api/distributor/DistributorApply"},
     *     summary="获取分销商申请权限",
     *     description="获取当前用户是否有申请分销商的权限",
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
    public function getDistributorApplyPermission(){
        $result = (new DistributorApplyService())->getDistributorApplyPermission();
        return ds_json_success('操作成功',$result);
    }

    /**
     * 创建分销商申请
     * @OA\Post(
     *     path="/api/distributor/apply",
     *     tags={"api/distributor/DistributorApply"},
     *     summary="创建分销商申请",
     *     description="提交分销商申请",
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
    public function createDistributorApply(){
        $result = (new DistributorApplyService())->createDistributorApply();
        return ds_json_success('操作成功',$result);
    }

}
