<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserRechargeService;

/**
 * @OA\Tag(name="api/user/UserRecharge", description="用户充值管理接口")
 */
class UserRecharge extends BaseUserController
{

    /**
     * @OA\Get(
     *     path="/api/user/recharge-logs/pages",
     *     tags={"api/user/UserRecharge"},
     *     summary="获取用户充值记录",
     *     description="获取当前用户的充值记录列表",
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
    public function getUserRechargeLogPages(){
        $data = array(

        );
        $data = (new UserRechargeService())->getUserRechargeLogPages($data);
        return ds_json_success('操作成功',$data);
    }

}
