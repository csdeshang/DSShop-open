<?php

namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;
use app\common\dao\user\UserGrowthLevelDao;
use app\common\dao\user\UserDao;

class UserLevelService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取用户等级信息
     */
    public function getUserLevelInfo()
    {
        // 获取用户信息
        $user_info = (new UserDao())->getUserInfo(['id' => $this->user_id]);
        
        if (empty($user_info['growth_level_id'])) {
            return [
                'user_info' => [
                    'growth' => $user_info['growth'],
                    'growth_level_id' => $user_info['growth_level_id']
                ],
                'level_progress' => null
            ];
        }
        
        // 获取当前等级信息
        $levelDao = new UserGrowthLevelDao();
        $current_level = $levelDao->getUserGrowthLevelInfoById($user_info['growth_level_id']);
        
        // 计算等级进度
        $level_progress = $this->calculateLevelProgress($user_info['growth'], $current_level);
        
        return [
            'user_info' => [
                'growth' => $user_info['growth'],
                'growth_level_id' => $user_info['growth_level_id']
            ],
            'level_progress' => $level_progress
        ];
    }

    /**
     * 获取等级列表
     */
    public function getUserLevelList()
    {
        $level_list = (new UserGrowthLevelDao())->getUserGrowthLevelList([], '*', 'min_growth asc');
        
        // 获取当前用户信息用于标记当前等级
        $user_info = (new UserDao())->getUserInfo(['id' => $this->user_id]);
        
        foreach ($level_list as &$level) {
            $level['is_current'] = $level['id'] == $user_info['growth_level_id'];
            $level['is_achieved'] = $user_info['growth'] >= $level['min_growth'];
        }
        
        return $level_list;
    }
    
    /**
     * 计算等级进度
     */
    private function calculateLevelProgress($current_growth, $current_level)
    {
        if (empty($current_level)) {
            return null;
        }
        
        // 获取所有等级列表
        $levelDao = new UserGrowthLevelDao();
        $all_levels = $levelDao->getUserGrowthLevelList([], '*', 'min_growth asc');
        
        // 找到下一等级
        $next_level = null;
        foreach ($all_levels as $level) {
            if ($level['min_growth'] > $current_growth) {
                $next_level = $level;
                break;
            }
        }
        
        if (!$next_level) {
            // 已经是最高等级
            return [
                'current_level' => $current_level,
                'next_level' => null,
                'progress_percent' => 100,
                'remaining_growth' => 0,
                'is_max_level' => true
            ];
        }
        
        // 计算进度
        $current_level_growth = $current_level['min_growth'];
        $next_level_growth = $next_level['min_growth'];
        $user_growth_in_level = $current_growth - $current_level_growth;
        $level_growth_range = $next_level_growth - $current_level_growth;
        
        $progress_percent = $level_growth_range > 0 ? 
            round(($user_growth_in_level / $level_growth_range) * 100, 1) : 0;
        
        return [
            'current_level' => $current_level,
            'next_level' => $next_level,
            'progress_percent' => $progress_percent,
            'remaining_growth' => $next_level_growth - $current_growth,
            'is_max_level' => false
        ];
    }
}
