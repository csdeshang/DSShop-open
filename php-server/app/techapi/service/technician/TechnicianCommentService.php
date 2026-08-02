<?php

namespace app\techapi\service\technician;

use app\deshang\base\service\BaseTechnicianService;
use app\common\dao\technician\TechnicianCommentDao;
use app\deshang\exceptions\CommonException;

class TechnicianCommentService extends BaseTechnicianService
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 获取师傅评论分页列表
     * @param array $data
     * @return array
     */
    public function getTechnicianCommentPages(array $data): array
    {
        $condition = [];
        
        // 只查询当前师傅的评论
        $condition[] = ['technician_id', '=', $this->technician_id];
        
        // 是否回复筛选
        if (in_array($data['is_reply'], [0, 1])) {
            $condition[] = ['is_reply', '=', $data['is_reply']];
        }
        
        // 是否显示筛选
        if (in_array($data['is_show'], [0, 1])) {
            $condition[] = ['is_show', '=', $data['is_show']];
        }

        $result = (new TechnicianCommentDao())->getWithRelTechnicianCommentPages($condition);
        return $result;
    }

    /**
     * 获取师傅评论详情
     * @param int $id
     * @return array
     */
    public function getTechnicianCommentInfo(int $id): array
    {
        $condition = [];
        $condition[] = ['id', '=', $id];
        $condition[] = ['technician_id', '=', $this->technician_id]; // 只能查看自己的评论

        $result = (new TechnicianCommentDao())->getWithRelTechnicianCommentInfo($condition);
        
        if (empty($result)) {
            throw new CommonException('评论不存在或无权限查看');
        }

        return $result;
    }

    /**
     * 回复师傅评论
     * @param array $data
     * @return bool
     */
    public function replyTechnicianComment(array $data): bool
    {
        // 先检查评论是否存在且属于当前师傅
        $condition = [];
        $condition[] = ['id', '=', $data['id']];
        $condition[] = ['technician_id', '=', $this->technician_id];

        $comment = (new TechnicianCommentDao())->getTechnicianCommentInfo($condition);
        
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

        $result = (new TechnicianCommentDao())->updateTechnicianComment($condition, $update_data);
        
        if (!$result) {
            throw new CommonException('回复失败');
        }

        return true;
    }


} 