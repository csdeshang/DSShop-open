<?php

namespace app\api\service\system;

use app\deshang\base\service\BaseApiService;
use app\common\dao\system\SysArticleDao;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;


class SysArticleService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取文章列表
     */
    public function getSysArticleList($data)
    {
        $cacheKey = sprintf(CacheKeyManager::SYS_ARTICLE_LIST_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);

        if ($result == null) {

            $condition = [];
            $condition[] = ['is_show', '=', 1];

            if (!empty($data['cid'])) {
                $condition[] = ['cid', '=', $data['cid']];
            }

            $result = (new SysArticleDao())->getSysArticleList($condition, '*', 'sort desc, id desc');
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::SYS_ARTICLE_TAG);
        }



        return $result;
    }

    /**
     * 获取文章分页列表
     */
    public function getSysArticlePages($data)
    {
        // 生成缓存键
        $cacheKey = sprintf(CacheKeyManager::SYS_ARTICLE_PAGES_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        
        if ($result == null) {
            $condition = [];
            $condition[] = ['is_show', '=', 1];

            if (!empty($data['cid'])) {
                $condition[] = ['cid', '=', $data['cid']];
            }

            if (!empty($data['keyword'])) {
                $condition[] = ['title', 'like', '%' . $data['keyword'] . '%'];
            }

            $result = (new SysArticleDao())->getSysArticlePages($condition, '*', 'sort desc, id desc');
            
            // 缓存结果，设置1小时过期时间
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::SYS_ARTICLE_TAG);
        }
        
        return $result;
    }

    /**
     * 获取文章详情
     */
    public function getSysArticleDetail($id)
    {
        // 构建查询条件
        $condition = [];
        $condition[] = ['is_show', '=', 1];
        $condition[] = ['id', '=', $id];

        $cacheKey = sprintf(CacheKeyManager::SYS_ARTICLE_INFO_KEY, $id);
        $result = KvManager::cache()->get($cacheKey);

        if ($result == null) {
            $sysArticleDao = new SysArticleDao();
            $result = $sysArticleDao->getSysArticleInfo($condition);
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::SYS_ARTICLE_TAG);
        }

        if ($result) {
            // 增加阅读量（无论是否从缓存获取，都需要更新阅读量）
            $sysArticleDao = new SysArticleDao();
            $sysArticleDao->setSysArticleInc($condition, 'actual_views', 1);
        }

        return $result;
    }
}
