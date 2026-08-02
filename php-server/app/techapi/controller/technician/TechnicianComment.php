<?php

namespace app\techapi\controller\technician;

use app\deshang\base\controller\BaseTechnicianController;
use app\techapi\service\technician\TechnicianCommentService;

/**
 * @OA\Tag(name="techapi/technician/TechnicianComment", description="师傅评论管理接口")
 */
class TechnicianComment extends BaseTechnicianController
{
    /**
     * 获取师傅评论分页列表
     * @OA\Get(
     *     path="/techapi/technician/comments/pages",
     *     tags={"techapi/technician/TechnicianComment"},
     *     summary="获取师傅评论分页列表",
     *     description="获取当前师傅的评论列表，支持分页和筛选",
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
    public function getTechnicianCommentPages()
    {
        $data = [
            'is_reply' => input('param.is_reply', ''),
            'is_show' => input('param.is_show', ''),
        ];

        // 验证器
        $this->validate($data, 'app\techapi\controller\technician\validate\TechnicianCommentValidate.pages');

        $result = (new TechnicianCommentService())->getTechnicianCommentPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取师傅评论详情
     * @OA\Get(
     *     path="/techapi/technician/comments/{id}",
     *     tags={"techapi/technician/TechnicianComment"},
     *     summary="获取师傅评论详情",
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
    public function getTechnicianCommentInfo($id)
    {
        // 验证器
        $this->validate(['id' => $id], 'app\techapi\controller\technician\validate\TechnicianCommentValidate.info');

        $result = (new TechnicianCommentService())->getTechnicianCommentInfo($id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 回复师傅评论
     * @OA\Post(
     *     path="/techapi/technician/comments/{id}/reply",
     *     tags={"techapi/technician/TechnicianComment"},
     *     summary="回复师傅评论",
     *     description="师傅回复用户的评论",
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
    public function replyTechnicianComment($id)
    {
        $data = [
            'id' => (int)$id,
            'reply_content' => input('param.reply_content'),
        ];

        // 验证器
        $this->validate($data, 'app\techapi\controller\technician\validate\TechnicianCommentValidate.reply');

        $result = (new TechnicianCommentService())->replyTechnicianComment($data);
        return ds_json_success('回复成功', $result);
    }
}
