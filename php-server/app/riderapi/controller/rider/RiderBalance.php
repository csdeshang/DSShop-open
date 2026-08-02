<?php

namespace app\riderapi\controller\rider;
use app\deshang\base\controller\BaseRiderController;

use app\riderapi\service\rider\RiderBalanceService;

/**
 * @OA\Tag(name="riderapi/rider/RiderBalance", description="骑手余额管理接口")
 */
class RiderBalance extends BaseRiderController{

    /**
     * 获取骑手余额流水
     * @OA\Get(
     *     path="/riderapi/rider/balance-logs/pages",
     *     tags={"riderapi/rider/RiderBalance"},
     *     summary="获取骑手余额流水分页",
     *     description="获取骑手余额变更记录的分页列表",
     *     @OA\Parameter(
     *         name="change_type",
     *         in="query",
     *         required=false,
     *         description="变更类型",
     *         @OA\Schema(type="string", example="income")
     *     ),
     *     @OA\Parameter(
     *         name="change_mode",
     *         in="query",
     *         required=false,
     *         description="变更模式",
     *         @OA\Schema(type="string", example="delivery")
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
    public function getRiderBalanceLogPages()
    {
        $data = array(
            'change_type' => input('param.change_type'),
            'change_mode' => input('param.change_mode'),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\rider\validate\RiderBalanceValidate.pages');

        $res = (new RiderBalanceService())->getRiderBalanceLogPages($data);
        return ds_json_success('操作成功', $res);
    }

    /**
     * 骑手余额转至用户余额
     * @OA\Post(
     *     path="/riderapi/rider/balance/to-user",
     *     tags={"riderapi/rider/RiderBalance"},
     *     summary="骑手余额转至用户余额",
     *     description="将骑手余额转换为用户余额",
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
    public function riderBalanceToUser()
    {
        $data = array(
            'change_amount' => input('param.change_amount'),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\rider\validate\RiderBalanceValidate.toUser');

        $res = (new RiderBalanceService())->riderBalanceToUser($data);
        return ds_json_success('操作成功', $res);
    }

}
