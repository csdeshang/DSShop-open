<?php

namespace app\api\controller\wechat;

use app\BaseController;
use app\deshang\service\wechat\DeshangOfficialEasyService;

/**
 * 微信消息接收控制器
 * 用于接收微信服务器推送的消息和事件
 */
class WechatServe extends BaseController
{
    /**
     * 微信公众号消息接收处理
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function official()
    {
        $response = (new DeshangOfficialEasyService())
            ->init()
            ->serve();

        return $response;
    }

    /**
     * 微信小程序消息接收处理（预留）
     *
     * @return \think\Response|void
     */
    public function mini() {}
}
