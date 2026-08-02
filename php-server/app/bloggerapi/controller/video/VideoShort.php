<?php

namespace app\bloggerapi\controller\video;

use app\deshang\base\controller\BaseBloggerController;
use app\bloggerapi\service\video\VideoShortService;

/**
 * @OA\Tag(name="blogger-api/video/VideoShort", description="博主短视频管理接口")
 */
class VideoShort extends BaseBloggerController
{
    /**
     * @OA\Post(
     *     path="/bloggerapi/video/shorts",
     *     summary="发布短视频",
     *     tags={"blogger-api/video/VideoShort"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="短视频发布数据",
     *         @OA\JsonContent(
     *             required={"type", "title", "description"},
     *             @OA\Property(property="type", type="integer", example=1, description="内容类型：1-视频 2-图片"),
     *             @OA\Property(property="title", type="string", example="精彩短视频", description="视频标题"),
     *             @OA\Property(property="description", type="string", example="这是一个很棒的视频", description="视频描述"),
     *             @OA\Property(property="cid", type="integer", example=1, description="分类ID"),
     *             @OA\Property(property="cover_image", type="string", example="cover.jpg", description="封面图片"),
     *             @OA\Property(property="video_url", type="string", example="video.mp4", description="视频文件URL（type=1时必需）"),
     *             @OA\Property(property="images_url", type="array", @OA\Items(type="string"), example={"image1.jpg", "image2.jpg"}, description="图片URL数组（type=2时必需）"),
     *             @OA\Property(property="goods_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}, description="关联商品ID数组")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="发布成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="integer", example=123, description="新创建的短视频ID")
     *         )
     *     )
     * )
     */
    public function publishVideoShort()
    {
        $data = [
            'type' => input('param.type', ''),
            'title' => input('param.title', ''),
            'description' => input('param.description', ''),
            'cid' => input('param.cid', ''),
            'cover_image' => input('param.cover_image', ''),
            'video_url' => input('param.video_url', ''),
            'images_url' => input('param.images_url/a', []),
            'goods_ids' => input('param.goods_ids/a', []),
        ];

        $data['images_url'] = implode(',', $data['images_url']);

        // 验证器
        $this->validate($data, 'app\bloggerapi\controller\video\validate\VideoShortValidate.publish');

        $result = (new VideoShortService())->publishVideoShort($data);
        return ds_json_success('操作成功', $result);
    }


    /**
     * @OA\Get(
     *     path="/bloggerapi/video/shorts/pages",
     *     summary="获取博主短视频分页列表",
     *     tags={"blogger-api/video/VideoShort"},
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         required=false,
     *         description="视频标题",
     *         @OA\Schema(type="string", example="精彩短视频")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         description="内容类型：1-视频 2-图片",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="audit_status",
     *         in="query",
     *         required=false,
     *         description="审核状态：0-待审核 1-审核通过 2-审核拒绝",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="cid",
     *         in="query",
     *         required=false,
     *         description="分类ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page_current",
     *         in="query",
     *         required=false,
     *         description="当前页码，默认1",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         required=false,
     *         description="每页数量，默认10",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(    
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="page_current", type="integer", example=1),
     *                 @OA\Property(property="page_size", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=100)
     *             )
     *         )
     *     )
     * )
     */
    public function getVideoShortPages()
    {
        $data = [
            'title' => input('param.title', ''),
            'type' => input('param.type', ''),
            'audit_status' => input('param.audit_status', ''),
            'cid' => input('param.cid', ''),
        ];

        $result = (new VideoShortService())->getVideoShortPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Delete(
     *     path="/bloggerapi/video/shorts/{id}",
     *     summary="删除短视频",
     *     tags={"blogger-api/video/VideoShort"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="短视频ID",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="删除成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="删除成功"),
     *             @OA\Property(property="data", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function deleteVideoShort($id)
    {
        $this->validate(['id' => $id], 'app\bloggerapi\controller\video\validate\VideoShortValidate.delete');

        $result = (new VideoShortService())->deleteVideoShort($id);
        return ds_json_success('删除成功', $result);
    }


   

}
