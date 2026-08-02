<?php

namespace app\riderapi\controller\rider;

use app\deshang\base\controller\BaseRiderController;
use app\riderapi\service\rider\RiderCommentService;

/**
 * @OA\Tag(name="riderapi/rider/RiderComment", description="骑手评论管理接口")
 */
class RiderComment extends BaseRiderController
{
    /**
     * 获取骑手评论分页列表
     * @OA\Get(
     *     path="/riderapi/rider/comments/pages",
     *     tags={"riderapi/rider/RiderComment"},
     *     summary="获取骑手评论分页列表",
     *     description="获取当前骑手的评论列表，支持分页和筛选",
     *     @OA\Parameter(
     *         name="is_reply",
     *         in="query",
     *         required=false,
     *         description="是否回复 0未回复 1已回复",
     *         @OA\Schema(type="integer", enum={0, 1})
     *     ),
     *     @OA\Parameter(
     *         name="is_show",
     *         in="query",
     *         required=false,
     *         description="是否显示 0隐藏 1显示",
     *         @OA\Schema(type="integer", enum={0, 1})
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
    public function getRiderCommentPages()
    {
        $data = [
            'is_reply' => input('param.is_reply', ''),
            'is_show' => input('param.is_show', ''),
        ];

        // 验证器
        $this->validate($data, 'app\riderapi\controller\rider\validate\RiderCommentValidate.pages');

        $result = (new RiderCommentService())->getRiderCommentPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取骑手评论详情
     * @OA\Get(
     *     path="/riderapi/rider/comments/{id}",
     *     tags={"riderapi/rider/RiderComment"},
     *     summary="获取骑手评论详情",
     *     description="获取指定评论的详细信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="评论ID",
     *         @OA\Schema(type="integer")
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
    public function getRiderCommentInfo($id)
    {
        $data = [
            'id' => (int)$id,
        ];

        // 验证器
        $this->validate($data, 'app\riderapi\controller\rider\validate\RiderCommentValidate.info');

        $result = (new RiderCommentService())->getRiderCommentInfo($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 回复骑手评论
     * @OA\Post(
     *     path="/riderapi/rider/comments/{id}/reply",
     *     tags={"riderapi/rider/RiderComment"},
     *     summary="回复骑手评论",
     *     description="骑手回复用户的评论",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="评论ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="回复数据",
     *         @OA\JsonContent(
     *             required={"reply_content"},
     *             @OA\Property(property="reply_content", type="string", description="回复内容", example="感谢您的评价，我会继续努力提供更好的服务")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="回复成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="回复成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function replyRiderComment($id)
    {
        $data = [
            'id' => (int)$id,
            'reply_content' => input('param.reply_content'),
        ];

        // 验证器
        $this->validate($data, 'app\riderapi\controller\rider\validate\RiderCommentValidate.reply');

        $result = (new RiderCommentService())->replyRiderComment($data);
        return ds_json_success('回复成功', $result);
    }
}
