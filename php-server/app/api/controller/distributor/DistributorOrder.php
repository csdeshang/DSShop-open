<?php

namespace app\api\controller\distributor;
use app\deshang\base\controller\BaseUserController;
use app\api\service\distributor\DistributorOrderService;

/**
 * @OA\Tag(name="api/distributor/DistributorOrder", description="分销商订单接口")
 */
class DistributorOrder extends BaseUserController
{

    /**
     * 获取分销商订单分页列表
     * @OA\Get(
     *     path="/api/distributor/orders/pages",
     *     tags={"api/distributor/DistributorOrder"},
     *     summary="获取分销商订单分页列表",
     *     description="获取分销商相关订单的分页数据",
     *     @OA\Parameter(
     *         name="commission_status",
     *         in="query",
     *         required=false,
     *         description="佣金状态",
     *         @OA\Schema(type="string", example="pending")
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
    public function getDistributorOrderPages(){
        $data = array(
            'commission_status' => input('param.commission_status'),
        );
        $data = (new DistributorOrderService())->getDistributorOrderPages($data);
        return ds_json_success('操作成功',$data);
    }

}
