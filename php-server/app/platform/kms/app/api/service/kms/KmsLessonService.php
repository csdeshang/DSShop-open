<?php

namespace app\platform\kms\app\api\service\kms;


use app\deshang\base\service\BaseApiService;
use app\deshang\exceptions\CommonException;
use app\common\dao\kms\KmsLessonDao;
use app\common\dao\order\TblOrderDao;
use app\common\dao\goods\TblGoodsDao;


/**
 * 课程课时服务
 * 
 * 提供课程课时列表和详情获取功能
 * 
 * @package app\platform\kms\app\api\service\kms
 */

class KmsLessonService extends BaseApiService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getKmsLessonList(array $data): array
    {
        $goodsId = intval($data['goods_id'] ?? 0);

        if ($goodsId <= 0) {
            throw new CommonException('商品ID不能为空');
        }

        // 检查商品是否存在
        $goods = (new TblGoodsDao())->getGoodsInfoById($goodsId);
        if (!$goods) {
            throw new CommonException('商品不存在');
        }

        // 获取课时列表
        $lesson_list = (new KmsLessonDao())->getKmsLessonList(['goods_id' => $goodsId]);
        
        // 检查用户购买状态（通过订单表）
        $userOrder = null;
        if ($this->user_id > 0) {
            $userOrder = (new TblOrderDao())->findUserPurchased($this->user_id, $goodsId);
        }
        
        // 格式化课时数据，添加观看权限
        foreach ($lesson_list as $key => $lesson) {
            $canWatch = $this->canUserWatchLesson($this->user_id, $lesson, $userOrder);
            $lesson_list[$key]['is_can_watch'] = $canWatch;
            $lesson_list[$key]['is_buy'] = $userOrder ? true : false;
            $lesson_list[$key]['video_url'] = $canWatch ? $lesson['video_url'] : '';
            $lesson_list[$key]['audio_url'] = $canWatch ? $lesson['audio_url'] : '';
            $lesson_list[$key]['content'] = $canWatch ? $lesson['content'] : '';
        }

        return $lesson_list;
    }

    public function getKmsLessonDetail(int $lessonId): array
    {
        if ($lessonId <= 0) {
            throw new CommonException('课时ID不能为空');
        }

        // 获取课时信息
        $lesson = (new KmsLessonDao())->getKmsLessonInfoById($lessonId);
        if (!$lesson) {
            throw new CommonException('课时不存在');
        }

        // 检查用户购买状态（通过订单表）
        $userOrder = null;
        if ($this->user_id > 0) {
            $userOrder = (new TblOrderDao())->findUserPurchased($this->user_id, $lesson['goods_id']);
        }

        // 检查观看权限
        $canWatch = $this->canUserWatchLesson($this->user_id, $lesson, $userOrder);

        $lesson['is_can_watch'] = $canWatch;
        $lesson['is_buy'] = $userOrder ? true : false;
        $lesson['video_url'] = $canWatch ? $lesson['video_url'] : '';
        $lesson['audio_url'] = $canWatch ? $lesson['audio_url'] : '';
        $lesson['content'] = $canWatch ? $lesson['content'] : '';

        return $lesson;
    }


    /**
     * 检查用户是否可以观看课时
     * 
     * @param int|null $user_id 用户ID，可能为null（未登录用户）
     * @param array $lesson 课时信息
     * @param array|null $userOrder 用户订单信息
     * @return bool
     */
    protected function canUserWatchLesson(?int $user_id, array $lesson, ?array $userOrder): bool
    {
        // 免费课时所有人都可以观看
        if ($lesson['is_free'] == 1) {
            return true;
        }
        
        // 未登录用户不能观看收费课时
        if ($user_id === null || $user_id <= 0) {
            return false;
        }
        
        // 已购买用户可以观看
        return !empty($userOrder);
    }
}