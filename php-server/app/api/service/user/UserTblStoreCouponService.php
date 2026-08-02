<?php

namespace app\api\service\user;


use think\facade\Db;
use app\common\enum\user\UserPointsEnum;

use app\deshang\base\service\BaseUserService;
use app\deshang\exceptions\CommonException;
use app\deshang\exceptions\NotFoundException;
use app\common\dao\store\TblStoreCouponDao;
use app\common\dao\store\TblStoreCouponUserDao;
use app\common\dao\user\UserDao;
use app\deshang\service\user\DeshangUserPointsService;
use app\common\enum\store\TblStoreCouponEnum;
use app\common\enum\store\TblStoreCouponUserEnum;

// 用户领取店铺优惠券
class UserTblStoreCouponService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();
    }

    // 获取用户店铺优惠券列表
    public function getUserTblStoreCouponPages($data)
    {

        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];

        if (array_key_exists($data['status'], TblStoreCouponUserEnum::getStoreCouponUserStatusDict())) {
            $condition[] = ['status', '=', $data['status']];
        }




        return (new TblStoreCouponUserDao)->getWithRelStoreCouponUserPages($condition);
    }

    // 用户领取店铺优惠券
    public function receiveTblStoreCoupon($id)
    {
        // 获取优惠券信息
        $couponDao = new TblStoreCouponDao();
        $couponUserDao = new TblStoreCouponUserDao();

        $coupon = $couponDao->getStoreCouponInfoById($id); // 获取优惠券信息

        // 检查优惠券是否存在
        if (empty($coupon)) {
            throw new NotFoundException('优惠券不存在');
        }

        // 检查优惠券状态
        if ($coupon['status'] !== 1) {
            throw new CommonException('优惠券已失效');
        }

        // 检查优惠券领取类型
        if ($coupon['claim_type'] !== TblStoreCouponEnum::CLAIM_TYPE_MANUAL) {
            throw new CommonException('该优惠券不允许您领取');
        }

        // 检查有效期
        $currentTime = time();
        if ($currentTime < strtotime($coupon['start_time']) || $currentTime > strtotime($coupon['end_time'])) {
            throw new CommonException('优惠券不在有效期内');
        }

        // 检查库存
        if ($coupon['available_stock'] <= 0) {
            throw new CommonException('优惠券已被领取完');
        }

        // 检查用户领取限制
        $claimedCount = $couponUserDao->getStoreCouponUserCount([['user_id', '=', $this->user_id], ['coupon_id', '=', $id]]); // 获取用户已领取的优惠券数量

        if ($claimedCount >= $coupon['limit_per_user']) {
            throw new CommonException('您已达到领取该优惠券的上限');
        }

        // 检查积分要求
        if ($coupon['points_required'] > 0) {
            $user = (new UserDao())->getUserInfoById($this->user_id);
            // 获取用户当前积分
            $userPoints = $user['points'];
            if ($userPoints < $coupon['points_required']) {
                throw new CommonException('您的积分不足，无法领取该优惠券');
            }
        }

        // 进行领取操作
        $data = [
            'user_id' => $this->user_id,
            'coupon_id' => $id,
            'status' => 0, // 0表示未使用
        ];

        Db::startTrans();
        try {
            // 插入到用户优惠券领取表
            $couponUserDao->createStoreCouponUser($data); // 插入用户领取记录
            // 更新优惠券的已领取数量
            $couponDao->updateStoreCoupon(['id' => $id], ['claimed_stock' => $coupon['claimed_stock'] + 1, 'available_stock' => $coupon['available_stock'] - 1]); // 更新已领取数量


            // 扣除用户积分
            if ($coupon['points_required'] > 0) {
                $add_data = array(
                    'user_id' => $this->user_id,
                    'related_id' => 0,
                    'change_type' => UserPointsEnum::TYPE_STORE_COUPON,
                    'change_mode' => UserPointsEnum::MODE_DECREASE,
                    'change_num' => $coupon['points_required'],
                    'change_desc' => '领取优惠券',
                );
                $deshangService = new DeshangUserPointsService();
                $deshangService->modifyUserPoints($add_data);
            }


            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }

        return true;
    }
}
