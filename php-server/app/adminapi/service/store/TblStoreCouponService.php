<?php

namespace app\adminapi\service\store;

use app\common\dao\store\TblStoreCouponDao;
use app\common\dao\store\TblStoreCouponUserDao;
use app\common\enum\store\TblStoreCouponEnum;
use app\deshang\base\service\BaseAdminService;
use app\deshang\exceptions\CommonException;

/**
 * 店铺优惠券服务类
 */
class TblStoreCouponService extends BaseAdminService
{
    /**
     * 获取优惠券分页列表
     */
    public function getTblStoreCouponPages(array $data): array
    {
        $condition = [];

        if (!empty($data['coupon_name'])) {
            $condition[] = ['coupon_name', 'like', '%' . $data['coupon_name'] . '%'];
        }

        if (array_key_exists($data['coupon_type'], TblStoreCouponEnum::getStoreCouponTypeDict())) {
            $condition[] = ['coupon_type', '=', (int)$data['coupon_type']];
        }

        return (new TblStoreCouponDao())->getStoreCouponPages($condition);
    }

    /**
     * 获取优惠券列表
     */
    public function getTblStoreCouponList(array $data): array
    {
        $condition = [];

        return (new TblStoreCouponDao())->getStoreCouponList($condition);
    }

    /**
     * 获取优惠券详情
     */
    public function getTblStoreCouponInfo(int $id): array
    {
        $condition = [];
        $condition[] = ['id', '=', $id];

        $result = (new TblStoreCouponDao())->getStoreCouponInfo($condition);
        if (!$result) {
            throw new CommonException('优惠券不存在');
        }

        return $result;
    }

    /**
     * 创建优惠券
     */
    public function createTblStoreCoupon(array $data): int
    {
        $now = time();
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];

        if ($startTime <= $now && $endTime > $now) {
            $data['status'] = TblStoreCouponEnum::STATUS_START;
        } elseif ($endTime <= $now) {
            $data['status'] = TblStoreCouponEnum::STATUS_END;
        } else {
            $data['status'] = TblStoreCouponEnum::STATUS_NOT_START;
        }

        $data['available_stock'] = $data['total_stock'];

        return (new TblStoreCouponDao())->createStoreCoupon($data);
    }

    /**
     * 更新优惠券
     */
    public function updateTblStoreCoupon(array $data): int
    {
        $condition = [];
        $condition[] = ['id', '=', $data['id']];

        $couponInfo = (new TblStoreCouponDao())->getStoreCouponInfo($condition);
        if (!$couponInfo) {
            throw new CommonException('优惠券不存在');
        }

        if ($data['total_stock'] < $couponInfo['claimed_stock']) {
            throw new CommonException('总库存不能小于已领取数量');
        }

        $data['available_stock'] = $data['total_stock'] - $couponInfo['claimed_stock'];

        return (new TblStoreCouponDao())->updateStoreCoupon($condition, $data);
    }

    /**
     * 获取优惠券领取记录分页列表
     */
    public function getTblStoreCouponUserPages(array $data): array
    {
        $condition = [];

        if ($data['coupon_id'] > 0) {
            $condition[] = ['coupon_id', '=', $data['coupon_id']];
        }

        return (new TblStoreCouponUserDao())->getStoreCouponUserPages($condition);
    }
}
