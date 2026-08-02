<?php
// php-server/app/api/service/wechat/WechatMiniService.php

namespace app\api\service\wechat;

use app\common\dao\system\SysNoticeTplDao;
use app\common\dao\wechat\WechatSubscribeRecordDao;
use app\common\enum\wechat\WechatSubscribeEnum;
use app\deshang\base\service\BaseApiService;

use app\deshang\exceptions\CommonException;

class WechatSubscribeService extends BaseApiService
{
    /**
     * 获取模板ID
     */
    public function getWechatSubscribeTemplateId($data)
    {
        $keys = explode(',', $data['keys']);
        $keys = array_filter(array_map('trim', $keys));
        $type = $data['type']; // mini 或 official
        
        if (empty($keys)) {
            throw new CommonException('模板key不能为空');
        }

        // 从通知模板表获取对应类型的模板ID
        $condition = [['key', 'in', $keys]];
        $templates = (new SysNoticeTplDao())->getSysNoticeTplList($condition);
        
        $templateIds = [];
        $fieldName = $type === WechatSubscribeEnum::SUBSCRIBE_TYPE_MINI ? 'wechat_mini_template_id' : 'wechat_official_template_id';
        $switchField = $type === WechatSubscribeEnum::SUBSCRIBE_TYPE_MINI ? 'wechat_mini_switch' : 'wechat_official_switch';
        
        foreach ($templates as $template) {
            if (!empty($template[$fieldName]) && $template[$switchField] == 1) {
                $templateIds[] = $template[$fieldName];
            }
        }

        if (empty($templateIds)) {
            throw new CommonException('未找到可用的订阅模板');
        }

        // 微信小程序一次最多订阅3个模板
        if ($type === WechatSubscribeEnum::SUBSCRIBE_TYPE_MINI && count($templateIds) > 3) {
            $templateIds = array_slice($templateIds, 0, 3);
        }

        return $templateIds;
    }

    /**
     * 保存订阅记录(小程序订阅消息和公众号订阅消息) 
     * 此处主要用于小程序订阅消息，公众号使用的是模板消息， 预留公众号一次性订阅消息接口， 当前默认使用模板消息
     */
    public function saveWechatSubscribeRecord($data)
    {
        $userId = $this->user_id;
        if (empty($userId)) {
            throw new CommonException('用户ID不能为空');
        }

        $keys = explode(',', $data['keys']);
        $keys = array_filter(array_map('trim', $keys));
        $type = $data['type'];

        if (empty($keys)) {
            throw new CommonException('模板key不能为空');
        }

        // 获取对应的模板ID
        $templateIds = $this->getWechatSubscribeTemplateId(['keys' => implode(',', $keys), 'type' => $type]);

        // 为每个key创建成功的订阅记录
        foreach ($keys as $index => $key) {
            $templateId = $templateIds[$index] ?? '';
            
            if (empty($templateId)) {
                continue; // 跳过没有模板ID的key
            }

            $recordData = [
                'user_id' => $userId,
                'template_key' => $key,
                'template_id' => $templateId,
                'subscribe_type' => $type,
                'subscribe_status' => WechatSubscribeEnum::SUBSCRIBE_STATUS_ACCEPT, // 直接设为成功
                'subscribe_time' => time(),
                'expire_time' => time() + (30 * 24 * 3600), // 30天有效期
                'create_at' => time(),
                'update_at' => time(),
            ];

            (new WechatSubscribeRecordDao())->createWechatSubscribeRecord($recordData);
        }

        return ['success' => true];
    }

    /**
     * 获取公众号订阅URL
     */
    public function getOfficialSubscribeUrl($data)
    {
        $keys = $data['keys'];
        $redirectUrl = $data['redirect_url'];
        $scene = $data['scene'] ?? 1000;

        // 获取公众号配置
        $officialSetting = (new \app\deshang\service\wechat\DeshangOfficialSettingService())->getWechatOfficialSetting();
        
        if (empty($officialSetting['app_id'])) {
            throw new CommonException('公众号配置错误');
        }

        // 获取第一个模板ID
        $templateIds = $this->getWechatSubscribeTemplateId(['keys' => $keys, 'type' => 'official']);
        
        if (empty($templateIds)) {
            throw new CommonException('未找到可用的订阅模板');
        }

        $templateId = $templateIds[0]; // 公众号只取第一个模板

        // 构建授权链接参数
        $params = [
            'action' => 'get_confirm',
            'appid' => $officialSetting['app_id'],
            'scene' => $scene,
            'template_id' => $templateId,
            'redirect_url' => $redirectUrl,
            'reserved' => $keys
        ];

        // 构建完整的授权URL
        $authUrl = 'https://mp.weixin.qq.com/mp/subscribemsg?' . http_build_query($params) . '#wechat_redirect';
        
        return [
            'auth_url' => $authUrl,
            'template_id' => $templateId,
            'app_id' => $officialSetting['app_id']
        ];
    }


}