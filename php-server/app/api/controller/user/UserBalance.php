<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserBalanceService;

/**
 * @OA\Tag(name="api/user/UserBalance", description="用户余额管理接口")
 */
// 用户余额记录
class UserBalance extends BaseUserController
{

    /**
     * @OA\Get(
     *     path="/api/user/balance-logs/pages",
     *     tags={"api/user/UserBalance"},
     *     summary="获取用户余额记录",
     *     description="分页获取用户余额变动记录",
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
    // 获取用户余额记录
    public function getUserBalanceLogPages(){
        $data = array(


        );
        $data = (new UserBalanceService())->getUserBalanceLogPages($data);
        return ds_json_success('操作成功',$data);
    }




}
