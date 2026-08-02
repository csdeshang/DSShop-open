<?php

namespace app\api\service\video;

use app\deshang\base\service\BaseApiService;
use app\common\dao\video\VideoCommentDao;
use app\common\dao\video\VideoCommentLikeDao;

use app\common\enum\video\VideoEnum;

use app\deshang\exceptions\CommonException;




class VideoCommentService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
    }

    // 评论分页
    public function getVideoCommentPages($data)
    {
        $condition = [];

        if (!array_key_exists($data['content_type'], VideoEnum::getContentTypeDict())) {
            throw new CommonException('内容类型错误');
        }
        

        // 内容类型
        $condition[] = ['content_type', '=', $data['content_type']];
        // 内容ID
        $condition[] = ['content_id', '=', $data['content_id']];
        // 父评论ID
        $condition[] = ['pid', '=', $data['pid']];
        $condition[] = ['is_deleted', '=', 0];
        $condition[] = ['is_show', '=', 1];

        $result = (new VideoCommentDao())->getWithRelVideoCommentPages($condition);

        // 如果用户登录 则返回用户点赞状态
        if ($this->user_id && !empty($result['data'])) {
            // 批量获取用户对这些评论的点赞状态
            $commentIds = array_column($result['data'], 'id');
            $comment_ids = (new VideoCommentLikeDao())->getVideoCommentLikeColumn([
                ['user_id', '=', $this->user_id],
                ['comment_id', 'in', $commentIds]
            ], 'comment_id');
            foreach ($result['data'] as &$comment) {
                $comment['is_liked'] = in_array($comment['id'], $comment_ids);
            }
        } else {
            foreach ($result['data'] as &$comment) {
                $comment['is_liked'] = false;
            }
        }

        return $result;
    }




}