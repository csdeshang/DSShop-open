<?php

namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;
use app\common\dao\video\VideoCommentDao;
use app\common\dao\video\VideoCommentLikeDao;
use app\common\dao\video\VideoShortDao;
use app\common\enum\video\VideoEnum;

use app\deshang\exceptions\AuthException;
use app\deshang\exceptions\CommonException;
use app\deshang\exceptions\NotFoundException;


use think\facade\Db;

class UserVideoCommentService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }



    // 创建评论
    public function createVideoComment($data)
    {
        // 必须登录
        if (!$this->user_id) {
            throw new AuthException('请先登录');
        }

        // 获取内容信息
        $content_info = [];

        // 内容是否存在
        switch ($data['content_type']) {
            case VideoEnum::CONTENT_TYPE_SHORT:
                $content_info = (new VideoShortDao())->getVideoShortInfoById($data['content_id']);

                break;
            // case VideoEnum::CONTENT_TYPE_DRAMA:
            //     $video_drama = (new VideoDramaDao())->getVideoById($data['content_id']);
            //     break;
            // case VideoEnum::CONTENT_TYPE_LIVE:
            //     $video_live = (new VideoLiveDao())->getVideoById($data['content_id']);
            //     break;
            default:
                throw new CommonException('内容类型错误');
        }
        if (empty($content_info)) {
            throw new NotFoundException('短视频不存在');
        }

        // 验证父评论是否存在
        if ($data['pid'] > 0) {
            $parent_comment = (new VideoCommentDao())->getVideoCommentInfo([['id', '=', $data['pid']]]);
            if (empty($parent_comment)) {
                throw new CommonException('父评论不存在');
            }
        }

        $comment_data = [
            'user_id' => $this->user_id,
            'blogger_id' => $content_info['blogger_id'],
            'content_type' => $data['content_type'],
            'content_id' => $data['content_id'],
            'comment_content' => $data['comment_content'],
            'pid' => $data['pid'],
            'like_count' => 0,
            'is_top' => 0,
            'is_deleted' => 0,
        ];

        $commentId = (new VideoCommentDao())->createVideoComment($comment_data);

        if ($commentId) {
            // 如果是回复，更新父评论的回复数
            if ($data['pid'] > 0) {
                // 10 秒内延迟
                (new VideoCommentDao())->setVideoCommentInc([['id', '=', $data['pid']]], 'reply_count', 1, 10);
            }

            // 更新内容表的评论数
            switch ($data['content_type']) {
                case VideoEnum::CONTENT_TYPE_SHORT:
                    (new VideoShortDao())->setVideoShortInc([['id', '=', $data['content_id']]], 'comment_count');
                    break;
                // case VideoEnum::CONTENT_TYPE_DRAMA:
                //     (new VideoDramaDao())->setVideoDramaInc($data['content_id'], 'comment_count');
                //     break;
                // case VideoEnum::CONTENT_TYPE_LIVE:
                //     (new VideoLiveDao())->setVideoLiveInc($data['content_id'], 'comment_count');
                //     break;
                default:
                    throw new CommonException('内容类型错误');
            }
        }

        return $commentId;
    }

    /**
     * 评论点赞/取消点赞
     */
    public function toggleVideoCommentLike($comment_id)
    {
        // 必须登录
        if (!$this->user_id) {
            throw new AuthException('请先登录');
        }

        $commentDao = new VideoCommentDao();
        $likeDao = new VideoCommentLikeDao();

        // 验证评论是否存在并获取原始点赞数
        $comment = $commentDao->getVideoCommentInfo([['id', '=', $comment_id]]);
        if (empty($comment)) {
            throw new NotFoundException('评论不存在');
        }
        $originalLikeCount = $comment['like_count'] ?? 0;

        // 事务处理
        Db::startTrans();
        try {
            // 检查用户是否已经点赞
            $existingLike = $likeDao->getVideoCommentLikeInfo([
                ['user_id', '=', $this->user_id],
                ['comment_id', '=', $comment_id]
            ]);
            
            if ($existingLike) {
                // 已点赞，执行取消点赞
                $likeDao->deleteVideoCommentLike([['id', '=', $existingLike['id']]]);
                $commentDao->setVideoCommentDec([['id', '=', $comment_id]], 'like_count');

                $action = 'cancel';
                $is_liked = false;
                $newLikeCount = max(0, $originalLikeCount - 1); // 防止负数
            } else {
                // 未点赞，执行点赞
                $likeData = [
                    'user_id' => $this->user_id,
                    'comment_id' => $comment_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $likeDao->createVideoCommentLike($likeData);
                $commentDao->setVideoCommentInc([['id', '=', $comment_id]], 'like_count');

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
}
