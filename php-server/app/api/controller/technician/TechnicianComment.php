<?php

namespace app\api\controller\technician;

use app\deshang\base\controller\BaseApiController;
use app\api\service\technician\TechnicianCommentService;

/**
 * @OA\Tag(name="api/technician/TechnicianComment", description="师傅评价查看接口")
 */
class TechnicianComment extends BaseApiController
{
    /**
     * 获取师傅评论分页列表
     * @OA\Get(
     *     path="/api/technician/comments/pages",
     *     tags={"api/technician/TechnicianComment"},
     *     summary="获取师傅评论分页列表",
     *     description="获取师傅评论分页列表，只显示公开的评论",
     *     @OA\Parameter(
     *         name="technician_id",
     *         in="query",
     *         required=false,
     *         description="师傅ID",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Parameter(
     *         name="is_reply",
     *         in="query",
     *         required=false,
     *         description="是否回复 0未回复 1已回复",
     *         @OA\Schema(type="string", enum={"0", "1"}, example="1")
     *     ),
     *     @OA\Parameter(
     *         name="service_score",
     *         in="query",
     *         required=false,
     *         description="服务评分 1-5分",
     *         @OA\Schema(type="string", enum={"1", "2", "3", "4", "5"}, example="5")
     *     ),
     *     @OA\Parameter(
     *         name="page_current",
     *         in="query",
     *         required=false,
     *         description="当前页码",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         required=false,
     *         description="每页数量",
     *         @OA\Schema(type="integer", example=10)
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
        $data = array(
            'technician_id' => input('param.technician_id'),
            'is_reply' => input('param.is_reply'),
            'service_score' => input('param.service_score'),
            'page_current' => input('param.page_current', 1),
            'page_size' => input('param.page_size', 10),
        );

        // 验证参数
        $this->validate($data, 'app\api\controller\technician\validate\TechnicianCommentValidate.pages');

        $result = (new TechnicianCommentService())->getTechnicianCommentPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取师傅评论列表
     * @OA\Get(
     *     path="/api/technician/comments/list",
     *     tags={"api/technician/TechnicianComment"},
     *     summary="获取师傅评论列表",
     *     description="获取师傅评论列表，只显示公开的评论",
     *     @OA\Parameter(
     *         name="technician_id",
     *         in="query",
     *         required=false,
     *         description="师傅ID",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Parameter(
     *         name="is_reply",
     *         in="query",
     *         required=false,
     *         description="是否回复 0未回复 1已回复",
     *         @OA\Schema(type="string", enum={"0", "1"}, example="1")
     *     ),
     *     @OA\Parameter(
     *         name="service_score",
     *         in="query",
     *         required=false,
     *         description="服务评分 1-5分",
     *         @OA\Schema(type="string", enum={"1", "2", "3", "4", "5"}, example="5")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="限制条数，最大100",
     *         @OA\Schema(type="integer", example=20)
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
    public function getTechnicianCommentList()
    {
        $data = array(
            'technician_id' => input('param.technician_id'),
            'is_reply' => input('param.is_reply'),
            'service_score' => input('param.service_score'),
            'limit' => input('param.limit', 20),
        );

        // 验证参数
        $this->validate($data, 'app\api\controller\technician\validate\TechnicianCommentValidate.list');

        $result = (new TechnicianCommentService())->getTechnicianCommentList($data);
        return ds_json_success('操作成功', $result);
    }

}
