<?php


namespace app\deshang\service\wechat;

use app\deshang\service\BaseDeshangService;

use app\common\dao\wechat\WechatSettingDao;


class DeshangAppSettingService extends BaseDeshangService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getWechatAppSetting()
    {
        $wechat_setting = (new WechatSettingDao())->getPlatformWechatSetting();

        $wechat_app_setting = isset($wechat_setting['wechat_app_setting']) ? json_decode($wechat_setting['wechat_app_setting'], true) : [];

        $result = [
            'app_id' => $wechat_app_setting['app_id'] ?? '',
            'app_secret' => $wechat_app_setting['app_secret'] ?? '',
        ];

        return $result;
    }


    public function updateWechatAppSetting(array $data)
    {
        $wechat_setting = (new WechatSettingDao())->getPlatformWechatSetting();

        $wechat_app_setting = [
            'app_id' => $data['app_id'] ?? '',
            'app_secret' => $data['app_secret'] ?? '',
        ];

        if (empty($wechat_setting)) {
            $update_data = [
                'wechat_app_setting' => json_encode($wechat_app_setting),
            ];
            $result = (new WechatSettingDao())->createWechatSetting($update_data);
        } else {
            $update_data = [
                'wechat_app_setting' => json_encode($wechat_app_setting),
            ];
            $result = (new WechatSettingDao())->updateWechatSetting([['id', '=', $wechat_setting['id']]], $update_data);
        }

        return $result;
    }
}
