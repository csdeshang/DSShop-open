<?php


namespace app\adminapi\service\wechat;


use app\deshang\base\service\BaseAdminService;

use app\deshang\service\wechat\DeshangMiniSettingService;

class WechatMiniSettingService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getWechatMiniSetting()
    {
        return (new DeshangMiniSettingService())->getWechatMiniSetting();
    }

    public function updateWechatMiniSetting(array $data)
    {
        return (new DeshangMiniSettingService())->updateWechatMiniSetting($data);
    }

}
