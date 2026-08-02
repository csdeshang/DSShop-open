<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;

use app\api\service\user\UserVideoCommentService;

/**
 * @OA\Tag(name="api/user/UserVideoComment", description="用户视频评论接口")
 */
class UserVideoComment extends BaseUserController{


    /**
     * 创建视频评论
     * @OA\Post(
     *     path="/api/user/video-comments",
     *     tags={"api/user/UserVideoComment"},
     *     summary="创建视频评论",
     *     description="对指定内容创建评论（短视频/短剧）",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content_type", "content_id", "comment_content"},
     *             @OA\Property(property="content_type", type="string", description="内容类型", example="video_short"),
     *             @OA\Property(property="content_id", type="integer", description="内容ID", example=1),
     *             @OA\Property(property="comment_content", type="string", description="评论内容", example="这个视频很棒！"),
     *             @OA\Property(property="pid", type="integer", description="父评论ID，默认0", example=0)
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
    public function createVideoComment(){
        $data = array(
            'content_type' => input('param.content_type'),
            'content_id' => input('param.content_id',0 ,'int'),
            'comment_content' => input('param.comment_content'),
            'pid' => input('param.pid',0 ,'int'),
        );
        
        // 数据验证
        $this->validate($data, 'app\api\controller\user\validate\UserVideoCommentValidate.create');
        
        $result = (new UserVideoCommentService())->createVideoComment($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 评论点赞/取消点赞
     * @OA\Patch(
     *     path="/api/user/video-comment-likes/{id}",
     *     tags={"api/user/UserVideoComment"},
     *     summary="评论点赞/取消点赞",
     *     description="对指定评论进行点赞或取消点赞操作",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="评论ID",
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
    public function toggleVideoCommentLike($id){
        // 数据验证
        $this->validate(['id' => $id], 'app\api\controller\user\validate\UserVideoCommentValidate.toggleLike');
        
        $result = (new UserVideoCommentService())->toggleVideoCommentLike($id);
        return ds_json_success('操作成功',$result);
    }
    
}