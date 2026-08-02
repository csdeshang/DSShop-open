<?php

namespace app\api\service\system;

use app\deshang\base\service\BaseApiService;
use app\common\dao\system\SysAgreementDao;

use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;

class SysAgreementService extends BaseApiService
{

    public function __construct()
    {
        parent::__construct();
    }
    public function getSysAgreementInfo($data)
    {
        $cacheKey = sprintf(CacheKeyManager::SYS_AGREEMENT_INFO_KEY, $data['code']);
        $result = KvManager::cache()->get($cacheKey);
        if ($result == null) {
            $condition = array();
            if (isset($data['code'])) {
                $condition['code'] = $data['code'];
            }
            $result = (new SysAgreementDao)->getSysAgreementInfo($condition);
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::SYS_AGREEMENT_TAG);
        }
        return $result;
    }
}
