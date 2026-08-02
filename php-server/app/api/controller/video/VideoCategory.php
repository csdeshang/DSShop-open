<?php

namespace app\api\controller\video;

use app\deshang\base\controller\BaseApiController;
use app\api\service\video\VideoCategoryService;

/**
 * @OA\Tag(name="api/video/VideoCategory", description="视频分类接口")
 */
class VideoCategory extends BaseApiController
{
    /**
     * 获取视频分类树结构
     * @OA\Get(
     *     path="/api/video/categories/tree",
     *     tags={"api/video/VideoCategory"},
     *     summary="获取视频分类树结构",
     *     description="获取视频分类的树形结构数据",
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         description="分类类型 short/drama/live",
     *         @OA\Schema(type="string", example="short")
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
    public function getVideoCategoryTree()
    {
        $data = array(
            'type' => input('param.type'),
        );
        
        $this->validate($data, 'app\api\controller\video\validate\VideoCategoryValidate.tree');
        
        $result = (new VideoCategoryService())->getVideoCategoryList($data);
        
        $result = linearToTree($result, 'id', 'pid');
        
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取视频分类列表
     * @OA\Get(
     *     path="/api/video/categories/list",
     *     tags={"api/video/VideoCategory"},
     *     summary="获取视频分类列表",
     *     description="获取视频分类的平铺列表数据",
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         description="分类类型 short/drama/live",
     *         @OA\Schema(type="string", example="short")
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
    public function getVideoCategoryList()
    {
        $data = array(
            'type' => input('param.type'),
            'pid' => input('param.pid', 0),
        );
        
        $this->validate($data, 'app\api\controller\video\validate\VideoCategoryValidate.list');
        
        $result = (new VideoCategoryService())->getVideoCategoryList($data);
        
        return ds_json_success('操作成功', $result);
    }
}
