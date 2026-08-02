<?php

namespace app\api\service\system;

use app\deshang\base\service\BaseApiService;
use app\common\dao\system\SysArticleCategoryDao;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;

class SysArticleCategoryService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取文章分类列表
     */
    public function getArticleCategoryList($data)
    {
        // 生成缓存键
        $cacheKey = sprintf(CacheKeyManager::SYS_ARTICLE_CATEGORY_LIST_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        
        if ($result == null) {
            $condition = [];
            $condition[] = ['is_show', '=', 1];
            
            if (!empty($data['pid'])) {
                $condition[] = ['pid', '=', $data['pid']];
            }
            
            $result = (new SysArticleCategoryDao())->getSysArticleCategoryList($condition, '*', 'sort desc, id asc');
            
            // 缓存结果，设置2小时过期时间（文章分类相对稳定）
            KvManager::cache()->set($cacheKey, $result, 7200, CacheKeyManager::SYS_ARTICLE_CATEGORY_TAG);
        }
        
        return $result;
    }
} 