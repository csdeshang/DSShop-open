<?php

namespace app\api\controller\login;

use app\deshang\base\controller\BaseApiController;

use app\api\service\login\ResetPasswordService;

/**
 * @OA\Tag(name="api/login/ResetPassword", description="密码重置接口")
 */
class ResetPassword extends BaseApiController{

    /**
     * @OA\Post(
     *     path="/api/reset-password/mobile",
     *     tags={"api/login/ResetPassword"},
     *     summary="手机号重置密码",
     *     description="通过手机号和验证码重置用户密码",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="mobile", type="string", example="13800138000", description="手机号"),
     *             @OA\Property(property="code", type="string", example="123456", description="验证码"),
     *             @OA\Property(property="password", type="string", example="123456", description="新密码"),
     *             @OA\Property(property="confirm_password", type="string", example="123456", description="确认密码")
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
    /**
     * 手机号重置密码 (手机号+验证码+新密码)
     * @return \think\response\Json
     */
    public function resetPasswordMobile(){
        $data = array(
            'mobile' => input('param.mobile'),
            'code' => input('param.code'),
            'password' => input('param.password'),
            'confirm_password' => input('param.confirm_password'),
        );

        $this->validate($data, 'app\api\controller\login\validate\ResetPasswordValidate.mobile');

        $result = (new ResetPasswordService())->resetPasswordMobile($data);
        return ds_json_success('重置密码成功',$result);
    }

    /**
     * @OA\Post(
     *     path="/api/reset-password/email",
     *     tags={"api/login/ResetPassword"},
     *     summary="邮箱重置密码",
     *     description="通过邮箱和验证码重置用户密码",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="user@example.com", description="邮箱地址"),
     *             @OA\Property(property="code", type="string", example="123456", description="验证码"),
     *             @OA\Property(property="password", type="string", example="123456", description="新密码"),
     *             @OA\Property(property="confirm_password", type="string", example="123456", description="确认密码")
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
    // 邮箱重置密码 (邮箱+验证码+新密码) 或通过链接重置密码
    public function resetPasswordEmail(){

    }

}