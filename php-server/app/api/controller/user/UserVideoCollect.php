<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;

use app\api\service\user\UserVideoCollectService;

/**
 * @OA\Tag(name="api/user/UserVideoCollect", description="用户视频收藏接口")
 */
class UserVideoCollect extends BaseUserController
{

    /**
     * 收藏/取消收藏
     * @OA\Post(
     *     path="/api/user/video-collects",
     *     tags={"api/user/UserVideoCollect"},
     *     summary="收藏/取消收藏",
     *     description="对短剧、短视频进行收藏或取消收藏操作",
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
    public function toggleVideoCollect() {

        $data = array(
            'content_type' => input('param.content_type'),
            'content_id' => input('param.content_id'),
        );

        $this->validate($data, 'app\api\controller\user\validate\UserVideoCollectValidate.toggle');

        $result = (new UserVideoCollectService())->toggleVideoCollect($data);
        return ds_json_success('操作成功',$result);

    }
}
