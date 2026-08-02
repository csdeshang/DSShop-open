<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserPointsSignService;

/**
 * @OA\Tag(name="api/user/UserPointsSign", description="用户积分签到接口")
 */
class UserPointsSign extends BaseUserController
{
    /**
     * @OA\Get(
     *     path="/api/user/points-sign/status",
     *     tags={"api/user/UserPointsSign"},
     *     summary="获取签到状态",
     *     description="获取当前用户今日是否已签到、连续签到天数、最后签到日期",
     *     @OA\Response(response=200, description="操作成功")
     * )
     */
    public function getUserPointsSignStatus()
    {
        $data = (new UserPointsSignService())->getUserPointsSignStatus();
        return ds_json_success('操作成功', $data);
    }

    /**
     * @OA\Post(
     *     path="/api/user/points-sign",
     *     tags={"api/user/UserPointsSign"},
     *     summary="签到",
     *     description="用户每日签到，获得积分并更新连续签到天数",
     *     @OA\Response(response=200, description="操作成功")
     * )
     */
    public function doUserPointsSign()
    {
        $data = (new UserPointsSignService())->doUserPointsSign();
        return ds_json_success($data['message'], $data);
    }
}
