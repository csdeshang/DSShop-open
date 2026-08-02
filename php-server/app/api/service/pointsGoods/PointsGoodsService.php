<?php

namespace app\api\service\pointsGoods;

use app\deshang\base\BaseService;
use app\common\dao\pointsGoods\PointsGoodsDao;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;

class PointsGoodsService extends BaseService
{
    /**
     * 获取积分商品列表
     * 
     * @param array $data 查询条件
     * @return array 积分商品列表
     */
    public function getPointsGoodsList(array $data)
    {
        // 生成缓存键
        $cacheKey = sprintf(CacheKeyManager::POINTS_GOODS_LIST_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        
        if ($result == null) {
            $condition = [];
            
            // 商品状态筛选
            if (isset($data['goods_status']) && in_array($data['goods_status'], [0, 1])) {
                $condition[] = ['goods_status', '=', $data['goods_status']];
            }
            
            // 分类筛选
            if (isset($data['category_id']) && $data['category_id'] > 0) {
                $condition[] = ['category_id', '=', $data['category_id']];
            }
            
            // 未删除
            $condition[] = ['is_deleted', '=', 0];

            // 限制条数
            $limit = isset($data['limit']) ? (int) $data['limit'] : 10;

            $result = (new PointsGoodsDao())->getPointsGoodsList($condition, '*', 'id desc', $limit);
            
            // 处理轮播图数据，将字符串转换为数组
            if (is_array($result)) {
                foreach ($result as &$item) {
                    if (isset($item['slide_image']) && is_string($item['slide_image'])) {
                        $item['slide_image'] = $item['slide_image'] ? explode(',', $item['slide_image']) : [];
                    }
                }
            }
            
            // 缓存结果，设置1小时过期时间
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::POINTS_GOODS_TAG);
        }
        
        return $result;
    }

    /**
     * 获取积分商品分页列表
     * 
     * @param array $data 查询条件
     * @return array 分页数据
     */
    public function getPointsGoodsPages(array $data)
    {
        // 生成缓存键
        $cacheKey = sprintf(CacheKeyManager::POINTS_GOODS_PAGES_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        
        if ($result == null) {
            $condition = [];
            
            // 商品名称模糊搜索
            if (isset($data['goods_name']) && !empty($data['goods_name'])) {
                $condition[] = ['goods_name', 'like', '%' . $data['goods_name'] . '%'];
            }
            
            // 商品状态筛选
            if (isset($data['goods_status']) && in_array($data['goods_status'], [0, 1])) {
                $condition[] = ['goods_status', '=', $data['goods_status']];
            }
            
            // 分类筛选
            if (isset($data['category_id']) && $data['category_id'] > 0) {
                $condition[] = ['category_id', '=', $data['category_id']];
            }
            
            // 未删除
            $condition[] = ['is_deleted', '=', 0];

            $result = (new PointsGoodsDao())->getPointsGoodsPages($condition);
            
            // 处理分页数据中的轮播图，将字符串转换为数组
            if (isset($result['data']) && is_array($result['data'])) {
                foreach ($result['data'] as &$item) {
                    if (isset($item['slide_image']) && is_string($item['slide_image'])) {
                        $item['slide_image'] = $item['slide_image'] ? explode(',', $item['slide_image']) : [];
                    }
                }
            }
            
            // 缓存结果，设置1小时过期时间
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::POINTS_GOODS_TAG);
        }
        
        return $result;
    }

    /**
     * 获取积分商品详情
     * 
     * @param int $id 积分商品ID
     * @return array 积分商品详情
     */
    public function getPointsGoodsDetail(int $id)
    {
        // 生成缓存键
        $cacheKey = sprintf(CacheKeyManager::POINTS_GOODS_INFO_KEY, $id);
        $result = KvManager::cache()->get($cacheKey);
        
        if ($result == null) {
            $result = (new PointsGoodsDao())->getPointsGoodsInfoById($id);
            
            // 处理轮播图数据，将字符串转换为数组
            if ($result && isset($result['slide_image']) && is_string($result['slide_image'])) {
                $result['slide_image'] = $result['slide_image'] ? explode(',', $result['slide_image']) : [];
            }
            
            // 缓存结果，设置1小时过期时间
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::POINTS_GOODS_TAG);
        }
        
        // 增加点击量（无论是否从缓存获取都需要增加点击量）
        if ($result) {
            (new PointsGoodsDao())->setPointsGoodsInc(['id' => $id], 'click_num', 1);
        }
        
        return $result;
    }
}
