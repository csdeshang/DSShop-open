<?php

namespace app\api\service\user;

use app\api\controller\blogger\Blogger;
use app\deshang\base\service\BaseUserService;

use app\deshang\exceptions\CommonException;

use app\common\enum\video\VideoEnum;

use app\common\dao\video\VideoCollectDao;
use app\common\dao\video\VideoShortDao;
use app\common\dao\blogger\BloggerDao;

use think\facade\Db;

class UserVideoCollectService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 收藏/取消收藏
     */
    public function toggleVideoCollect($data)
    {

        $blogger_id = 0;

        // 内容是否存在并获取原始收藏数
        $originalCollectCount = 0;
        switch ($data['content_type']) {
            case VideoEnum::CONTENT_TYPE_SHORT:
                $video_short = (new VideoShortDao())->getVideoShortInfoById($data['content_id']);
                if (empty($video_short)) {
                    throw new CommonException('短视频不存在');
                }
                $originalCollectCount = $video_short['collect_count'] ?? 0;
                $blogger_id = $video_short['blogger_id'];
                break;
            // case VideoEnum::CONTENT_TYPE_DRAMA:
            //     $video_drama = (new VideoDramaDao())->getVideoById($data['content_id']);
            //     if (empty($video_drama)) {
            //         throw new CommonException('短剧不存在');
            //     }
            //     break;
            // case VideoEnum::CONTENT_TYPE_LIVE:
            //     $video_live = (new VideoLiveDao())->getVideoById($data['content_id']);
            //     if (empty($video_live)) {
            //         throw new CommonException('直播不存在');
            //     }
            //     break;
            default:
                throw new CommonException('内容类型错误');
        }

        // 检查用户是否已经收藏
        $collectDao = new VideoCollectDao();
        $existingCollect = $collectDao->getVideoCollectInfo([
            ['user_id', '=', $this->user_id],
            ['content_id', '=', $data['content_id']],
            ['content_type', '=', $data['content_type']]
        ]);

        // 事务处理
        Db::startTrans();
        try {
            if ($existingCollect) {
                // 已收藏，执行取消收藏
                $collectDao->deleteVideoCollect([['id', '=', $existingCollect['id']]]);
                // 减少 短剧，短视频 收藏量
                $this->decreaseContentCollectCount($data);

                // 减少博主总收藏
                (new BloggerDao())->setBloggerDec([['id', '=', $blogger_id]], 'total_collect');




                $action = 'cancel';
                $is_collected = false;
                $newCollectCount = max(0, $originalCollectCount - 1); // 防止负数
            } else {
                // 未收藏，执行收藏
                $collectData = [
                    'user_id' => $this->user_id,
                    'content_id' => $data['content_id'],
                    'content_type' => $data['content_type'],
                ];
                $collectDao->createVideoCollect($collectData);
                // 增加 短剧，短视频 收藏量
                $this->increaseContentCollectCount($data);

                //增加博主总收藏
                (new BloggerDao())->setBloggerInc([['id', '=', $blogger_id]], 'total_collect');

                $action = 'collect';
                $is_collected = true;
                $newCollectCount = $originalCollectCount + 1;
            }

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            throw new CommonException('操作失败：' . $e->getMessage());
        }

        return [
            'action' => $action,
            'is_collected' => $is_collected,
            'collect_count' => $newCollectCount
        ];
    }

    /**
     * 增加内容收藏数
     */
    private function increaseContentCollectCount($data)
    {
        switch ($data['content_type']) {
            case VideoEnum::CONTENT_TYPE_SHORT:
                (new VideoShortDao())->setVideoShortInc([['id', '=', $data['content_id']]], 'collect_count');
                break;
            // case VideoEnum::CONTENT_TYPE_DRAMA:
            //     (new VideoDramaDao())->setVideoDramaInc([['id', '=', $data['content_id']]], 'collect_count');
            //     break;
            // case VideoEnum::CONTENT_TYPE_LIVE:
            //     (new VideoLiveDao())->setVideoLiveInc([['id', '=', $data['content_id']]], 'collect_count');
            //     break;
        }
    }

    /**
     * 减少内容收藏数
     */
    private function decreaseContentCollectCount($data)
    {
        switch ($data['content_type']) {
            case VideoEnum::CONTENT_TYPE_SHORT:
                (new VideoShortDao())->setVideoShortDec([['id', '=', $data['content_id']]], 'collect_count');
                break;
            // case VideoEnum::CONTENT_TYPE_DRAMA:
            //     (new VideoDramaDao())->setVideoDramaDec([['id', '=', $data['content_id']]], 'collect_count');
            //     break;
            // case VideoEnum::CONTENT_TYPE_LIVE:
            //     (new VideoLiveDao())->setVideoLiveDec([['id', '=', $data['content_id']]], 'collect_count');
            //     break;
        }
    }



}
