<?php

namespace app\bloggerapi\service\blogger;

use app\deshang\base\service\BaseBloggerService;
use app\common\dao\blogger\BloggerDao;
use app\deshang\exceptions\CommonException;

class BloggerInfoService extends BaseBloggerService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取博主信息
     * @return array
     */
    public function getBloggerInfo(): array
    {
        $bloggerInfo = (new BloggerDao())->getBloggerInfo(['id' => $this->blogger_id]);
        
        if (!$bloggerInfo) {
            throw new CommonException('博主信息不存在');
        }

        return $bloggerInfo;
    }

    /**
     * 更新博主信息
     * @param array $data
     * @return bool
     */
    public function updateBloggerInfo(array $data): bool
    {
        $result = (new BloggerDao())->updateBlogger([['id', '=', $this->blogger_id]], $data);
        return $result;
    }
} 