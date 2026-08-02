<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserGrowthService;

/**
 * @OA\Tag(name="api/user/UserGrowth", description="用户成长值管理接口")
 */
class UserGrowth extends BaseUserController
{

    /**
     * @OA\Get(
     *     path="/api/user/growth-logs/pages",
     *     tags={"api/user/UserGrowth"},
     *     summary="获取用户成长记录",
     *     description="获取当前用户的成长值记录列表",
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
    public function getUserGrowthLogPages(){
        $data = array(

        );
        $data = (new UserGrowthService())->getUserGrowthLogPages($data);
        return ds_json_success('操作成功',$data);
    }

}
