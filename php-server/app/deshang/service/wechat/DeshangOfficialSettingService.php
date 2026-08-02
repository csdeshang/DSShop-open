<?php


namespace app\deshang\service\wechat;

use app\deshang\service\BaseDeshangService;

use app\common\dao\wechat\WechatSettingDao;


class DeshangOfficialSettingService extends BaseDeshangService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getWechatOfficialSetting()
    {
        $wechat_setting = (new WechatSettingDao())->getPlatformWechatSetting();

        $wechat_official_setting = isset($wechat_setting['wechat_official_setting']) ? json_decode($wechat_setting['wechat_official_setting'], true) : [];

        $result = [
            'app_id' => $wechat_official_setting['app_id'] ?? '',
            'app_secret' => $wechat_official_setting['app_secret'] ?? '',
            'token' => $wechat_official_setting['token'] ?? '',
            'encoding_aes_key' => $wechat_official_setting['encoding_aes_key'] ?? '',
            'encryption_type' => $wechat_official_setting['encryption_type'] ?? '',
        ];

        return $result;
    }


    public function updateWechatOfficialSetting(array $data)
    {
        $wechat_setting = (new WechatSettingDao())->getPlatformWechatSetting();

        $wechat_official_setting = [
            'app_id' => $data['app_id'] ?? '',
            'app_secret' => $data['app_secret'] ?? '',
            'token' => $data['token'] ?? '',
            'encoding_aes_key' => $data['encoding_aes_key'] ?? '',
            'encryption_type' => $data['encryption_type'] ?? '',
        ];

        if (empty($wechat_setting)) {
            $update_data = [
                'wechat_official_setting' => json_encode($wechat_official_setting),
            ];
            $result = (new WechatSettingDao())->createWechatSetting($update_data);
        } else {
            $update_data = [
                'wechat_official_setting' => json_encode($wechat_official_setting),
            ];
            $result = (new WechatSettingDao())->updateWechatSetting([['id', '=', $wechat_setting['id']]], $update_data);
        }

        return $result;
    }
}
