<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserBindWechatService;

/**
 * @OA\Tag(name="api/user/UserBindWechat", description="用户绑定微信管理接口")
 */
class UserBindWechat extends BaseUserController
{

    /**
     * @OA\Get(
     *     path="/api/user/bind/wechat/status",
     *     tags={"api/user/UserBindWechat"},
     *     summary="获取微信绑定状态",
     *     description="获取当前用户的微信绑定状态",
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
    public function getBindWechatStatus(){
        $data = (new UserBindWechatService())->getWechatBindStatus();
        return ds_json_success('操作成功',$data);
    }

    /**
     * @OA\Post(
     *     path="/api/user/bind/wechat/delete",
     *     tags={"api/user/UserBindWechat"},
     *     summary="解绑微信",
     *     description="解除当前用户的微信绑定",
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
    public function deleteBindWechat(){
        $data = (new UserBindWechatService())->deleteBindWechat();
        return ds_json_success('操作成功',$data);
    }

}
