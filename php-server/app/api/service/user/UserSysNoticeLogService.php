<?php



namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;

use app\common\dao\system\SysNoticeLogDao;
use app\common\enum\system\SysNoticeEnum;


class UserSysNoticeLogService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();

    }




    // 获取用户消息通知
    function getUserSysNoticeLogPages(array $data) : array
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        // 站内通知
        $condition[] = ['notice_channel', '=', SysNoticeEnum::CHANNEL_INTERNAL];
        $result = (new SysNoticeLogDao())->getSysNoticeLogPages($condition,'*','is_read asc,id desc');
        return $result;
    }


    // 获取用户消息通知详情
    function getUserSysNoticeLogInfo(int $id) : array
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = ['id', '=', $id];
        $condition[] = ['notice_channel', '=', SysNoticeEnum::CHANNEL_INTERNAL];
        $result = (new SysNoticeLogDao())->getSysNoticeLogInfo($condition);

        if ($result) {
            // 更新消息状态为已读
            (new SysNoticeLogDao())->updateSysNoticeLog([['id', '=', $id]], ['is_read' => 1]);
        }
        return $result;
    }






}