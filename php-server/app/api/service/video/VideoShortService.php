<?php

namespace app\api\service\video;

use app\deshang\base\service\BaseApiService;

use app\common\dao\video\VideoShortDao;
use app\common\dao\video\VideoShortGoodsRelDao;
use app\common\dao\goods\TblGoodsDao;
use app\common\dao\video\VideoLikeDao;
use app\common\dao\video\VideoCollectDao;
use app\common\dao\blogger\BloggerFollowDao;

use app\common\model\video\VideoShortModel;

use app\common\enum\video\VideoShortEnum;
use app\common\enum\video\VideoEnum;

use think\facade\Db;


class VideoShortService extends BaseApiService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getVideoShortPages($data)
    {
        $condition = [];
        // 审核状态
        $condition[] = ['audit_status', '=', VideoShortEnum::AUDIT_STATUS_PASS];

        // 查询类型：default(默认) | user_liked(用户点赞) | user_collected(用户收藏)
        $query_type = $data['query_type'] ?? 'default';


        if ($query_type == 'user_liked') {
            // 获取某用户点赞的短视频 数组 (用户点赞基本1W条以内)
            $user_id = $data['user_id'];

            $likeCondition = [
                ['user_id', '=', $user_id],
                ['content_type', '=', VideoEnum::CONTENT_TYPE_SHORT]
            ];
            $likedVideoIds = (new VideoLikeDao())->getVideoLikeColumn($likeCondition, 'content_id');

            $condition[] = ['id', 'in', $likedVideoIds];
        } elseif ($query_type == 'user_collected') {
            // 获取某用户收藏的短视频 数组 (用户收藏基本1W条以内)
            $user_id = $data['user_id'];
            $collectCondition = [
                ['user_id', '=', $user_id],
                ['content_type', '=', VideoEnum::CONTENT_TYPE_SHORT]
            ];
            $collectVideoIds = (new VideoCollectDao())->getVideoCollectColumn($collectCondition, 'content_id');

            $condition[] = ['id', 'in', $collectVideoIds];
        } else {
            // 审核通过
            $condition[] = ['audit_status', '=', VideoShortEnum::AUDIT_STATUS_PASS];
            // 博主ID
            if ($data['blogger_id'] > 0) {
                $condition[] = ['blogger_id', '=', $data['blogger_id']];
            }
        }

        // 类型
        if (array_key_exists($data['type'], VideoShortEnum::getTypeDict())) {
            $condition[] = ['type', '=', $data['type']];
        }

        // 指定要放在第一位的视频ID
        $specified_id = $data['specified_id'] > 0 ? $data['specified_id'] : 0;
        $page_current = intval(input('param.page_current')) > 0 ? intval(input('param.page_current')) : 1;

        // 如果指定了要显示在第一位的视频ID
        if ($specified_id > 0 && $page_current == 1) {
           
            // 先排除指定的ID，避免重复
            $condition[] = ['id', '<>', $specified_id];
            // 获取列表数据
            $result = (new VideoShortDao)->getWithRelVideoShortPages($condition);
            // 获取指定ID的视频详情
            $specifiedVideo = (new VideoShortDao)->getWithRelVideoShortInfo([['id', '=', $specified_id]]);

            if ($specifiedVideo) {
                // 将指定视频插入到列表第一位
                array_unshift($result['data'], $specifiedVideo);
            }
        } else {
            // 正常获取分页数据
            $result = (new VideoShortDao)->getWithRelVideoShortPages($condition);
        }

        // 如果用户登录 则返回用户交互状态
        if ($this->user_id && !empty($result['data'])) {
            // 批量获取用户对这些视频的交互状态
            $videoIds = array_column($result['data'], 'id');
            $bloggerIds = array_unique(array_filter(array_column($result['data'], 'blogger_id')));
            
            // 批量获取点赞状态
            $likedVideoIds = (new VideoLikeDao())->getVideoLikeColumn([
                ['user_id', '=', $this->user_id],
                ['content_type', '=', VideoEnum::CONTENT_TYPE_SHORT],
                ['content_id', 'in', $videoIds]
            ], 'content_id');
            
            // 批量获取收藏状态
            $collectedVideoIds = (new VideoCollectDao())->getVideoCollectColumn([
                ['user_id', '=', $this->user_id],
                ['content_type', '=', VideoEnum::CONTENT_TYPE_SHORT],
                ['content_id', 'in', $videoIds]
            ], 'content_id');
            
            // 批量获取关注状态
            $followedBloggerIds = [];
            if (!empty($bloggerIds)) {
                $followedBloggerIds = (new BloggerFollowDao())->getBloggerFollowColumn([
                    ['user_id', '=', $this->user_id],
                    ['blogger_id', 'in', $bloggerIds]
                ], 'blogger_id');
            }
            
            // 为每个视频设置交互状态
            foreach ($result['data'] as &$video) {
                $video['is_liked'] = in_array($video['id'], $likedVideoIds);
                $video['is_collected'] = in_array($video['id'], $collectedVideoIds);
                
                // 设置博主关注状态
                if (isset($video['blogger']) && isset($video['blogger_id'])) {
                    $video['blogger']['is_followed'] = in_array($video['blogger_id'], $followedBloggerIds);
                }
            }
        } else {
            // 未登录用户，设置默认状态
            foreach ($result['data'] as &$video) {
                $video['is_liked'] = false;
                $video['is_collected'] = false;
                if (isset($video['blogger'])) {
                    $video['blogger']['is_followed'] = false;
                }
            }
        }

        return $result;
    }


    // 获取短视频详情
    public function getVideoShortDetail($id)
    {
        $result = (new VideoShortDao)->getWithRelVideoShortInfo([['id', '=', $id]]);

        // 获取关联商品
        $result['goods_list'] = $this->getVideoShortGoodsList($id);

        // 处理images_url为数组
        if ($result['type'] == VideoShortEnum::TYPE_IMAGE && !empty($result['images_url'])) {
            $result['images_url'] = explode(',', $result['images_url']);
        }

        // 如果用户登录 则返回用户交互状态
        if ($this->user_id) {
            // 获取点赞状态
            $videoLikeCount = (new VideoLikeDao())->getVideoLikeCount([
                ['user_id', '=', $this->user_id],
                ['content_id', '=', $id],
                ['content_type', '=', VideoEnum::CONTENT_TYPE_SHORT]
            ]);
            $result['is_liked'] = $videoLikeCount > 0;

            // 获取收藏状态
            $videoCollectCount = (new VideoCollectDao())->getVideoCollectCount([
                ['user_id', '=', $this->user_id],
                ['content_id', '=', $id],
                ['content_type', '=', VideoEnum::CONTENT_TYPE_SHORT]
            ]);
            $result['is_collected'] = $videoCollectCount > 0;

            // 获取关注状态
            if (isset($result['blogger_id']) && isset($result['blogger'])) {
                $bloggerFollowCount = (new BloggerFollowDao())->getBloggerFollowCount([
                    ['user_id', '=', $this->user_id],
                    ['blogger_id', '=', $result['blogger_id']]
                ]);
                $result['blogger']['is_followed'] = $bloggerFollowCount > 0;
            }
        } else {
            // 未登录用户，设置默认状态
            $result['is_liked'] = false;
            $result['is_collected'] = false;
            if (isset($result['blogger'])) {
                $result['blogger']['is_followed'] = false;
            }
        }

        return $result;
    }


    // 获取短视频关联商品列表
    public function getVideoShortGoodsList($id)
    {
        $goodsIds = (new VideoShortGoodsRelDao())->getVideoShortGoodsRelColumn([['video_short_id', '=', $id]], 'goods_id');
        $goodsList = (new TblGoodsDao())->getWithRelGoodsList([['id', 'in', $goodsIds]]);
        return $goodsList;
    }
}
