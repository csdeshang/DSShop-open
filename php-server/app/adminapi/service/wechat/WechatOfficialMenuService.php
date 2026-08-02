<?php


namespace app\adminapi\service\wechat;


use app\deshang\base\service\BaseAdminService;

use app\deshang\service\wechat\DeshangOfficialMenuService;

class WechatOfficialMenuService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取微信公众号菜单
     * @return array
     */
    public function getWechatOfficialMenu()
    {
        return (new DeshangOfficialMenuService())->getWechatOfficialMenu();
    }


    /**
     * 更新微信公众号菜单
     * @param array $data
     * @return array
     */
    public function updateWechatOfficialMenu(array $data)
    {
        return (new DeshangOfficialMenuService())->updateWechatOfficialMenu($data);
    }


    /**
     * 发布微信公众号菜单
     * @return array
     */
    public function publishWechatOfficialMenu()
    {
        return (new DeshangOfficialMenuService())->publishWechatOfficialMenu();
    }
}
