<?php

namespace app\api\service\blogger;

use app\deshang\base\service\BaseApiService;

use app\deshang\exceptions\CommonException;

use app\common\dao\blogger\BloggerDao;
use app\common\dao\blogger\BloggerFollowDao;
use app\common\dao\user\UserDao;



class BloggerService extends BaseApiService
{

    public function __construct()
    {
        parent::__construct();
    }



    // 获取博主基本信息
    public function getBloggerDetail($blogger_id)
    {
        $bloggerDao = new BloggerDao();
        
        // 获取博主基本信息
        $bloggerInfo = $bloggerDao->getBloggerInfoById($blogger_id);
        if (empty($bloggerInfo)) {
            throw new CommonException('博主不存在');
        }

        // 获取博主关联的用户
        $userInfo = (new UserDao())->getUserInfoById($bloggerInfo['user_id'],'id,username,avatar');
        $bloggerInfo['user'] = $userInfo;

        // 如果用户已登录，检查是否已关注
        if ($this->user_id) {
            $bloggerFollowDao = new BloggerFollowDao();
            $followInfo = $bloggerFollowDao->getBloggerFollowInfo([
                ['user_id', '=', $this->user_id],
                ['blogger_id', '=', $blogger_id]
            ]);
            $bloggerInfo['is_followed'] = !empty($followInfo);
        } else {
            $bloggerInfo['is_followed'] = false;
        }
        return $bloggerInfo;
    }











}