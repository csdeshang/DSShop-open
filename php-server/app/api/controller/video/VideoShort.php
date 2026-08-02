<?php

namespace app\api\controller\video;
use app\deshang\base\controller\BaseApiController;

use app\api\service\video\VideoShortService;

/**
 * @OA\Tag(name="api/video/VideoShort", description="短视频接口")
 */
class VideoShort extends BaseApiController{

    /**
     * 获取短视频分页列表
     * @OA\Get(
     *     path="/api/video/shorts/pages",
     *     tags={"api/video/VideoShort"},
     *     summary="获取短视频分页列表",
     *     description="获取短视频分页数据，支持多种查询类型",
     *     @OA\Parameter(
     *         name="blogger_id",
     *         in="query",
     *         required=false,
     *         description="博主ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         description="视频类型",
     *         @OA\Schema(type="integer", example=0)
     *     ),
     *     @OA\Parameter(
     *         name="query_type",
     *         in="query",
     *         required=false,
     *         description="查询类型：default(默认) | user_liked(用户点赞) | user_collected(用户收藏)",
     *         @OA\Schema(type="string", example="default")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=false,
     *         description="用户ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="specified_id",
     *         in="query",
     *         required=false,
     *         description="指定短视频ID（放在第一个）",
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
    public function getVideoShortPages(){

        $data = array(
            'blogger_id' => input('param.blogger_id',0 ,'int'),
            'type' => input('param.type',0 , 'int'),
             // 查询类型：default(默认) | user_liked(用户点赞) | user_collected(用户收藏)
            'query_type' => input('param.query_type'),
            'user_id' => input('param.user_id',0 , 'int'),
            // 短视频ID（放在第一个）
            'specified_id' => input('param.specified_id',0 , 'int'),
        );

        $this->validate($data, 'app\api\controller\video\validate\VideoShortValidate.pages');
        
        $result = (new VideoShortService())->getVideoShortPages($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取短视频详情
     * @OA\Get(
     *     path="/api/video/shorts/{id}",
     *     tags={"api/video/VideoShort"},
     *     summary="获取短视频详情",
     *     description="获取指定短视频的详细信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="短视频ID",
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
    public function getVideoShortDetail($id){
        $this->validate(['id' => $id], 'app\api\controller\video\validate\VideoShortValidate.detail');

        $result = (new VideoShortService())->getVideoShortDetail($id);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取短视频关联商品列表
     * @OA\Get(
     *     path="/api/video/shorts/{id}/goods-list",
     *     tags={"api/video/VideoShort"},
     *     summary="获取短视频关联商品列表",
     *     description="获取指定短视频关联的商品列表",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="短视频ID",
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
    public function getVideoShortGoodsList($id){
        $this->validate(['id' => $id], 'app\api\controller\video\validate\VideoShortValidate.goodsList');
        
        $result = (new VideoShortService())->getVideoShortGoodsList($id);
        return ds_json_success('操作成功',$result);
    }

}

