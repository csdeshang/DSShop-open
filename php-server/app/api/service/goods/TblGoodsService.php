<?php

namespace app\api\service\goods;

use app\deshang\base\BaseService;
use app\common\dao\goods\TblGoodsDao;
use app\common\enum\goods\TblGoodsEnum;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;
use app\common\dao\goods\TblGoodsCategoryRelDao;




class TblGoodsService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 构建排序字符串
     * @param array $data 请求参数
     * @return string 排序字符串
     */
    private function buildSortOrder($data)
    {
        $sortField = $data['sort_field'] ?? 'sales_num';
        $sortType = strtolower($data['sort_type'] ?? 'desc') === 'desc' ? 'desc' : 'asc';
        
        // 验证排序字段，无效时默认为sales_num
        if (!in_array($sortField, ['sales_num', 'avg_goods_score', 'create_at', 'click_num', 'goods_minprice'])) {
            $sortField = 'sales_num';
        }
        
        // 构建排序字符串：推荐商品优先，然后按指定字段排序
        return "sys_recommend_status desc,{$sortField} {$sortType}";
    }

    public function getTblGoodsList($data)
    {


        $cacheKey = sprintf(CacheKeyManager::GOODS_LIST_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        if ($result == null) {


            $condition = [];
            // 上架商品
            $condition[] = ['goods_status', '=', TblGoodsEnum::STATUS_SHELVED];
            // 系统审核通过商品
            $condition[] = ['sys_status', '=', TblGoodsEnum::SYS_STATUS_NORMAL];
            // 未删除
            $condition[] = ['is_deleted', '=', 0];
            // 有库存的商品
            $condition[] = ['stock_num', '>', 0];

            if (isset($data['platform']) && !empty($data['platform'])) {
                $condition[] = ['platform', '=', $data['platform']];
            }

            if (isset($data['category_id']) && !empty($data['category_id'])) {
                // 获取分类下的所有商品ID
                $goods_ids = (new TblGoodsCategoryRelDao)->getGoodsCategoryRelColumn([['category_id', '=', $data['category_id']]], 'goods_id');
                if (count($goods_ids) > 0) {
                    $condition[] = ['id', 'in', $goods_ids];
                }else{
                    // 如果分类下没有商品，则不查询
                    $condition[] = ['id', '=', -1];
                }
            }

            // 限制条数
            $limit = isset($data['limit']) ? (int) $data['limit'] : 10;

            // 构建排序字段
            $order = $this->buildSortOrder($data);

            $result = (new TblGoodsDao)->getWithRelGoodsList($condition, '*', $order, $limit);

            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::GOODS_TAG);
        }



        return $result;
    }


    public function getTblGoodsPages($data)
    {

        $cacheKey = sprintf(CacheKeyManager::GOODS_PAGES_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        
        if ($result == null) {


            $condition = [];
            // 上架商品
            $condition[] = ['goods_status', '=', TblGoodsEnum::STATUS_SHELVED];
            // 系统审核通过商品
            $condition[] = ['sys_status', '=', TblGoodsEnum::SYS_STATUS_NORMAL];
            // 未删除
            $condition[] = ['is_deleted', '=', 0];
            // 有库存的商品
            $condition[] = ['stock_num', '>', 0];

            if (isset($data['platform']) && !empty($data['platform'])) {
                $condition[] = ['platform', '=', $data['platform']];
            }

            if (isset($data['category_id']) && !empty($data['category_id'])) {
                // 获取分类下的所有商品ID
                $goods_ids = (new TblGoodsCategoryRelDao)->getGoodsCategoryRelColumn([['category_id', '=', $data['category_id']]], 'goods_id');
                if (count($goods_ids) > 0) {
                    $condition[] = ['id', 'in', $goods_ids];
                }else{
                    // 如果分类下没有商品，则不查询
                    $condition[] = ['id', '=', -1];
                }
            }

            if (isset($data['is_flashsale_goods']) && $data['is_flashsale_goods'] == 1) {
                $condition[] = ['is_flashsale_goods', '=', 1];
            }
            if (isset($data['is_wholesale_goods']) && $data['is_wholesale_goods'] == 1) {
                $condition[] = ['is_wholesale_goods', '=', 1];
            }
            if (isset($data['is_userdiscount_goods']) && $data['is_userdiscount_goods'] == 1) {
                $condition[] = ['is_userdiscount_goods', '=', 1];
            }
            if (isset($data['is_distributor_goods']) && $data['is_distributor_goods'] == 1) {
                $condition[] = ['is_distributor_goods', '=', 1];
            }

            // 价格区间：按商品最低价 goods_minprice 筛选
            if (isset($data['min_price']) && $data['min_price'] !== '' && $data['min_price'] !== null) {
                $minPrice = floatval($data['min_price']);
                if ($minPrice >= 0) {
                    $condition[] = ['goods_minprice', '>=', $minPrice];
                }
            }
            if (isset($data['max_price']) && $data['max_price'] !== '' && $data['max_price'] !== null) {
                $maxPrice = floatval($data['max_price']);
                if ($maxPrice >= 0) {
                    $condition[] = ['goods_minprice', '<=', $maxPrice];
                }
            }

            // 评分区间：按商品平均评分 avg_goods_score 筛选（0-5分）
            if (isset($data['min_goods_avg_score']) && $data['min_goods_avg_score'] !== '' && $data['min_goods_avg_score'] !== null) {
                $minScore = floatval($data['min_goods_avg_score']);
                if ($minScore >= 0 && $minScore <= 5) {
                    $condition[] = ['avg_goods_score', '>=', $minScore];
                }
            }
            if (isset($data['max_goods_avg_score']) && $data['max_goods_avg_score'] !== '' && $data['max_goods_avg_score'] !== null) {
                $maxScore = floatval($data['max_goods_avg_score']);
                if ($maxScore >= 0 && $maxScore <= 5) {
                    $condition[] = ['avg_goods_score', '<=', $maxScore];
                }
            }

            // 商品名称模糊搜索
            if (isset($data['goods_name']) && !empty($data['goods_name'])) {
                $condition[] = ['goods_name', 'like', '%' . $data['goods_name'] . '%'];
            }

            // 构建排序字段
            $order = $this->buildSortOrder($data);

            $result = (new TblGoodsDao)->getWithRelGoodsPages($condition, '*', $order);

            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::GOODS_TAG);
        }


        return $result;

    }

    /**
     * 获取随机商品列表（猜你喜欢）
     * @param array $data 查询参数
     * @return array 随机商品列表
     */
    public function getRandomGoods($data)
    {
        // 添加随机因子，让缓存有一定的随机性
        // 直接生成1-100的随机数
        $randomFactor = rand(1, 100);
        $cacheKey = sprintf(CacheKeyManager::GOODS_RANDOM_KEY, md5(serialize($data) . $randomFactor));
        $result = KvManager::cache()->get($cacheKey);
        
        if ($result == null) {
            $condition = [];
            // 上架商品
            $condition[] = ['goods_status', '=', TblGoodsEnum::STATUS_SHELVED];
            // 系统审核通过商品
            $condition[] = ['sys_status', '=', TblGoodsEnum::SYS_STATUS_NORMAL];
            // 未删除
            $condition[] = ['is_deleted', '=', 0];
            // 有库存的商品
            $condition[] = ['stock_num', '>', 0];

            if (isset($data['platform']) && !empty($data['platform'])) {
                $condition[] = ['platform', '=', $data['platform']];
            }

            // 限制条数
            $limit = isset($data['limit']) ? (int) $data['limit'] : 10;

            // 先获取商品总数
            $totalCount = (new TblGoodsDao)->getGoodsCount($condition);
            
            if ($totalCount == 0) {
                $result = [];
            } else {
                // 注意：不能使用 RAND() 函数，因为 ThinkPHP 的查询构建器不支持 RAND() 作为排序表达式
                // 会报错：order express error:RAND()
                // 解决方案：先获取更多数据，然后在PHP层面进行随机排序
                
                // 获取更多数据用于随机选择（最多3倍）
                $fetchLimit = min($totalCount, $limit * 3);
                $order = "id desc"; // 使用固定排序避免RAND()问题
                $tempResult = (new TblGoodsDao)->getWithRelGoodsList($condition, '*', $order, $fetchLimit);
                
                // 在PHP层面随机排序，实现真正的随机性
                if (is_array($tempResult) && count($tempResult) > 0) {
                    shuffle($tempResult);
                    $result = array_slice($tempResult, 0, $limit);
                } else {
                    $result = $tempResult;
                }
            }

            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::GOODS_TAG); // 缓存1小时
        }

        return $result;
    }
}
