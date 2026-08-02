<?php

namespace app\bloggerapi\service\video;

use think\facade\Db;

use app\deshang\base\service\BaseBloggerService;

use app\common\enum\video\VideoEnum;
use app\common\enum\video\VideoShortEnum;

use app\common\dao\blogger\BloggerDao;

use app\common\dao\video\VideoShortDao;
use app\common\dao\video\VideoShortGoodsRelDao;
use app\common\dao\video\VideoCommentDao;
use app\common\dao\video\VideoCommentLikeDao;
use app\common\dao\video\VideoLikeDao;
use app\common\dao\video\VideoCollectDao;
use app\common\dao\video\VideoShareDao;

use app\deshang\exceptions\CommonException;

class VideoShortService extends BaseBloggerService
{

    public function __construct()
    {
        parent::__construct();
    }

    public function publishVideoShort(array $data): int
    {
        $data['blogger_id'] = $this->blogger_id;
        $data['create_at'] = time();
        $data['publish_at'] = time();

        if (!array_key_exists($data['type'], VideoShortEnum::getTypeDict())) {
            throw new CommonException('视频类型错误');
        }

        if ($data['type'] == VideoShortEnum::TYPE_VIDEO) {
            $data['images_url'] = '';
        }

        if ($data['type'] == VideoShortEnum::TYPE_IMAGE) {
            $data['video_url'] = '';
        }

        // 默认待审核状态
        $data['audit_status'] = VideoShortEnum::AUDIT_STATUS_WAIT;
        $data['audit_time'] = time();

        // 保存商品ID，创建视频时不需要这个字段
        $goods_ids = $data['goods_ids'] ?? [];
        unset($data['goods_ids']);

        $videoShortId = (new VideoShortDao())->createVideoShort($data);

        // 批量添加商品关联
        if (!empty($goods_ids)) {
            $goodsRelData = [];
            foreach ($goods_ids as $goods_id) {
                $goodsRelData[] = [
                    'video_short_id' => $videoShortId,
                    'goods_id' => $goods_id,
                    'create_at' => time()
                ];
            }
            (new VideoShortGoodsRelDao())->createVideoShortGoodsRelAll($goodsRelData);
        }

        // 博主的作品数量 + 1
        (new BloggerDao())->setBloggerInc(['id' => $this->blogger_id], 'video_count', 1);

        return $videoShortId;
    }


    /**
     * 硬删除短视频
     * 
     * @param int $id 短视频ID
     * @return bool
     */
    public function deleteVideoShort(int $id): bool
    {
        // 验证是否为当前博主的视频
        $videoShort = (new VideoShortDao())->getVideoShortInfo(['id' => $id, 'blogger_id' => $this->blogger_id]);
        if (!$videoShort) {
            throw new CommonException('视频不存在或无权限操作');
        }

        // 先获取该视频下的所有评论IDs
        $commentIds = (new VideoCommentDao())->getVideoCommentColumn(['content_id' => $id, 'content_type' => VideoEnum::CONTENT_TYPE_SHORT], 'id');


        Db::startTrans();
        try {
            // 删除评论点赞（根据评论IDs）
            if (!empty($commentIds)) {
                (new VideoCommentLikeDao())->deleteVideoCommentLike([['comment_id', 'in', $commentIds]]);
            }
            // 删除评论
            (new VideoCommentDao())->deleteVideoComment(['content_id' => $id, 'content_type' => VideoEnum::CONTENT_TYPE_SHORT]);
            // 删除点赞
            (new VideoLikeDao())->deleteVideoLike(['content_id' => $id, 'content_type' => VideoEnum::CONTENT_TYPE_SHORT]);
            // 删除收藏
            (new VideoCollectDao())->deleteVideoCollect(['content_id' => $id, 'content_type' => VideoEnum::CONTENT_TYPE_SHORT]);
            // 删除分享
            // (new VideoShareDao())->deleteVideoShare(['content_id' => $id, 'content_type' => VideoEnum::CONTENT_TYPE_SHORT]);
            // 删除视频
            (new VideoShortDao())->deleteVideoShort(['id' => $id, 'blogger_id' => $this->blogger_id]);
            // 删除商品关联
            (new VideoShortGoodsRelDao())->deleteVideoShortGoodsRel(['video_short_id' => $id]);

            // 博主的作品数量 - 1
            (new BloggerDao())->setBloggerDec(['id' => $this->blogger_id], 'video_count', 1);


            Db::commit();

        } catch (\Exception $e) {
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }


        return true;
    }

    /**
     * 获取博主短视频分页列表
     * 
     * @param array $data 查询条件
     * @return array
     */
    public function getVideoShortPages(array $data): array
    {
        $condition = [
            ['blogger_id', '=', $this->blogger_id] // 只能查看自己的视频
        ];

        // 标题搜索
        if (!empty($data['title'])) {
            $condition[] = ['title', 'like', '%' . $data['title'] . '%'];
        }

        // 内容类型筛选
        if (array_key_exists($data['type'], VideoShortEnum::getTypeDict())) {
            $condition[] = ['type', '=', $data['type']];
        }

        // 审核状态筛选
        if (array_key_exists($data['audit_status'], VideoShortEnum::getAuditStatusDict())) {
            $condition[] = ['audit_status', '=', $data['audit_status']];
        }

        // 分类筛选
        if (!empty($data['cid'])) {
            $condition[] = ['cid', '=', $data['cid']];
        }

        $result = (new VideoShortDao())->getVideoShortPages($condition);
        return $result;
    }
}
