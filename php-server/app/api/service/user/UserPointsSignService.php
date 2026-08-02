<?php

namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;
use app\deshang\exceptions\CommonException;
use app\common\enum\user\UserPointsEnum;
use app\common\dao\user\UserPointsLogDao;
use app\common\dao\user\UserDao;
use app\deshang\service\user\DeshangUserPointsService;

/**
 * 用户签到服务（API）- 积分签到
 */
class UserPointsSignService extends BaseUserService
{
    /**
     * 检查用户今天是否已签到
     */
    protected function hasSignedToday(): bool
    {
        $today_start = strtotime(date('Y-m-d'));
        $condition = [
            ['user_id', '=', $this->user_id],
            ['change_type', '=', UserPointsEnum::TYPE_SIGN],
            ['change_mode', '=', UserPointsEnum::MODE_INCREASE],
            ['create_at', '>=', $today_start],
        ];
        $log = (new UserPointsLogDao())->getPointsLogInfo($condition);
        return !empty($log);
    }

    /**
     * 获取签到前（今日之前）的连续签到天数
     */
    protected function getSignStreakBeforeToday(): int
    {
        $user = (new UserDao())->getUserInfoById($this->user_id, 'sign_streak,sign_last_date');
        if (empty($user) || empty($user['sign_last_date'])) {
            return 0;
        }
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        return ($user['sign_last_date'] === $yesterday) ? (int) $user['sign_streak'] : 0;
    }

    /**
     * 获取签到状态（今日是否已签到、连续天数、最后签到日期）
     *
     * @return array ['signed_today'=>bool, 'streak'=>int, 'sign_last_date'=>string|null]
     */
    public function getUserPointsSignStatus(): array
    {
        $signed_today = $this->hasSignedToday();
        $user = (new UserDao())->getUserInfoById($this->user_id, 'sign_streak,sign_last_date');
        $streak = isset($user['sign_streak']) ? (int) $user['sign_streak'] : 0;
        $sign_last_date = isset($user['sign_last_date']) && $user['sign_last_date'] !== '' ? $user['sign_last_date'] : null;
        if ($sign_last_date !== null) {
            $today = date('Y-m-d');
            if ($sign_last_date !== $today && $streak > 0) {
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                if ($sign_last_date !== $yesterday) {
                    $streak = 0;
                }
            }
        }
        return [
            'signed_today' => $signed_today,
            'streak' => $signed_today ? $streak : $streak,
            'sign_last_date' => $sign_last_date,
        ];
    }

    /**
     * 执行签到并增加积分
     *
     * @return array ['success'=>bool, 'points'=>int, 'streak'=>int, 'message'=>string]
     */
    public function doUserPointsSign(): array
    {
        $points_sign_enabled = sysConfig('points:points_sign_enabled');
        if ($points_sign_enabled != '1' && $points_sign_enabled != 1) {
            throw new CommonException('签到积分未开启');
        }
        if ($this->hasSignedToday()) {
            throw new CommonException('今日已签到');
        }

        $base = (int) sysConfig('points:points_sign_amount');
        if ($base <= 0) {
            throw new CommonException('签到积分配置错误');
        }

        $streak_before = $this->getSignStreakBeforeToday();
        $today_streak = $streak_before + 1;

        $points = $base;
        $streak_enabled = sysConfig('points:points_sign_streak_enabled');
        if ($streak_enabled == '1' || $streak_enabled === 1) {
            $after_days = (int) sysConfig('points:points_sign_streak_after_days');
            $max_days = (int) sysConfig('points:points_sign_streak_max_days');
            $multiplier = (float) sysConfig('points:points_sign_streak_multiplier');
            $effective_days = min($today_streak, $max_days > 0 ? $max_days : 7);
            if ($effective_days >= $after_days && $multiplier > 0) {
                $points = (int) round($base * $multiplier);
                if ($points <= 0) {
                    $points = $base;
                }
            }
        }

        $change_desc = $today_streak > 1 ? "每日签到(连续{$today_streak}天)" : '每日签到';
        $add_data = [
            'user_id' => $this->user_id,
            'related_id' => 0,
            'change_type' => UserPointsEnum::TYPE_SIGN,
            'change_mode' => UserPointsEnum::MODE_INCREASE,
            'change_num' => $points,
            'change_desc' => $change_desc,
        ];
        (new DeshangUserPointsService())->modifyUserPoints($add_data);

        (new UserDao())->updateUser(
            [['id', '=', $this->user_id]],
            [
                'sign_streak' => $today_streak,
                'sign_last_date' => date('Y-m-d'),
            ]
        );

        return [
            'success' => true,
            'points' => $points,
            'streak' => $today_streak,
            'message' => '签到成功',
        ];
    }
}
