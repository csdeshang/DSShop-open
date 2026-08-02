<?php

namespace app\api\service\goods;

use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;
use app\deshang\base\BaseService;
use app\common\dao\goods\TblGoodsCommentDao;




class TblGoodsCommentService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }



    public function getTblGoodsCommentPages($data){

        $goods_id = $data['goods_id'] ?? 0;
        $goods_score = $data['goods_score'] ?? '';
        $filter_type = $data['filter_type'] ?? 'all';
        
        // 构建缓存键，包含所有参数（包括分页参数）
        $cacheKey = sprintf(CacheKeyManager::GOODS_COMMENT_PAGES_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        if($result == null){
            $condition = [];
            $condition['goods_id'] = $goods_id;
            $condition['is_show'] = 1;
            $condition['is_deleted'] = 0;
            
            // 根据筛选类型添加条件
            switch ($filter_type) {
                case 'image':
                    // 有图的评价
                    $condition[] = ['images', '<>', ''];
                    break;
                case 'good':
                    // 好评 (5分)
                    $condition['goods_score'] = 5;
                    break;
                case 'medium':
                    // 中评 (3-4分)
                    $condition[] = ['goods_score', 'between', [3, 4]];
                    break;
                case 'bad':
                    // 差评 (1-2分)
                    $condition[] = ['goods_score', 'between', [1, 2]];
                    break;
                case 'all':
                default:
                    // 全部评价，不添加额外条件
                    break;
            }
            
            // 如果指定了具体评分
            if (!empty($goods_score)) {
                $condition['goods_score'] = $goods_score;
            }
            
            $result = (new TblGoodsCommentDao)->getWithRelGoodsCommentPages($condition);
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::GOODS_COMMENT_TAG);
        }

        return $result;
    }



}