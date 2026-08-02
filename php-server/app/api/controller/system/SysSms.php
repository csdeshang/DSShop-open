<?php

namespace app\api\controller\system;
use app\deshang\base\controller\BaseApiController;

use app\api\service\system\SysSmsService;

/**
 * @OA\Tag(name="api/system/SysSms", description="短信服务接口")
 */
class SysSms extends BaseApiController{

    /**
     * 发送短信验证码
     * @OA\Post(
     *     path="/api/system/sms",
     *     tags={"api/system/SysSms"},
     *     summary="发送短信验证码",
     *     description="发送登录、注册、找回密码等短信验证码",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key", "mobile"},
     *             @OA\Property(property="key", type="string", description="短信类型 login注册登录 register注册 reset重置密码", example="login"),
     *             @OA\Property(property="mobile", type="string", description="手机号", example="13800138000")
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
    public function sendSms(){

        // 参数预留
        $data = array(
            'key' => (string) input('param.key'),
            'mobile' => (string) input('param.mobile'),
        );

        // 验证器
        $this->validate($data, 'app\api\controller\system\validate\SysSmsValidate.sendSms');
        

        $result = (new SysSmsService())->sendSms($data);
        return ds_json_success('操作成功',$result);
    }





}