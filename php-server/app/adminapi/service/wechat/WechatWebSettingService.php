<?php


namespace app\adminapi\service\wechat;


use app\deshang\base\service\BaseAdminService;

use app\deshang\service\wechat\DeshangWebSettingService;

class WechatWebSettingService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getWechatWebSetting()
    {
        return (new DeshangWebSettingService())->getWechatWebSetting();
    }

    public function updateWechatWebSetting(array $data)
    {
        return (new DeshangWebSettingService())->updateWechatWebSetting($data);
    }

}
