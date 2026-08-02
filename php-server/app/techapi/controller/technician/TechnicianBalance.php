<?php

namespace app\techapi\controller\technician;
use app\deshang\base\controller\BaseTechnicianController;

use app\techapi\service\technician\TechnicianBalanceService;

/**
 * @OA\Tag(name="techapi/technician/TechnicianBalance", description="师傅余额管理接口")
 */
class TechnicianBalance extends BaseTechnicianController{

    /**
     * 获取师傅余额流水
     * @OA\Get(
     *     path="/techapi/technician/balance-logs/pages",
     *     tags={"techapi/technician/TechnicianBalance"},
     *     summary="获取师傅余额流水",
     *     description="获取师傅余额变动流水分页列表",
     *     @OA\Parameter(
     *         name="change_type",
     *         in="query",
     *         required=false,
     *         description="变动类型",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="change_mode",
     *         in="query",
     *         required=false,
     *         description="变动模式",
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
    public function getTechnicianBalanceLogPages()
    {
        $data = array(
            'change_type' => input('param.change_type'),
            'change_mode' => input('param.change_mode'),
        );

        // 验证器
        $this->validate($data, 'app\techapi\controller\technician\validate\TechnicianBalanceValidate.pages');

        $res = (new TechnicianBalanceService())->getTechnicianBalanceLogPages($data);
        return ds_json_success('操作成功', $res);
    }

    /**
     * 师傅余额转至用户余额
     * @OA\Post(
     *     path="/techapi/technician/balance/to-user",
     *     tags={"techapi/technician/TechnicianBalance"},
     *     summary="师傅余额转至用户余额",
     *     description="将师傅余额转换为用户余额",
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
    public function technicianBalanceToUser()
    {
        $data = array(
            'change_amount' => input('param.change_amount'),
        );

        // 验证器
        $this->validate($data, 'app\techapi\controller\technician\validate\TechnicianBalanceValidate.toUser');

        $res = (new TechnicianBalanceService())->technicianBalanceToUser($data);
        return ds_json_success('操作成功', $res);
    }
}
