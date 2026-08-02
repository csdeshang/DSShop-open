<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserBloggerFollowService;

/**
 * @OA\Tag(name="api/user/UserBloggerFollow", description="用户博主关注管理接口")
 */
class UserBloggerFollow extends BaseUserController
{

    /**
     * 关注/取消关注博主
     * @OA\Patch(
     *     path="/api/user/blogger-follows/{id}",
     *     tags={"api/user/UserBloggerFollow"},
     *     summary="关注/取消关注博主",
     *     description="对指定博主进行关注或取消关注操作",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="博主ID",
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
    public function toggleBloggerFollow($id) {
        $this->validate(['id' => $id], 'app\api\controller\user\validate\UserBloggerFollowValidate.toggle');

        $result = (new UserBloggerFollowService())->toggleBloggerFollow($id);
        return ds_json_success('操作成功',$result);
    }

}
