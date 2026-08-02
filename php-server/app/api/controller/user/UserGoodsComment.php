<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserGoodsCommentService;

/**
 * @OA\Tag(name="api/user/UserGoodsComment", description="用户商品评论管理接口")
 */
// 用户商品评论
class UserGoodsComment extends BaseUserController
{

    /**
     * 获取用户商品评论
     * @OA\Get(
     *     path="/api/user/goods-comments/pages",
     *     tags={"api/user/UserGoodsComment"},
     *     summary="获取用户商品评论列表",
     *     description="分页获取当前用户的商品评论记录",
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="list", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="limit", type="integer", example=10)
     *             )
     *         )
     *     )
     * )
     */
    public function getUserGoodsCommentPages(){
        $data = array(


        );
        $data = (new UserGoodsCommentService())->getUserGoodsCommentPages($data);
        return ds_json_success('操作成功',$data);
    }



    /**
     * 添加用户商品评论
     * @OA\Post(
     *     path="/api/user/goods-comments",
     *     tags={"api/user/UserGoodsComment"},
     *     summary="添加商品评论",
     *     description="用户对订单商品进行评价，包括商品评价、配送员评价、师傅评价",
     *     @OA\RequestBody(
     *         required=true,
     *         description="评论数据",
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="integer", description="订单ID", example=123),
     *             @OA\Property(property="goods_comments", type="array", description="商品评论列表",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="goods_id", type="integer", description="商品ID", example=456),
     *                     @OA\Property(property="content", type="string", description="评论内容", example="商品质量很好"),
     *                     @OA\Property(property="score", type="integer", description="评分 1-5分", example=5),
     *                     @OA\Property(property="images", type="array", description="评论图片", @OA\Items(type="string", example="https://example.com/image.jpg"))
     *                 )
     *             ),
     *             @OA\Property(property="is_anonymous", type="integer", description="是否匿名 0否 1是", example=0),
     *             @OA\Property(property="rider_content", type="string", description="配送员评价内容", example="配送很及时"),
     *             @OA\Property(property="rider_service_score", type="integer", description="配送员服务评分 1-5分", example=5),
     *             @OA\Property(property="technician_content", type="string", description="师傅评价内容", example="师傅服务很专业"),
     *             @OA\Property(property="technician_service_score", type="integer", description="师傅服务评分 1-5分", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="评价成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    // 添加用户商品评论
    public function addUserGoodsComment(){
        $data = array(
            'order_id' => input('param.order_id'),
            'goods_comments' => input('param.goods_comments/a'),
            // 是否匿名 0 否 1 是
            'is_anonymous' => input('param.is_anonymous') ? 1 : 0,
            // 配送员评价
            'rider_content' => input('param.rider_content',''),
            'rider_service_score' => input('param.rider_service_score',5),
            // 师傅评价
            'technician_content' => input('param.technician_content',''),
            'technician_service_score' => input('param.technician_service_score',5),
        );

        $this->validate($data, 'app\api\controller\user\validate\UserGoodsCommentValidate.add');

        $data = (new UserGoodsCommentService())->addUserGoodsComment($data);
        return ds_json_success('操作成功',$data);
    }



}
