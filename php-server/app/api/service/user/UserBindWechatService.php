<?php



namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;

use app\common\dao\user\UserIdentityDao;

class UserBindWechatService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();
    }




    // 获取用户微信绑定状态
    function getWechatBindStatus()
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = ['wx_unionid', '<>', ''];
        $result = (new UserIdentityDao())->getUserIdentityInfo($condition);
        if (empty($result)) {
            return false;
        } else {
            return true;
        }
    }

    // 解绑微信
    function deleteBindWechat()
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = ['wx_unionid', '<>', ''];
        $result = (new UserIdentityDao())->deleteUserIdentity($condition);
        return $result;
    }
}
