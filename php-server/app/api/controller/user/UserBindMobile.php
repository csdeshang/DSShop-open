<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserBindMobileService;

/**
 * @OA\Tag(name="api/user/UserBindMobile", description="用户绑定手机管理接口")
 */
class UserBindMobile extends BaseUserController
{

    /**
     * @OA\Post(
     *     path="/api/user/bind/mobile",
     *     tags={"api/user/UserBindMobile"},
     *     summary="绑定手机号",
     *     description="用户绑定手机号码（如果已绑定则覆盖更新）",
     *     @OA\RequestBody(
     *         required=true,
     *         description="绑定手机号请求数据",
     *         @OA\JsonContent(
     *             @OA\Property(property="mobile", type="string", description="手机号码", example="13800138000"),
     *             @OA\Property(property="code", type="string", description="短信验证码", example="123456")
     *         )
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
    public function bindMobile()
    {
        $data = array(
            'mobile' => input('param.mobile'),
            'code' => input('param.code'),
        );

        $this->validate($data, 'app\api\controller\user\validate\UserBindMobileValidate.bindMobile');

        $result = (new UserBindMobileService())->bindMobile($data);
        return ds_json_success('手机绑定成功', $result);
    }

    /**
     * @OA\Post(
     *     path="/api/user/unbind/mobile",
     *     tags={"api/user/UserBindMobile"},
     *     summary="解绑手机号",
     *     description="用户解绑手机号码",
     *     @OA\RequestBody(
     *         required=true,
     *         description="解绑手机号请求数据",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string", description="短信验证码", example="123456")
     *         )
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
    public function unbindMobile()
    {
        $data = array(
            'code' => input('param.code'),
        );

        $this->validate($data, 'app\api\controller\user\validate\UserBindMobileValidate.unbindMobile');

        $result = (new UserBindMobileService())->unbindMobile($data);
        return ds_json_success('手机解绑成功', $result);
    }
}