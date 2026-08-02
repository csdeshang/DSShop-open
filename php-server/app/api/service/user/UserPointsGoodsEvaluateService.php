<?php

namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;
use app\deshang\exceptions\CommonException;
use app\deshang\exceptions\NotFoundException;
use app\common\dao\pointsGoods\PointsGoodsEvaluateDao;
use app\common\dao\pointsGoods\PointsGoodsOrderDao;
use app\common\dao\pointsGoods\PointsGoodsDao;

/**
 * 用户积分商品评价服务
 */
class UserPointsGoodsEvaluateService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 添加积分商品评价
     * @param array $data 评价数据
     * @return bool 结果
     */
    public function addPointsGoodsEvaluate(array $data): bool
    {
        // 验证订单是否存在且属于当前用户
        $order = (new PointsGoodsOrderDao())->getPointsGoodsOrderInfoById($data['order_id']);
        if (empty($order)) {
            throw new CommonException('订单不存在');
        }

        if ($order['user_id'] != $this->user_id) {
            throw new CommonException('无权限操作该订单');
        }

        // 检查订单状态是否允许评价
        if ($order['order_status'] != 30) { // 30: 已收货
            throw new CommonException('订单状态不允许评价');
        }

        // 检查是否已经评价过
        $existingEvaluate = (new PointsGoodsEvaluateDao())->getPointsGoodsEvaluateInfo([
            ['order_id', '=', $data['order_id']],
            ['user_id', '=', $this->user_id],
            ['is_deleted', '=', 0]
        ]);

        if (!empty($existingEvaluate)) {
            throw new CommonException('该订单已经评价过了');
        }

        // 构建评价数据
        $evaluateData = [
            'user_id' => $this->user_id,
            'goods_id' => $data['goods_id'],
            'order_id' => $data['order_id'],
            'evaluate_content' => $data['evaluate_content'],
            'evaluate_images' => $data['evaluate_images'],
            'evaluate_score' => $data['evaluate_score'],
            'is_anonymous' => $data['is_anonymous'],
            'is_show' => 1,
            'create_at' => time(),
            'update_at' => time(),
            'is_deleted' => 0
        ];

        // 创建评价
        $result = (new PointsGoodsEvaluateDao())->createPointsGoodsEvaluate($evaluateData);
        
        if (!$result || $result <= 0) {
            throw new CommonException('评价失败');
        }

        // 更新订单评价状态
        (new PointsGoodsOrderDao())->updatePointsGoodsOrder(
            ['id' => $data['order_id']],
            ['is_evaluate' => 1, 'update_at' => time()]
        );

        // 更新商品评价统计
        $this->updateGoodsEvaluateStats($data['goods_id']);

        return true;
    }

    /**
     * 获取积分商品评价分页列表
     * @param array $data 查询参数
     * @return array 分页数据
     */
    public function getPointsGoodsEvaluatePages(array $data): array
    {
        $condition = [
            ['user_id', '=', $this->user_id],
            ['is_deleted', '=', 0]
        ];

        // 商品ID筛选
        if (!empty($data['goods_id'])) {
            $condition[] = ['goods_id', '=', $data['goods_id']];
        }

        // 评价分数筛选
        if (!empty($data['evaluate_score'])) {
            if (is_array($data['evaluate_score'])) {
                // 多个分数值，使用IN查询
                $condition[] = ['evaluate_score', 'in', $data['evaluate_score']];
            } else {
                // 单个分数值
                $condition[] = ['evaluate_score', '=', $data['evaluate_score']];
            }
        }

        // 获取分页数据
        $result = (new PointsGoodsEvaluateDao())->getPointsGoodsEvaluatePages($condition);
        
        return $result;
    }

    /**
     * 获取积分商品评价详情
     * @param int $id 评价ID
     * @return array 评价详情
     */
    public function getPointsGoodsEvaluateInfo(int $id): array
    {
        $evaluate = (new PointsGoodsEvaluateDao())->getPointsGoodsEvaluateInfoById($id);
        if (empty($evaluate)) {
            throw new NotFoundException('评价不存在');
        }

        // 验证评价归属
        if ($evaluate['user_id'] != $this->user_id) {
            throw new CommonException('无权限访问该评价');
        }

        return $evaluate;
    }


    /**
     * 更新商品评价统计
     * 
     * @param int $goodsId 商品ID
     * @return bool 是否更新成功
     */
    private function updateGoodsEvaluateStats(int $goodsId)
    {
        // 计算该商品的评价统计
        $condition = [
            ['goods_id', '=', $goodsId],
            ['is_show', '=', 1],
            ['is_deleted', '=', 0]
        ];
        
        $totalCount = (new PointsGoodsEvaluateDao())->getPointsGoodsEvaluateCount($condition);
        
        if ($totalCount > 0) {
            $avgScore = (new PointsGoodsEvaluateDao())->getPointsGoodsEvaluateColumn($condition, 'evaluate_score');
            $avgScore = round(array_sum($avgScore) / count($avgScore), 2);
        } else {
            $avgScore = 5.00;
        }
        
        // 更新商品表中的评价统计
        $updateData = [
            'evaluate_num' => $totalCount,
            'avg_evaluate_score' => $avgScore,
            'update_at' => time()
        ];
        
        $condition = [['id', '=', $goodsId]];
        return (new PointsGoodsDao())->updatePointsGoods($condition, $updateData);
    }
}
