<?php

namespace app\api\service\store;

use app\deshang\base\BaseService;
use app\common\dao\store\TblStoreCouponDao;
use app\common\enum\store\TblStoreCouponEnum;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;

// 展示店铺优惠券
class TblStoreCouponService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }


    // 获取店铺优惠券列表
    public function getTblStoreCouponList($data)
    {

        $cacheKey = sprintf(CacheKeyManager::STORE_COUPON_LIST_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);

        if ($result == null) {

            $condition = [];
            if (isset($data['platform']) && $data['platform'] != '') {
                $condition['platform'] = $data['platform'];
            }
            if (array_key_exists($data['status'], TblStoreCouponEnum::getStoreCouponStatusDict())) {
                $condition['status'] = $data['status'];
            }

            $result = (new TblStoreCouponDao)->getStoreCouponList($condition);
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::STORE_COUPON_TAG);
        }

        return $result;
    }


    // 获取店铺优惠券分页列表
    public function getTblStoreCouponPages($data)
    {

        $cacheKey = sprintf(CacheKeyManager::STORE_COUPON_PAGES_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);

        if ($result == null) {

            $condition = [];
            if (isset($data['platform']) && $data['platform'] != '') {
                $condition['platform'] = $data['platform'];
            }
            if (array_key_exists($data['status'], TblStoreCouponEnum::getStoreCouponStatusDict())) {
                $condition['status'] = $data['status'];
            }

            $result = (new TblStoreCouponDao)->getStoreCouponPages($condition);
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::STORE_COUPON_TAG);
        }

        return $result;
    }

    // 获取店铺优惠券详情
    public function getTblStoreCouponInfo($id)
    {

        $cacheKey = sprintf(CacheKeyManager::STORE_COUPON_INFO_KEY, $id);
        $result = KvManager::cache()->get($cacheKey);
        
        if($result == null){

            $result = (new TblStoreCouponDao)->getStoreCouponInfo($id);
            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::STORE_COUPON_TAG);
        }

        return $result;

    }
}
