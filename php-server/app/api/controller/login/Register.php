<?php

namespace app\api\controller\login;

use app\deshang\base\controller\BaseApiController;

use app\api\service\login\RegisterService;

/**
 * @OA\Tag(name="api/login/Register", description="用户注册接口")
 */
class Register extends BaseApiController{

    /**
     * @OA\Post(
     *     path="/api/register/normal",
     *     tags={"api/login/Register"},
     *     summary="普通注册",
     *     description="使用用户名和密码进行普通注册",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="testuser", description="用户名"),
     *             @OA\Property(property="password", type="string", example="123456", description="密码"),
     *             @OA\Property(property="confirm_password", type="string", example="123456", description="确认密码"),
     *             @OA\Property(property="referrer_id", type="integer", example=0, description="邀请人ID")
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
    public function registerNormal(){
        $data = array(
            'username' => input('param.username'),
            'password' => input('param.password'),
            'confirm_password' => input('param.confirm_password'),
            // 邀请人ID
            'inviter_id' => (int) input('param.referrer_id'),
        );

        $this->validate($data, 'app\api\controller\login\validate\RegisterValidate.normal');

        $result = (new RegisterService())->registerNormal($data);
        return ds_json_success('注册成功',$result);
    }

    /**
     * @OA\Post(
     *     path="/api/register/mobile",
     *     tags={"api/login/Register"},
     *     summary="手机号注册",
     *     description="使用手机号和验证码进行注册",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="testuser", description="用户名"),
     *             @OA\Property(property="password", type="string", example="123456", description="密码"),
     *             @OA\Property(property="confirm_password", type="string", example="123456", description="确认密码"),
     *             @OA\Property(property="mobile", type="string", example="13800138000", description="手机号"),
     *             @OA\Property(property="code", type="string", example="123456", description="验证码"),
     *             @OA\Property(property="referrer_id", type="integer", example=0, description="邀请人ID")
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
    public function registerMobile(){
        $data = array(
            'username' => input('param.username'),
            'password' => input('param.password'),
            'confirm_password' => input('param.confirm_password'),
            'mobile' => input('param.mobile'),
            'code' => input('param.code'),
            // 邀请人ID
            'inviter_id' => (int) input('param.referrer_id'),
        );

        $this->validate($data, 'app\api\controller\login\validate\RegisterValidate.mobile');

        $result = (new RegisterService())->registerMobile($data);
        return ds_json_success('注册成功',$result); 
    }



}