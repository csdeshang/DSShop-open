<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserVideoViewLogService;

/**
 * @OA\Tag(name="api/user/UserVideoViewLog", description="用户视频浏览记录管理接口")
 */
class UserVideoViewLog extends BaseUserController
{
    /**
     * 获取用户视频浏览记录列表
     * @OA\Get(
     *     path="/api/user/video-view-logs/pages",
     *     tags={"api/user/UserVideoViewLog"},
     *     summary="获取用户视频浏览记录列表",
     *     description="分页获取当前用户的视频浏览记录列表",
     *     @OA\Parameter(
     *         name="page_current",
     *         in="query",
     *         description="当前页码",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         description="每页数量",
     *         required=false,
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
    public function getVideoViewLogPages()
    {
        $data = array(
            'content_type' => input('param.content_type', 'short'),
        );
        $this->validate($data, 'app\api\controller\user\validate\UserVideoViewLogValidate.pages');
        
        $result = (new UserVideoViewLogService())->getVideoViewLogPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 创建视频浏览记录
     * @OA\Post(
     *     path="/api/user/video-view-logs",
     *     tags={"api/user/UserVideoViewLog"},
     *     summary="创建视频浏览记录",
     *     description="记录用户观看视频的行为",
     *     @OA\RequestBody(
     *         required=true,
     *         description="浏览记录数据",
     *         @OA\JsonContent(
     *             @OA\Property(property="content_type", type="string", description="内容类型", example="short"),
     *             @OA\Property(property="content_id", type="integer", description="内容ID", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="记录成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function createVideoViewLog()
    {
        $data = array(
            'content_type' => input('param.content_type', 'short'),
            'content_id' => input('param.content_id', 0),
        );
        $this->validate($data, 'app\api\controller\user\validate\UserVideoViewLogValidate.create');
        
        $result = (new UserVideoViewLogService())->createVideoViewLog($data['content_type'], $data['content_id']);
        return ds_json_success('操作成功', $result);
    }



    /**
     * 批量删除视频浏览记录
     * @OA\Delete(
     *     path="/api/user/video-view-logs/batch",
     *     tags={"api/user/UserVideoViewLog"},
     *     summary="批量删除视频浏览记录",
     *     description="批量删除多个视频浏览记录",
     *     @OA\RequestBody(
     *         required=true,
     *         description="记录ID数组",
     *         @OA\JsonContent(
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"), description="记录ID数组", example={1,2,3})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="批量删除成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function batchDeleteVideoViewLog()
    {
        $data = input();
        $this->validate($data, 'app\api\controller\user\validate\UserVideoViewLogValidate.batchDelete');
        
        $result = (new UserVideoViewLogService())->batchDeleteVideoViewLog($data['ids']);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 清空所有视频浏览记录
     * @OA\Delete(
     *     path="/api/user/video-view-logs/clear",
     *     tags={"api/user/UserVideoViewLog"},
     *     summary="清空所有视频浏览记录",
     *     description="清空当前用户的所有视频浏览记录",
     *     @OA\Response(
     *         response=200,
     *         description="清空成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function clearAllVideoViewLog()
    {
        $data = array(
            'content_type' => input('param.content_type', 'short'),
        );
        $this->validate($data, 'app\api\controller\user\validate\UserVideoViewLogValidate.clearAll');

        $result = (new UserVideoViewLogService())->clearAllVideoViewLog($data['content_type']);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 删除视频浏览记录
     * @OA\Delete(
     *     path="/api/user/video-view-logs/{id}",
     *     tags={"api/user/UserVideoViewLog"},
     *     summary="删除视频浏览记录",
     *     description="删除指定的视频浏览记录",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="记录ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="删除成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function deleteVideoViewLog($id)
    {
        $data = ['id' => $id];
        $this->validate($data, 'app\api\controller\user\validate\UserVideoViewLogValidate.info');
        
        $result = (new UserVideoViewLogService())->deleteVideoViewLog($id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取视频浏览记录详情
     * @OA\Get(
     *     path="/api/user/video-view-logs/{id}",
     *     tags={"api/user/UserVideoViewLog"},
     *     summary="获取视频浏览记录详情",
     *     description="获取指定视频浏览记录的详细信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="记录ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getVideoViewLogInfo($id)
    {
        $data = ['id' => $id];
        $this->validate($data, 'app\api\controller\user\validate\UserVideoViewLogValidate.info');
        
        $result = (new UserVideoViewLogService())->getVideoViewLogInfo($id);
        return ds_json_success('操作成功', $result);
    }


}
