<?php


namespace app\deshang\service\wechat;

use app\deshang\service\BaseDeshangService;

use app\common\dao\wechat\WechatSettingDao;


class DeshangMiniSettingService extends BaseDeshangService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getWechatMiniSetting()
    {
        $wechat_setting = (new WechatSettingDao())->getPlatformWechatSetting();

        $wechat_mini_setting = isset($wechat_setting['wechat_mini_setting']) ? json_decode($wechat_setting['wechat_mini_setting'], true) : [];

        $result = [
            'app_id' => $wechat_mini_setting['app_id'] ?? '',
            'app_secret' => $wechat_mini_setting['app_secret'] ?? '',
        ];

        return $result;
    }


    public function updateWechatMiniSetting(array $data)
    {
        $wechat_setting = (new WechatSettingDao())->getPlatformWechatSetting();

        $wechat_mini_setting = [
            'app_id' => $data['app_id'] ?? '',
            'app_secret' => $data['app_secret'] ?? '',
        ];

        if (empty($wechat_setting)) {
            $update_data = [
                'wechat_mini_setting' => json_encode($wechat_mini_setting),
            ];
            $result = (new WechatSettingDao())->createWechatSetting($update_data);
        } else {
            $update_data = [
                'wechat_mini_setting' => json_encode($wechat_mini_setting),
            ];
            $result = (new WechatSettingDao())->updateWechatSetting([['id', '=', $wechat_setting['id']]], $update_data);
        }

        return $result;
    }
}
