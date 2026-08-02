<?php

namespace app\adminapi\service\goods;

use app\deshang\base\service\BaseAdminService;
use app\deshang\exceptions\CommonException;

use app\common\dao\goods\TblGoodsWholesaleDao;
use app\common\dao\goods\TblGoodsDao;
use app\common\dao\goods\TblGoodsSkuDao;

use app\common\enum\goods\TblGoodsEnum;

// 批发价格阶梯
class TblGoodsWholesaleService extends BaseAdminService
{
    protected $goodsDao;
    protected $goodsSkuDao;
    protected $goodsWholesaleDao;

    public function __construct()
    {
        parent::__construct();
        $this->goodsDao = new TblGoodsDao();
        $this->goodsSkuDao = new TblGoodsSkuDao();
        $this->goodsWholesaleDao = new TblGoodsWholesaleDao();
    }

    /**
     * 获取商品批发价格阶梯信息
     */
    public function getTblGoodsWholesaleInfo($goods_id)
    {
        if (empty($goods_id)) {
            throw new CommonException('商品ID不能为空');
        }

        // 获取商品信息
        $goodsInfo = $this->goodsDao->getGoodsInfo([
            ['id', '=', $goods_id],
            ['sys_status', '=', TblGoodsEnum::SYS_STATUS_NORMAL],
            ['goods_status', '=', TblGoodsEnum::STATUS_SHELVED]
        ]);
        if (empty($goodsInfo)) {
            throw new CommonException('商品不存在');
        }

        // 获取规格列表
        $skuList = $this->goodsSkuDao->getGoodsSkuList([
            ['goods_id', '=', $goods_id]
        ]);

        // 获取已设置的批发价格阶梯数据
        $goodsWholesaleList = $this->goodsWholesaleDao->getGoodsWholesaleList([
            ['goods_id', '=', $goods_id]
        ]);

        // 组合数据
        $skuWholesaleList = [];
        foreach ($skuList as $sku) {
            $skuWholesale = [
                'sku_id' => $sku['id'],
                'sku_name' => $sku['sku_name'],
                'sku_price' => $sku['sku_price'],
                'wholesale_ladders' => []
            ];

            // 查找已设置的数据
            foreach ($goodsWholesaleList as $wholesale) {
                if ($wholesale['sku_id'] == $sku['id']) {
                    $skuWholesale['wholesale_ladders'][] = [
                        'id' => $wholesale['id'],
                        'wholesale_price' => $wholesale['wholesale_price'],
                        'quantity_min' => $wholesale['quantity_min'],
                        'quantity_max' => $wholesale['quantity_max']
                    ];
                }
            }

            // 按照最小数量排序
            if (!empty($skuWholesale['wholesale_ladders'])) {
                usort($skuWholesale['wholesale_ladders'], function ($a, $b) {
                    return $a['quantity_min'] - $b['quantity_min'];
                });
            }

            $skuWholesaleList[] = $skuWholesale;
        }

        return [
            'goods_info' => $goodsInfo,
            'sku_wholesale_list' => $skuWholesaleList
        ];
    }

    /**
     * 更新商品批发价格阶梯
     */
    public function updateTblGoodsWholesale(int $goods_id, array $data)
    {
        // 验证参数
        if (empty($goods_id) || empty($data['sku_wholesale_list'])) {
            throw new CommonException('参数错误');
        }

        // 验证商品
        $goodsInfo = $this->goodsDao->getGoodsInfo([
            ['id', '=', $goods_id]
        ]);
        if (empty($goodsInfo)) {
            throw new CommonException('商品不存在');
        }
        if ($goodsInfo['goods_status'] != TblGoodsEnum::STATUS_SHELVED) {
            throw new CommonException('商品未上架，无法设置批发价格');
        }

        if (empty($data['is_wholesale_goods']) || $data['is_wholesale_goods'] != 1) {
            return $this->deleteTblGoodsWholesale($goods_id);
        }

        // 验证规格
        $skuList = $this->goodsSkuDao->getGoodsSkuList([
            ['goods_id', '=', $goods_id]
        ]);
        $skuIds = array_column($data['sku_wholesale_list'], 'sku_id');
        $validSkuIds = array_column($skuList, 'id');
        if (count(array_diff($skuIds, $validSkuIds)) > 0) {
            throw new CommonException('存在无效的商品规格');
        }

        // 验证批发价格数据
        $skuPrices = array_column($skuList, 'sku_price', 'id');
        foreach ($data['sku_wholesale_list'] as $skuData) {
            $skuPrice = $skuPrices[$skuData['sku_id']] ?? 0;

            if (empty($skuData['wholesale_ladders'])) {
                continue;
            }

            // 验证价格阶梯区间
            $ladders = $skuData['wholesale_ladders'];
            usort($ladders, function ($a, $b) {
                return $a['quantity_min'] - $b['quantity_min'];
            });

            for ($i = 0; $i < count($ladders); $i++) {
                $ladder = $ladders[$i];

                if ($ladder['quantity_min'] <= 0) {
                    throw new CommonException('最小数量必须大于0');
                }

                if ($ladder['wholesale_price'] <= 0) {
                    throw new CommonException('批发价格必须大于0');
                }

                if ($ladder['wholesale_price'] >= $skuPrice) {
                    throw new CommonException('批发价格必须低于原价');
                }

                if ($i > 0) {
                    $prevLadder = $ladders[$i - 1];
                    if ($prevLadder['quantity_max'] + 1 != $ladder['quantity_min']) {
                        throw new CommonException('价格阶梯区间必须连续');
                    }
                }

                if ($i == count($ladders) - 1 && $ladder['quantity_max'] !== null && $ladder['quantity_max'] <= $ladder['quantity_min']) {
                    throw new CommonException('最大数量必须大于最小数量');
                } else if ($i < count($ladders) - 1 && ($ladder['quantity_max'] === null || $ladder['quantity_max'] <= $ladder['quantity_min'])) {
                    throw new CommonException('最大数量必须大于最小数量');
                }
            }
        }

        // 删除原有数据
        $this->goodsWholesaleDao->deleteGoodsWholesale([
            ['goods_id', '=', $goods_id]
        ]);

        // 保存新数据
        $allData = array();
        foreach ($data['sku_wholesale_list'] as $skuData) {
            if (empty($skuData['wholesale_ladders'])) {
                continue;
            }

            foreach ($skuData['wholesale_ladders'] as $ladder) {
                $saveData = [
                    'goods_id' => $goods_id,
                    'sku_id' => $skuData['sku_id'],
                    'wholesale_price' => $ladder['wholesale_price'],
                    'quantity_min' => $ladder['quantity_min'],
                    'quantity_max' => $ladder['quantity_max'],
                    'create_at' => time(),
                ];

                $allData[] = $saveData;
            }
        }

        if (!empty($allData)) {
            $this->goodsWholesaleDao->createGoodsWholesaleAll($allData);

            $this->goodsDao->updateGoods([['id', '=', $goods_id]], [
                'is_wholesale_goods' => 1,
            ]);
        }

        return true;
    }

    /**
     * 删除批发价格设置
     */
    public function deleteTblGoodsWholesale(int $goods_id)
    {
        if (empty($goods_id)) {
            throw new CommonException('商品ID不能为空');
        }

        $goodsInfo = $this->goodsDao->getGoodsInfo([
            ['id', '=', $goods_id]
        ]);
        if (empty($goodsInfo)) {
            throw new CommonException('商品不存在');
        }

        $this->goodsWholesaleDao->deleteGoodsWholesale([
            ['goods_id', '=', $goods_id]
        ]);

        $this->goodsDao->updateGoods([['id', '=', $goods_id]], [
            'is_wholesale_goods' => 0,
        ]);

        return true;
    }

    /**
     * 获取商品的批发价格
     */
    public function getGoodsWholesalePrice(int $goods_id, int $sku_id, int $quantity)
    {
        if ($quantity <= 0) {
            return 0;
        }

        $skuInfo = $this->goodsSkuDao->getGoodsSkuInfo([
            ['id', '=', $sku_id],
            ['goods_id', '=', $goods_id]
        ]);

        if (empty($skuInfo)) {
            return 0;
        }

        $wholesaleInfo = $this->goodsWholesaleDao->getGoodsWholesaleInfo([
            ['goods_id', '=', $goods_id],
            ['sku_id', '=', $sku_id],
            ['quantity_min', '<=', $quantity],
            ['quantity_max', '>=', $quantity, 'OR'],
            ['quantity_max', 'is', 'NULL', 'OR']
        ], '*', 'quantity_min DESC');

        if (empty($wholesaleInfo)) {
            return $skuInfo['sku_price'];
        }

        return $wholesaleInfo['wholesale_price'];
    }
}
