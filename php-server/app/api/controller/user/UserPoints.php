<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserPointsService;

/**
 * @OA\Tag(name="api/user/UserPoints", description="用户积分管理接口")
 */
class UserPoints extends BaseUserController
{

    /**
     * @OA\Get(
     *     path="/api/user/points-logs/pages",
     *     tags={"api/user/UserPoints"},
     *     summary="获取用户积分记录",
     *     description="获取当前用户的积分记录列表",
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
    public function getUserPointsLogPages(){
        $data = array(

        );
        $data = (new UserPointsService())->getUserPointsLogPages($data);
        return ds_json_success('操作成功',$data);
    }

}
