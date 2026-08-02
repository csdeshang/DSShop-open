<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;

use app\api\service\user\UserVideoLikeService;

/**
 * @OA\Tag(name="api/user/UserVideoLike", description="用户视频点赞接口")
 */
class UserVideoLike extends BaseUserController
{

    /**
     * 点赞/取消点赞
     * @OA\Post(
     *     path="/api/user/video-likes",
     *     tags={"api/user/UserVideoLike"},
     *     summary="点赞/取消点赞",
     *     description="对短剧、短视频进行点赞或取消点赞操作",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content_type", "content_id"},
     *             @OA\Property(property="content_type", type="string", description="内容类型", example="video_short"),
     *             @OA\Property(property="content_id", type="integer", description="内容ID", example=1)
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
    public function toggleVideoLike() {

        $data = array(
            'content_type' => input('param.content_type'),
            'content_id' => input('param.content_id'),
        );

        $this->validate($data, 'app\api\controller\user\validate\UserVideoLikeValidate.toggle');

        $result = (new UserVideoLikeService())->toggleVideoLike($data);
        return ds_json_success('操作成功',$result);

    }
}
