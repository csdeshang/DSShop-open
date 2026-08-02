<?php

namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;

use app\deshang\exceptions\CommonException;

use app\common\enum\video\VideoEnum;

use app\common\dao\video\VideoLikeDao;
use app\common\dao\video\VideoShortDao;
use app\common\dao\blogger\BloggerDao;
use think\facade\Db;

class UserVideoLikeService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 点赞/取消点赞
     */
    public function toggleVideoLike($data)
    {

        $blogger_id = 0;

        // 内容是否存在并获取原始点赞数
        $originalLikeCount = 0;
        switch ($data['content_type']) {
            case VideoEnum::CONTENT_TYPE_SHORT:
                $video_short = (new VideoShortDao())->getVideoShortInfoById($data['content_id']);
                if (empty($video_short)) {
                    throw new CommonException('短视频不存在');
                }
                $originalLikeCount = $video_short['like_count'] ?? 0;
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

        // 检查用户是否已经点赞
        $likeDao = new VideoLikeDao();
        $existingLike = $likeDao->getVideoLikeInfo([
            ['user_id', '=', $this->user_id],
            ['content_id', '=', $data['content_id']],
            ['content_type', '=', $data['content_type']]
        ]);

        // 事务处理
        Db::startTrans();
        try {
            if ($existingLike) {
                // 已点赞，执行取消点赞
                $likeDao->deleteVideoLike([['id', '=', $existingLike['id']]]);
                $this->decreaseContentLikeCount($data);

                // 减少博主总收藏
                (new BloggerDao())->setBloggerDec([['id', '=', $blogger_id]], 'total_likes');

                $action = 'cancel';
                $is_liked = false;
                $newLikeCount = max(0, $originalLikeCount - 1); // 防止负数
            } else {
                // 未点赞，执行点赞
                $likeData = [
                    'user_id' => $this->user_id,
                    'content_id' => $data['content_id'],
                    'content_type' => $data['content_type'],
                ];
                $likeDao->createVideoLike($likeData);
                $this->increaseContentLikeCount($data);

                //增加博主总收藏
                (new BloggerDao())->setBloggerInc([['id', '=', $blogger_id]], 'total_likes');

                $action = 'like';
                $is_liked = true;
                $newLikeCount = $originalLikeCount + 1;
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
            'is_liked' => $is_liked,
            'like_count' => $newLikeCount
        ];
    }

    /**
     * 增加内容点赞数
     */
    private function increaseContentLikeCount($data)
    {
        switch ($data['content_type']) {
            case VideoEnum::CONTENT_TYPE_SHORT:
                (new VideoShortDao())->setVideoShortInc([['id', '=', $data['content_id']]], 'like_count');
                break;
            // case VideoEnum::CONTENT_TYPE_DRAMA:
            //     (new VideoDramaDao())->setVideoDramaInc([['id', '=', $data['content_id']]], 'like_count');
            //     break;
            // case VideoEnum::CONTENT_TYPE_LIVE:
            //     (new VideoLiveDao())->setVideoLiveInc([['id', '=', $data['content_id']]], 'like_count');
            //     break;
        }
    }

    /**
     * 减少内容点赞数
     */
    private function decreaseContentLikeCount($data)
    {
        switch ($data['content_type']) {
            case VideoEnum::CONTENT_TYPE_SHORT:
                (new VideoShortDao())->setVideoShortDec([['id', '=', $data['content_id']]], 'like_count');
                break;
            // case VideoEnum::CONTENT_TYPE_DRAMA:
            //     (new VideoDramaDao())->setVideoDramaDec([['id', '=', $data['content_id']]], 'like_count');
            //     break;
            // case VideoEnum::CONTENT_TYPE_LIVE:
            //     (new VideoLiveDao())->setVideoLiveDec([['id', '=', $data['content_id']]], 'like_count');
            //     break;
        }
    }



}
