<?php

namespace app\bloggerapi\controller\video;

use app\deshang\base\controller\BaseBloggerController;
use app\bloggerapi\service\video\VideoCommentService;

/**
 * @OA\Tag(name="blogger-api/video/VideoComment", description="博主视频评论管理接口")
 */
class VideoComment extends BaseBloggerController
{
    /**
     * @OA\Patch(
     *     path="/bloggerapi/video/comments/{id}/toggle-field",
     *     summary="切换视频评论字段状态",
     *     tags={"blogger-api/video/VideoComment"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="评论ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="切换数据",
     *         @OA\JsonContent(
     *             required={"field"},
     *             @OA\Property(property="field", type="string", example="is_top", description="字段名(is_top-置顶, is_deleted-删除)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="切换成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="切换成功")
     *         )
     *     )
     * )
     */
    public function toggleVideoCommentField($id)
    {
        $data = [
            'id' => $id,
            'field' => input('param.field')
        ];

        $this->validate($data, 'app\bloggerapi\controller\video\validate\VideoCommentValidate.toggleField');

        $result = (new VideoCommentService())->toggleVideoCommentField($data);
        return ds_json_success('切换成功', $result);
    }
}