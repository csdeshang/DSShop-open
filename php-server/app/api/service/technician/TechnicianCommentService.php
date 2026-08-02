<?php

namespace app\api\service\technician;

use app\deshang\base\service\BaseApiService;
use app\common\dao\technician\TechnicianCommentDao;
use app\deshang\exceptions\CommonException;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;

/**
 * 师傅评价服务类
 */
class TechnicianCommentService extends BaseApiService
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
        // 生成缓存键
        $cacheKey = sprintf(CacheKeyManager::TECHNICIAN_COMMENT_PAGES_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        
        if ($result == null) {
            $condition = [];
            
            // 只显示公开的评论
            $condition[] = ['is_show', '=', '1'];
            
            // 师傅ID筛选
            if (isset($data['technician_id']) && $data['technician_id'] != '') {
                $condition[] = ['technician_id', '=', $data['technician_id']];
            }
            
            // 是否回复筛选
            if (isset($data['is_reply']) && in_array($data['is_reply'], ['0', '1'])) {
                $condition[] = ['is_reply', '=', $data['is_reply']];
            }
            
            // 评分筛选
            if (isset($data['service_score']) && $data['service_score'] != '') {
                $condition[] = ['service_score', '=', $data['service_score']];
            }

            $result = (new TechnicianCommentDao())->getWithRelTechnicianCommentPages($condition);
            
            // 缓存结果，设置30分钟过期时间（评论数据相对动态）
            KvManager::cache()->set($cacheKey, $result, 1800, CacheKeyManager::TECHNICIAN_COMMENT_TAG);
        }
        
        return $result;
    }

    /**
     * 获取师傅评论列表
     * @param array $data
     * @return array
     */
    public function getTechnicianCommentList(array $data): array
    {
        // 生成缓存键
        $cacheKey = sprintf(CacheKeyManager::TECHNICIAN_COMMENT_LIST_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        
        if ($result == null) {
            $condition = [];
            
            // 只显示公开的评论
            $condition[] = ['is_show', '=', '1'];
            
            // 师傅ID筛选
            if (isset($data['technician_id']) && $data['technician_id'] != '') {
                $condition[] = ['technician_id', '=', $data['technician_id']];
            }
            
            // 是否回复筛选
            if (isset($data['is_reply']) && in_array($data['is_reply'], ['0', '1'])) {
                $condition[] = ['is_reply', '=', $data['is_reply']];
            }
            
            // 评分筛选
            if (isset($data['service_score']) && $data['service_score'] != '') {
                $condition[] = ['service_score', '=', $data['service_score']];
            }

            // 设置限制条数
            $limit = isset($data['limit']) ? (int)$data['limit'] : 20;

            $result = (new TechnicianCommentDao())->getWithRelTechnicianCommentList(
                $condition, 
                '*', 
                'create_at desc', 
                $limit
            );
            
            // 缓存结果，设置30分钟过期时间（评论数据相对动态）
            KvManager::cache()->set($cacheKey, $result, 1800, CacheKeyManager::TECHNICIAN_COMMENT_TAG);
        }
        
        return $result;
    }



} 