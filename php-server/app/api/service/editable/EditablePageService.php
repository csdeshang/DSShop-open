<?php

namespace app\api\service\editable;

use app\deshang\base\BaseService;
use app\common\dao\editable\EditablePageDao;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;


class EditablePageService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getEditablePageInfo($data)
    {

        $cacheKey = sprintf(CacheKeyManager::EDITABLE_PAGE_INFO_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);

        if ($result == null) {

            $condition = [];
            if (isset($data['platform']) && $data['platform'] != '') {
                $condition[] = ['platform', '=', $data['platform']];
            }
            if (isset($data['type']) && $data['type'] != '') {
                $condition[] = ['type', '=', $data['type']];
            }
            if (isset($data['device_type']) && $data['device_type'] != '') {
                $condition[] = ['device_type', '=', $data['device_type']];
            }
            if (isset($data['id']) && $data['id'] > 0) {
                $condition[] = ['id', '=', $data['id']];
            }
            $result = (new EditablePageDao)->getEditablePageInfo($condition);
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::EDITABLE_PAGE_TAG);
        }

        return $result;
    }
}
