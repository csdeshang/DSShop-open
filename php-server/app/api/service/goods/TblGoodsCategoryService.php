<?php

namespace app\api\service\goods;

use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;
use app\deshang\base\BaseService;
use app\common\dao\goods\TblGoodsCategoryDao;




class TblGoodsCategoryService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getTblGoodsCategoryList($data)
    {

        $cacheKey = sprintf(CacheKeyManager::GOODS_CATEGORY_LIST_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        if ($result == null) {
            $condition = [];
            $condition[] = ['platform', '=', $data['platform']];
            $result = (new TblGoodsCategoryDao)->getGoodsCategoryList($condition);
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::GOODS_CATEGORY_TAG);
        }

        return $result;
    }
}
