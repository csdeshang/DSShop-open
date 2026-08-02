<?php


namespace app\adminapi\service\wechat;


use app\deshang\base\service\BaseAdminService;
use app\deshang\service\wechat\DeshangAppSettingService;

class WechatAppSettingService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getWechatAppSetting()
    {
        return (new DeshangAppSettingService())->getWechatAppSetting();
    }

    public function updateWechatAppSetting(array $data)
    {
        return (new DeshangAppSettingService())->updateWechatAppSetting($data);
    }

}
