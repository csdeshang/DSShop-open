<?php


namespace app\adminapi\service\wechat;


use app\deshang\base\service\BaseAdminService;

use app\deshang\service\wechat\DeshangOfficialSettingService;

class WechatOfficialSettingService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getWechatOfficialSetting()
    {
        return (new DeshangOfficialSettingService())->getWechatOfficialSetting();
    }

    public function updateWechatOfficialSetting(array $data)
    {
        return (new DeshangOfficialSettingService())->updateWechatOfficialSetting($data);
    }

}
