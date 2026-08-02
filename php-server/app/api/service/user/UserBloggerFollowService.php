<?php



namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;


use think\facade\Db;

use app\common\dao\user\UserDao;
use app\common\dao\blogger\BloggerDao;
use app\common\dao\blogger\BloggerFollowDao;

use app\deshang\exceptions\CommonException;


class UserBloggerFollowService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();

    }


    // 关注/取消关注
    public function toggleBloggerFollow($blogger_id)
    {

        $bloggerDao = new BloggerDao();
        $bloggerFollowDao = new BloggerFollowDao();

        // 验证博主是否存在并获取原始粉丝数
        $blogger = $bloggerDao->getBloggerInfoById($blogger_id);
        if (empty($blogger)) {
            throw new CommonException('博主不存在');
        }
        $originalFansCount = $blogger['follower_count'] ?? 0;

        // 不能关注自己
        // if ($blogger['user_id'] == $this->user_id) {
        //     throw new CommonException('不能关注自己');
        // }

        // 检查用户是否已经关注
        $existingFollow = $bloggerFollowDao->getBloggerFollowInfo([
            ['user_id', '=', $this->user_id],
            ['blogger_id', '=', $blogger_id]
        ]);

        // 事务处理
        Db::startTrans();
        try {
            if ($existingFollow) {
                // 已关注，执行取消关注
                $bloggerFollowDao->deleteBloggerFollow([['id', '=', $existingFollow['id']]]);
                // 博主粉丝数-1
                (new BloggerDao())->setBloggerDec([['id', '=', $blogger_id]], 'follower_count');
                // 用户关注博主数-1
                (new UserDao())->setUserDec([['id', '=', $this->user_id]], 'following_blogger_count');

                $action = 'unfollow';
                $is_followed = false;
                $newFansCount = max(0, $originalFansCount - 1); // 防止负数
            } else {
                // 未关注，执行关注
                $followData = [
                    'user_id' => $this->user_id,
                    'blogger_id' => $blogger_id,
                ];
                $bloggerFollowDao->createBloggerFollow($followData);

                // 博主粉丝数+1
                (new BloggerDao())->setBloggerInc([['id', '=', $blogger_id]], 'follower_count');
                // 用户关注博主数+1
                (new UserDao())->setUserInc([['id', '=', $this->user_id]], 'following_blogger_count');

                $action = 'follow';
                $is_followed = true;
                $newFansCount = $originalFansCount + 1;
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
            'is_followed' => $is_followed,
            'follower_count' => $newFansCount
        ];
    }






}