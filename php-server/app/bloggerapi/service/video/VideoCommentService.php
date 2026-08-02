<?php

namespace app\bloggerapi\service\video;

use app\deshang\base\service\BaseBloggerService;
use app\common\dao\video\VideoCommentDao;
use app\deshang\exceptions\CommonException;

class VideoCommentService extends BaseBloggerService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 切换视频评论字段状态
     * 
     * @param array $data 包含 id 和 field 的数据
     * @return bool
     */
    public function toggleVideoCommentField(array $data): bool
    {
        // 验证评论是否存在
        $commentDao = new VideoCommentDao();
        $comment = $commentDao->getVideoCommentInfo([['id', '=', $data['id']]]);
        
        if (empty($comment)) {
            throw new CommonException('评论不存在');
        }

        // 是否属于博主
        if ($comment['blogger_id'] != $this->blogger_id) {
            throw new CommonException('评论不属于博主');
        }

        // 获取当前字段值
        $currentValue = $comment[$data['field']] ?? 0;
        $newValue = $currentValue ? 0 : 1;

        // 更新字段值
        $updateData = [
            $data['field'] => $newValue,
        ];

        $result = $commentDao->updateVideoComment([['id', '=', $data['id']]], $updateData);
        
        if (!$result) {
            throw new CommonException('切换失败');
        }

        return true;
    }

} 