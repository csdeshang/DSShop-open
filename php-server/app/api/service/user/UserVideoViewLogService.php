<?php

namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;
use app\common\dao\video\VideoViewLogDao;
use app\common\dao\video\VideoShortDao;
use app\common\dao\blogger\BloggerDao;

use app\common\model\video\VideoViewLogModel;

use app\deshang\exceptions\CommonException;

class UserVideoViewLogService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取用户视频浏览记录列表
     */
    public function getVideoViewLogPages($data)
    {
        $condition = [];
        $condition[] = ['content_type', '=', $data['content_type']];
        $condition[] = ['user_id', '=', $this->user_id];


        switch ($data['content_type']) {
            case 'short':

                $result = (new VideoViewLogModel())->where($condition)
                    ->with([
                        'videoShort' => function ($query) {
                            $query->field('id,blogger_id,type,title,description,cover_image');
                        },
                    ])
                    ->order('update_at desc');
                return $this->getPaginate($result);

                break;
            case 'drama':
                // TODO: 当短剧功能实现时补充
                break;
            case 'live':
                // TODO: 当直播功能实现时补充
                break;
            default:
                throw new CommonException('内容类型不正确');
        }
    }

    /**
     * 创建视频浏览记录
     * 
     * @param string $contentType 内容类型 short/drama/live
     * @param int $contentId 内容ID
     * @return array
     */
    public function createVideoViewLog(string $contentType, int $contentId)
    {
        // 验证内容类型
        if (!in_array($contentType, ['short', 'drama', 'live'])) {
            throw new CommonException('内容类型不正确');
        }

        $contentInfo = [];
        // 根据内容类型获取数据信息
        switch ($contentType) {
            case 'short':
                $contentInfo = (new VideoShortDao())->getVideoShortInfo([['id', '=', $contentId]]);
                break;
            case 'drama':
                // TODO: 当短剧功能实现时补充
                break;
            case 'live':
                // TODO: 当直播功能实现时补充
                break;
            default:
                throw new CommonException('内容类型不正确');
        }
        if (empty($contentInfo)) {
            throw new CommonException('内容不存在');
        }

        // 获取用户IP
        $ipAddress = request()->ip();

        // 查看是否存在记录
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = ['content_type', '=', $contentType];
        $condition[] = ['content_id', '=', $contentId];
        $viewLog = (new VideoViewLogDao())->getVideoViewLogInfo($condition);

        $viewLogData = [
            'user_id' => $this->user_id,
            'content_type' => $contentType,
            'content_id' => $contentId,
            'blogger_id' => $contentInfo['blogger_id'],
            'ip_address' => $ipAddress,
        ];
        // 不存在记录，则创建记录
        if (empty($viewLog)) {
            $viewLog = (new VideoViewLogDao())->createVideoViewLog($viewLogData);
        } else {
            $viewLog = (new VideoViewLogDao())->updateVideoViewLog([['id', '=', $viewLog['id']]], $viewLogData);
        }

        // 更新内容观看次数
        switch ($contentType) {
            case 'short':
                // 更新内容观看次数
                (new VideoShortDao())->setVideoShortInc([['id', '=', $contentId]], 'view_count');
                // 更新博主总观看次数
                (new BloggerDao())->setBloggerInc([['id', '=', $contentInfo['blogger_id']]], 'total_views');
                break;
            case 'drama':
                // TODO: 当短剧功能实现时补充
                break;
            case 'live':
                // TODO: 当直播功能实现时补充
                break;
            default:
                throw new CommonException('内容类型不正确');
        }



        return true;
    }


    /**
     * 批量删除视频浏览记录
     * 
     * @param array $ids 记录ID数组
     * @return int
     */
    public function batchDeleteVideoViewLog(array $ids): int
    {
        $condition = [];
        $condition[] = ['id', 'in', $ids];
        $condition[] = ['user_id', '=', $this->user_id];

        $result = (new VideoViewLogDao())->deleteVideoViewLog($condition);
        return $result;
    }

    /**
     * 清空用户所有浏览记录
     * 
     * @return int
     */
    public function clearAllVideoViewLog($content_type): int
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = ['content_type', '=', $content_type];

        $result = (new VideoViewLogDao())->deleteVideoViewLog($condition);
        return $result;
    }

    /**
     * 删除单个视频浏览记录
     * 
     * @param int $id 记录ID
     * @return int
     */
    public function deleteVideoViewLog(int $id): int
    {
        $condition = [];
        $condition[] = ['id', '=', $id];
        $condition[] = ['user_id', '=', $this->user_id];

        $result = (new VideoViewLogDao())->deleteVideoViewLog($condition);
        return $result;
    }

    /**
     * 获取用户浏览记录详情
     * 
     * @param int $id 记录ID
     * @return array
     */
    public function getVideoViewLogInfo(int $id): array
    {
        $condition = [];
        $condition[] = ['id', '=', $id];
        $condition[] = ['user_id', '=', $this->user_id];

        $result = (new VideoViewLogDao())->getVideoViewLogInfo($condition);
        return $result;
    }
}
