<?php

namespace app\deshang\queue\handler\order;

use app\deshang\queue\handler\QueueHandlerInterface;
use app\common\dao\goods\TblGoodsDao;
use app\deshang\exceptions\CommonException;

/**
 * 订单生成销量增加队列处理器
 *
 * 说明：
 * - 订单生成时，基于订单商品表（tbl_order_goods）将商品销量进行累加
 * - 参数直接传递 order_goods_list，避免重复查询
 * - 并发安全：事务由 QueueConsumer 层统一管理
 */
class OrderGenerateSalesIncQueue implements QueueHandlerInterface
{
    /**
     * 执行业务处理
     *
     * @param array $params 必须包含：
     *   - order_goods_list: array 订单商品列表（已在 Listener 中查询）
     * @return void
     * @throws CommonException
     */
    public function handle(array $params): void
    {
        $orderGoodsList = $params['order_goods_list'] ?? [];
        if (empty($orderGoodsList) || !is_array($orderGoodsList)) {
            throw new CommonException('order_goods_list 缺失或格式错误');
        }

        // 累加商品销量（平台自营不再维护店铺销量）
        foreach ($orderGoodsList as $og) {
            $goodsId = (int)($og['goods_id'] ?? 0);
            $goodsNum = (int)($og['goods_num'] ?? 0);

            if ($goodsId <= 0 || $goodsNum <= 0) {
                continue;
            }

            (new TblGoodsDao())->setGoodsInc(['id' => $goodsId], 'sales_num', $goodsNum);
        }
    }
}
