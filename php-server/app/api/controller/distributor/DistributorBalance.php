<?php

namespace app\api\controller\distributor;
use app\deshang\base\controller\BaseUserController;
use app\api\service\distributor\DistributorBalanceService;

/**
 * @OA\Tag(name="api/distributor/DistributorBalance", description="分销商佣金管理接口")
 */
class DistributorBalance extends BaseUserController
{

    /**
     * 获取分销商佣金记录分页
     * @OA\Get(
     *     path="/api/distributor/balance-logs/pages",
     *     tags={"api/distributor/DistributorBalance"},
     *     summary="获取分销商佣金记录分页",
     *     description="获取分销商佣金变更记录的分页列表",
     *     @OA\Parameter(
     *         name="change_type",
     *         in="query",
     *         required=false,
     *         description="变更类型",
     *         @OA\Schema(type="string", example="commission")
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
    public function getDistributorBalanceLogPages(){
        $data = array(
            'change_type' => input('param.change_type'),
        );

        $this->validate($data, 'app\api\controller\distributor\validate\DistributorBalanceValidate.pages');

        $data = (new DistributorBalanceService())->getDistributorBalanceLogPages($data);
        return ds_json_success('操作成功',$data);
    }

    /**
     * 分销商佣金转至余额
     * @OA\Post(
     *     path="/api/distributor/balance/to-user",
     *     tags={"api/distributor/DistributorBalance"},
     *     summary="分销商佣金转至余额",
     *     description="将分销商佣金转换为用户余额",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"change_amount"},
     *             @OA\Property(property="change_amount", type="number", description="转换金额", example=100.00)
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
    public function distributorBalanceToUser(){
        $data = array(
            'change_amount' => input('param.change_amount'),
        );
        
        $this->validate($data, 'app\api\controller\distributor\validate\DistributorBalanceValidate.toUser');
        
        $data = (new DistributorBalanceService())->distributorBalanceToUser($data);
        return ds_json_success('操作成功',$data);
    }

}
