<?php

namespace app\api\service\system;
use app\deshang\base\service\BaseApiService;
use app\common\dao\system\SysExpressDao;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;


class SysExpressService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getSysExpressList($data){
        // 生成缓存键
        $cacheKey = sprintf(CacheKeyManager::SYS_EXPRESS_LIST_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        
        if ($result == null) {
            // 参数预留
            $condition = array(

            );

            $result = (new SysExpressDao)->getExpressList($condition);
            
            // 缓存结果，设置4小时过期时间（快递公司数据相对稳定）
            KvManager::cache()->set($cacheKey, $result, 14400, CacheKeyManager::SYS_EXPRESS_TAG);
        }
        
        return $result;
    }

}