<?php

namespace app\riderapi\service\rider;

use app\deshang\base\service\BaseRiderService;
use app\common\dao\rider\RiderCommentDao;
use app\deshang\exceptions\CommonException;

class RiderCommentService extends BaseRiderService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取骑手评论分页列表
     * @param array $data
     * @return array
     */
    public function getRiderCommentPages(array $data): array
    {
        $condition = [];
        
        // 只查询当前骑手的评论
        $condition[] = ['rider_id', '=', $this->rider_id];
        
        // 是否回复筛选
        if (in_array($data['is_reply'], [0, 1])) {
            $condition[] = ['is_reply', '=', $data['is_reply']];
        }
        
        // 是否显示筛选
        if (in_array($data['is_show'], [0, 1])) {
            $condition[] = ['is_show', '=', $data['is_show']];
        }

        $result = (new RiderCommentDao())->getWithRelRiderCommentPages($condition);
        return $result;
    }

    /**
     * 获取骑手评论详情
     * @param array $data
     * @return array
     */
    public function getRiderCommentInfo(array $data): array
    {
        $condition = [];
        $condition[] = ['id', '=', $data['id']];
        $condition[] = ['rider_id', '=', $this->rider_id]; // 只能查看自己的评论

        $result = (new RiderCommentDao())->getWithRelRiderCommentInfo($condition);
        
        if (empty($result)) {
            throw new CommonException('评论不存在或无权限查看');
        }

        return $result;
    }

    /**
     * 回复骑手评论
     * @param array $data
     * @return bool
     */
    public function replyRiderComment(array $data): bool
    {
        // 先检查评论是否存在且属于当前骑手
        $condition = [];
        $condition[] = ['id', '=', $data['id']];
        $condition[] = ['rider_id', '=', $this->rider_id];

        $comment = (new RiderCommentDao())->getRiderCommentInfo($condition);
        
        if (empty($comment)) {
            throw new CommonException('评论不存在或无权限操作');
        }

        // 检查是否已经回复过
        if ($comment['is_reply'] == 1) {
            throw new CommonException('该评论已经回复过了');
        }

        // 更新回复内容
        $update_data = [
            'reply_content' => $data['reply_content'],
            'reply_time' => time(),
            'is_reply' => 1,
        ];

        $result = (new RiderCommentDao())->updateRiderComment($condition, $update_data);
        
        if (!$result) {
            throw new CommonException('回复失败');
        }

        return true;
    }
} 