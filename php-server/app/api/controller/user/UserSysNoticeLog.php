<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserSysNoticeLogService;

/**
 * @OA\Tag(name="api/user/UserSysNoticeLog", description="用户消息通知管理接口")
 */
// 用户消息通知
class UserSysNoticeLog extends BaseUserController
{

    /**
     * 获取用户消息通知列表
     * @OA\Get(
     *     path="/api/user/notice-logs/pages",
     *     tags={"api/user/UserSysNoticeLog"},
     *     summary="获取用户消息通知列表",
     *     description="分页获取用户的消息通知列表",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页码",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="每页数量",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
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
    // 获取用户消息通知
    public function getUserSysNoticeLogPages(){
        $data = array(


        );
        $data = (new UserSysNoticeLogService())->getUserSysNoticeLogPages($data);
        return ds_json_success('操作成功',$data);
    }


    /**
     * 获取用户消息通知详情
     * @OA\Get(
     *     path="/api/user/notice-logs/{id}",
     *     tags={"api/user/UserSysNoticeLog"},
     *     summary="获取消息通知详情",
     *     description="获取指定消息通知的详细信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="消息通知ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=123)
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
    // 获取用户消息通知详情
    public function getUserSysNoticeLogInfo($id){
        $this->validate(['id' => $id], 'app\api\controller\user\validate\UserSysNoticeLogValidate.info');
        
        $data = (new UserSysNoticeLogService())->getUserSysNoticeLogInfo($id);
        return ds_json_success('操作成功',$data);
    }

}
