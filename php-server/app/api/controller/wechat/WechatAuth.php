<?php

namespace app\api\controller\wechat;
use app\deshang\base\controller\BaseApiController;

use app\api\service\wechat\WechatAuthService;

/**
 * @OA\Tag(name="api/wechat/WechatAuth", description="微信授权接口")
 */
class WechatAuth extends BaseApiController{

    /**
     * 微信授权获取用户信息
     * @OA\Post(
     *     path="/api/wechat/auth",
     *     tags={"api/wechat/WechatAuth"},
     *     summary="微信授权获取用户信息",
     *     description="根据微信授权Code获取用户信息",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code", "scene", "mode"},
     *             @OA\Property(property="code", type="string", description="微信授权码", example="061a7c9f6a1e4b0000000001"),
     *             @OA\Property(property="scene", type="string", description="授权场景", example="wechat_official"),
     *             @OA\Property(property="mode", type="string", description="授权模式", example="login")
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
    public function getWechatAuthInfo(){
        $data = array(
            'code' => input('param.code', ''),
            'scene' => input('param.scene', ''),
            'mode' => input('param.mode', ''),
        );

        $this->validate($data, 'app\api\controller\wechat\validate\WechatAuthValidate.getWechatAuthInfo');
        
        $result = (new WechatAuthService())->getWechatAuthInfo($data);
        return ds_json_success('授权成功',$result);
    }

    /**
     * 检测微信绑定状态
     * @OA\Get(
     *     path="/api/wechat/check-auth",
     *     tags={"api/wechat/WechatAuth"},
     *     summary="检测微信绑定状态",
     *     description="检测用户是否已绑定微信openid",
     *     @OA\Parameter(
     *         name="scene",
     *         in="query",
     *         required=true,
     *         description="授权场景",
     *         @OA\Schema(type="string", example="wechat_official")
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
    public function checkWechatAuth(){
        $data = array(
            'scene' => input('param.scene', ''),
        );
        
        $this->validate($data, 'app\api\controller\wechat\validate\WechatAuthValidate.checkWechatAuth');
        
        $result = (new WechatAuthService())->checkWechatAuth($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取公众号网页授权URL
     * @OA\Post(
     *     path="/api/wechat/official-auth-url",
     *     tags={"api/wechat/WechatAuth"},
     *     summary="获取公众号网页授权URL",
     *     description="获取微信公众号网页授权链接（仅用于wechat_official场景，微信浏览器内授权）",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"redirect_url", "mode"},
     *             @OA\Property(property="mode", type="string", description="授权模式", example="login"),
     *             @OA\Property(property="redirect_url", type="string", description="回调地址", example="https://example.com/callback")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="string", example="https://open.weixin.qq.com/connect/oauth2/authorize?...")
     *         )
     *     )
     * )
     */
    public function getOfficialAuthUrl(){
        $data = array(
            'mode' => input('param.mode', ''),
            'redirect_url' => input('param.redirect_url', ''),
        );
 
        $this->validate($data, 'app\api\controller\wechat\validate\WechatAuthValidate.getOfficialAuthUrl');
        
        $url = (new WechatAuthService())->getOfficialAuthUrl($data);
        return ds_json_success('操作成功',$url);
    }

    /**
     * 获取网站应用PC扫码登录URL
     * @OA\Post(
     *     path="/api/wechat/web-auth-url",
     *     tags={"api/wechat/WechatAuth"},
     *     summary="获取网站应用PC扫码登录URL",
     *     description="获取微信开放平台网站应用PC扫码登录URL（仅用于wechat_web场景）",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"redirect_url", "mode"},
     *             @OA\Property(property="mode", type="string", description="授权模式", example="login"),
     *             @OA\Property(property="redirect_url", type="string", description="回调地址", example="https://example.com/callback")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="string", example="https://open.weixin.qq.com/connect/qrconnect?...")
     *         )
     *     )
     * )
     */
    public function getWebAuthUrl(){
        $data = array(
            'mode' => input('param.mode', ''),
            'redirect_url' => input('param.redirect_url', ''),
        );
 
        $this->validate($data, 'app\api\controller\wechat\validate\WechatAuthValidate.getWebAuthUrl');
        
        $url = (new WechatAuthService())->getWebAuthUrl($data);
        return ds_json_success('操作成功',$url);
    }

    /**
     * 获取微信JS SDK配置
     * @OA\Post(
     *     path="/api/wechat/js-sdk-config",
     *     tags={"api/wechat/WechatAuth"},
     *     summary="获取微信JS SDK配置",
     *     description="获取微信JS SDK配置信息",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"url"},
     *             @OA\Property(property="url", type="string", description="当前页面URL", example="https://example.com/page")
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
    public function getJsSdkConfig(){
        $data = array(
            'url' => input('param.url', ''),
        );

        $this->validate($data, 'app\api\controller\wechat\validate\WechatAuthValidate.getJsSdkConfig');

        $result = (new WechatAuthService())->getJsSdkConfig($data);
        return ds_json_success('操作成功',$result);
    }
}
