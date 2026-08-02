<?php

namespace app\api\controller\video;
use app\deshang\base\controller\BaseApiController;

use app\api\service\video\VideoCommentService;

/**
 * @OA\Tag(name="api/video/VideoComment", description="视频评论接口")
 */
class VideoComment extends BaseApiController{

    /**
     * 获取视频评论分页
     * @OA\Get(
     *     path="/api/video/comments/pages",
     *     tags={"api/video/VideoComment"},
     *     summary="获取视频评论分页",
     *     description="获取指定内容的评论分页列表（短视频/短剧）",
     *     @OA\Parameter(
     *         name="content_type",
     *         in="query",
     *         required=true,
     *         description="内容类型",
     *         @OA\Schema(type="string", example="video_short")
     *     ),
     *     @OA\Parameter(
     *         name="content_id",
     *         in="query",
     *         required=true,
     *         description="内容ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="pid",
     *         in="query",
     *         required=false,
     *         description="父评论ID，默认0",
     *         @OA\Schema(type="integer", example=0)
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
    public function getVideoCommentPages(){
        $data = array(
            'content_type' => input('param.content_type'),
            'content_id' => input('param.content_id',0 ,'int'),
            'pid' => input('param.pid',0 ,'int'),
        );
        
        $this->validate($data, 'app\api\controller\video\validate\VideoCommentValidate.pages');
        
        $result = (new VideoCommentService())->getVideoCommentPages($data);
        return ds_json_success('操作成功',$result);
    }


}