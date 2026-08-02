<?php

namespace app\api\controller\wechat;

use app\deshang\base\controller\BaseApiController;
use app\api\service\wechat\WechatSubscribeService;

/**
 * @OA\Tag(name="api/wechat/WechatSubscribe", description="微信订阅消息接口")
 */
class WechatSubscribe extends BaseApiController
{
    /**
     * 获取微信订阅模板ID
     * @OA\Get(
     *     path="/api/wechat/subscribe/template-id",
     *     tags={"api/wechat/WechatSubscribe"},
     *     summary="获取微信订阅模板ID",
     *     description="根据模板key和类型获取对应的微信订阅模板ID",
     *     @OA\Parameter(
     *         name="keys",
     *         in="query",
     *         required=true,
     *         description="模板key，逗号分隔",
     *         @OA\Schema(type="string", example="user_order_delivery,user_refund_approved")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=true,
     *         description="订阅类型：mini=小程序，official=公众号",
     *         @OA\Schema(type="string", enum={"mini", "official"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function getWechatSubscribeTemplateId()
    {
        $data = [
            'keys' => input('param.keys', ''),
            'type' => input('param.type', ''),
        ];

        $this->validate($data, 'app\api\controller\wechat\validate\WechatSubscribeValidate.getTemplateId');

        $result = (new WechatSubscribeService())->getWechatSubscribeTemplateId($data);
        return ds_json_success('获取成功', $result);
    }

    /**
     * 保存订阅记录
     * @OA\Post(
     *     path="/api/wechat/subscribe/record",
     *     tags={"api/wechat/WechatSubscribe"},
     *     summary="保存订阅记录",
     *     description="保存用户的订阅消息记录",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"keys", "type"},
     *             @OA\Property(property="keys", type="string", description="模板key", example="user_order_delivery,user_refund_approved"),
     *             @OA\Property(property="type", type="string", description="订阅类型", enum={"mini", "official"})
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
    public function saveWechatSubscribeRecord()
    {
        $data = [
            'keys' => input('param.keys', ''),
            'type' => input('param.type', ''),
        ];

        $this->validate($data, 'app\api\controller\wechat\validate\WechatSubscribeValidate.saveRecord');

        $result = (new WechatSubscribeService())->saveWechatSubscribeRecord($data);
        return ds_json_success('保存成功', $result);
    }

    /**
     * 获取公众号订阅URL 【公众号消息 分为 模板消息和一次性订阅消息,此处是预留一次性订阅消息接口， 默认使用模板消息】
     * @OA\Post(
     *     path="/api/wechat/subscribe/official-url",
     *     tags={"api/wechat/WechatSubscribe"},
     *     summary="获取公众号订阅URL",
     *     description="获取微信公众号一次性订阅消息的授权链接",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"keys", "redirect_url"},
     *             @OA\Property(property="keys", type="string", description="模板key", example="user_order_delivery"),
     *             @OA\Property(property="redirect_url", type="string", description="授权后重定向URL", example="https://example.com/callback"),
     *             @OA\Property(property="scene", type="integer", description="场景值", example=1000)
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
    public function getOfficialSubscribeUrl()
    {
        $data = [
            'keys' => input('param.keys', ''),
            'redirect_url' => input('param.redirect_url', ''),
            'scene' => input('param.scene', 1000),
        ];

        $this->validate($data, 'app\api\controller\wechat\validate\WechatSubscribeValidate.getOfficialUrl');

        $result = (new WechatSubscribeService())->getOfficialSubscribeUrl($data);
        return ds_json_success('获取成功', $result);
    }


}