<?php

namespace app\api\service\pointsGoods;

use app\deshang\base\service\BaseApiService;
use app\common\dao\pointsGoods\PointsGoodsEvaluateDao;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;

class PointsGoodsEvaluateService extends BaseApiService
{
    /**
     * 获取积分商品评价列表
     * @param array $data 查询参数
     * @return array 评价列表
     */
    public function getPointsGoodsEvaluateList(array $data): array
    {
        $goods_id = $data['goods_id'] ?? 0;
        $limit = isset($data['limit']) && $data['limit'] > 0 ? (int) $data['limit'] : 10;
        
        // 构建缓存键，包含所有参数
        $cacheKey = sprintf(CacheKeyManager::POINTS_GOODS_EVALUATE_LIST_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        if($result == null){
            $condition = [
                ['goods_id', '=', $goods_id],
                ['is_deleted', '=', 0],
                ['is_show', '=', 1] // 默认只显示已审核的评价
            ];

            // 获取评价列表
            $result = (new PointsGoodsEvaluateDao())->getPointsGoodsEvaluateList($condition, '*', 'create_at desc', $limit);
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::POINTS_GOODS_EVALUATE_TAG);
        }
        
        return $result;
    }

    /**
     * 获取积分商品评价分页列表
     * @param array $data 查询参数
     * @return array 分页数据
     */
    public function getPointsGoodsEvaluatePages(array $data): array
    {
        $goods_id = $data['goods_id'] ?? 0;
        $evaluate_score = $data['evaluate_score'] ?? '';
        $filter_type = $data['filter_type'] ?? 'all';
        
        // 构建缓存键，包含所有参数（包括分页参数）
        $cacheKey = sprintf(CacheKeyManager::POINTS_GOODS_EVALUATE_PAGES_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        if($result == null){
            $condition = [
                ['goods_id', '=', $goods_id],
                ['is_deleted', '=', 0],
                ['is_show', '=', 1] // 默认只显示已审核的评价
            ];

            // 评价分数筛选
            if (!empty($evaluate_score)) {
                $condition[] = ['evaluate_score', '=', $evaluate_score];
            }

            // 筛选类型处理
            if ($filter_type !== 'all') {
                switch ($filter_type) {
                    case 'image':
                        // 有图评价
                        $condition[] = ['evaluate_images', '<>', ''];
                        break;
                    case 'good':
                        // 好评 (4-5分)
                        $condition[] = ['evaluate_score', '>=', 4];
                        break;
                    case 'medium':
                        // 中评 (3分)
                        $condition[] = ['evaluate_score', '=', 3];
                        break;
                    case 'bad':
                        // 差评 (1-2分)
                        $condition[] = ['evaluate_score', '<=', 2];
                        break;
                }
            }

            // 获取分页数据
            $result = (new PointsGoodsEvaluateDao())->getPointsGoodsEvaluatePages($condition);
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::POINTS_GOODS_EVALUATE_TAG);
        }
        
        return $result;
    }
}
