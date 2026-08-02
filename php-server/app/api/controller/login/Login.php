<?php

namespace app\api\controller\login;

use app\deshang\base\controller\BaseApiController;
use app\deshang\utils\TokenCache;
use app\deshang\utils\JwtToken;

use app\api\service\login\LoginService;

/**
 * @OA\Tag(name="api/login/Login", description="用户登录接口")
 */
class Login extends BaseApiController{

    /**
     * 普通登录
     * @OA\Post(
     *     path="/api/login/normal",
     *     tags={"api/login/Login"},
     *     summary="普通登录",
     *     description="支持用户名、手机号、邮箱登录",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", description="用户名/手机号/邮箱", example="test123"),
     *             @OA\Property(property="password", type="string", description="密码", example="123456"),
     *             @OA\Property(property="login_type", type="string", description="登录类型：user-用户端", example="user")
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
    public function loginNormal(){
        $data = array(
            'username'   => input('param.username'),
            'password'   => input('param.password'),
            'login_type' => input('param.login_type', 'user'),
        );

        $this->validate($data, 'app\api\controller\login\validate\LoginValidate.normal');

        $result = (new LoginService())->loginNormal($data);
        return ds_json_success('登录成功',$result);
    }

    /**
     * 手机验证码登录
     * @OA\Post(
     *     path="/api/login/mobile",
     *     tags={"api/login/Login"},
     *     summary="手机验证码登录",
     *     description="使用手机号和验证码登录",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile", "code"},
     *             @OA\Property(property="mobile", type="string", description="手机号", example="13800138000"),
     *             @OA\Property(property="code", type="string", description="验证码", example="123456"),
     *             @OA\Property(property="login_type", type="string", description="登录类型：user-用户端", example="user")
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
    public function loginMobile(){
        $data = array(
            'mobile'     => input('param.mobile'),
            'code'       => input('param.code'),
            'login_type' => input('param.login_type', 'user'),
        );

        $this->validate($data, 'app\api\controller\login\validate\LoginValidate.mobile');

        $result = (new LoginService())->loginMobile($data);
        return ds_json_success('登录成功',$result);
    }

    /**
     * 刷新Token
     * @OA\Get(
     *     path="/api/refresh-token",
     *     tags={"api/login/Login"},
     *     summary="刷新Token",
     *     description="使用refresh_token刷新access_token",
     *     @OA\Parameter(
     *         name="refresh_token",
     *         in="query",
     *         required=true,
     *         description="刷新令牌",
     *         @OA\Schema(type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
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
    public function refreshToken()
    {
        $refresh_token = input('param.refresh_token');
        $LoginService = new LoginService();
        $result = $LoginService->refreshUserToken($refresh_token);

        return ds_json_success('操作成功', $result);
    }

    /**
     * 用户退出
     * @OA\Get(
     *     path="/api/logout",
     *     tags={"api/login/Login"},
     *     summary="用户退出",
     *     description="用户退出登录",
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
    public function logout()
    {
        // 获取refresh_token参数
        $refresh_token = input('param.refresh_token');
        
        // 退出登录处理：使该管理员在所有设备上的所有Token失效 防止账号被盗用后其他设备仍可访问
        // 这种"全平台退出"机制被微信、QQ、支付宝、淘宝等主流平台采用  
        if ($refresh_token) {
            // 验证refresh_token的有效性
            $refresh_token_info = (new JwtToken())->validateToken($refresh_token);
            if ($refresh_token_info && isset($refresh_token_info['role']) && isset($refresh_token_info['user_id'])) {
                (new TokenCache())->invalidateToken($refresh_token_info['role'], $refresh_token_info['user_id']);
            }
        }
        
        $data = array(
            'code' => 10000,
            'msg' => '退出成功'
        );
        return json($data);
    }
}