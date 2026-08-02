<?php

namespace app\api\service\system;

use app\deshang\base\service\BaseApiService;
use app\common\dao\system\SysAreaDao;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;

class SysAreaService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getSysAreaList($data)
    {

        $cacheKey = sprintf(CacheKeyManager::SYS_AREA_LIST_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);

        if ($result == null) {
            $condition = array();
            if (isset($data['pid'])) {
                $condition['pid'] = $data['pid'];
            }
            $result = (new SysAreaDao)->getAreaList($condition);
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::SYS_AREA_TAG);
        }

        return $result;
    }
}
