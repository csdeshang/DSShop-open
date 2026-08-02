<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserLevelService;

/**
 * @OA\Tag(name="api/user/UserLevel", description="用户等级接口")
 */
class UserLevel extends BaseUserController
{
    /**
     * 获取用户等级信息
     * @OA\Get(
     *     path="/api/user/level/info",
     *     tags={"api/user/UserLevel"},
     *     summary="获取用户等级信息",
     *     description="获取当前登录用户的等级信息、进度等",
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
    public function getUserLevelInfo()
    {
        $result = (new UserLevelService())->getUserLevelInfo();
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取等级列表
     * @OA\Get(
     *     path="/api/user/level/list",
     *     tags={"api/user/UserLevel"},
     *     summary="获取等级列表",
     *     description="获取所有会员等级列表",
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
    public function getUserLevelList()
    {
        $result = (new UserLevelService())->getUserLevelList();
        return ds_json_success('操作成功', $result);
    }
}
