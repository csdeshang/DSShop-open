<?php

namespace app\adminapi\service\distributor;

use app\common\dao\distributor\DistributorGoodsDao;
use app\common\dao\distributor\DistributorLevelDao;
use app\common\dao\goods\TblGoodsDao;
use app\common\dao\goods\TblGoodsSkuDao;
use app\common\enum\distributor\DistributorGoodsEnum;
use app\common\enum\goods\TblGoodsEnum;
use app\deshang\base\service\BaseAdminService;
use app\deshang\exceptions\CommonException;

/**
 * 分销商品服务类
 */
class DistributorGoodsService extends BaseAdminService
{
    protected $goodsDao;
    protected $goodsSkuDao;
    protected $distributorLevelDao;
    protected $distributorGoodsDao;

    public function __construct()
    {
        parent::__construct();
        $this->goodsDao = new TblGoodsDao();
        $this->goodsSkuDao = new TblGoodsSkuDao();
        $this->distributorLevelDao = new DistributorLevelDao();
        $this->distributorGoodsDao = new DistributorGoodsDao();
    }

    /**
     * 获取分销商品信息
     */
    public function getDistributorGoodsInfo($goods_id)
    {
        if (empty($goods_id)) {
            throw new CommonException('商品ID不能为空');
        }

        $goodsInfo = $this->goodsDao->getGoodsInfo([
            ['id', '=', $goods_id],
            ['sys_status', '=', TblGoodsEnum::SYS_STATUS_NORMAL],
            ['goods_status', '=', TblGoodsEnum::STATUS_SHELVED]
        ]);
        if (empty($goodsInfo)) {
            throw new CommonException('商品不存在');
        }

        $skuList = $this->goodsSkuDao->getGoodsSkuList([
            ['goods_id', '=', $goods_id]
        ]);

        $levelList = $this->distributorLevelDao->getDistributorLevelList([], '*', 'sort asc');

        $distributorGoodsList = $this->distributorGoodsDao->getDistributorGoodsList([
            ['goods_id', '=', $goods_id]
        ]);

        $skuDistributorList = [];
        foreach ($skuList as $sku) {
            $skuDistributor = [
                'sku_id' => $sku['id'],
                'sku_name' => $sku['sku_name'],
                'sku_price' => $sku['sku_price'],
                'distributor_levels' => []
            ];

            foreach ($levelList as $level) {
                $distributorData = [
                    'distributor_level_id' => $level['id'],
                    'level_name' => $level['name'],
                    'base_self_ratio' => $level['base_self_ratio'],
                    'base_parent1_ratio' => $level['base_parent1_ratio'],
                    'base_parent2_ratio' => $level['base_parent2_ratio'],
                    'goods_self_ratio' => 0,
                    'goods_parent1_ratio' => 0,
                    'goods_parent2_ratio' => 0,
                    'goods_self_amount' => 0,
                    'goods_parent1_amount' => 0,
                    'goods_parent2_amount' => 0
                ];

                foreach ($distributorGoodsList as $dg) {
                    if ($dg['sku_id'] == $sku['id'] && $dg['distributor_level_id'] == $level['id']) {
                        $distributorData = array_merge($distributorData, [
                            'goods_self_ratio' => $dg['goods_self_ratio'],
                            'goods_parent1_ratio' => $dg['goods_parent1_ratio'],
                            'goods_parent2_ratio' => $dg['goods_parent2_ratio'],
                            'goods_self_amount' => $dg['goods_self_amount'],
                            'goods_parent1_amount' => $dg['goods_parent1_amount'],
                            'goods_parent2_amount' => $dg['goods_parent2_amount']
                        ]);
                        break;
                    }
                }

                $skuDistributor['distributor_levels'][] = $distributorData;
            }

            $skuDistributorList[] = $skuDistributor;
        }

        return [
            'goods_info' => $goodsInfo,
            'sku_distributor_list' => $skuDistributorList
        ];
    }

    /**
     * 保存分销商品设置
     */
    public function updateDistributorGoods($goods_id, $data)
    {
        if (empty($goods_id) || empty($data['sku_distributor_list'])) {
            throw new CommonException('参数错误');
        }

        $distributor_goods_type = $data['distributor_goods_type'];
        if (!in_array($distributor_goods_type, array_keys(DistributorGoodsEnum::getDistributorGoodsTypeDict()))) {
            throw new CommonException('佣金类型错误');
        }

        $goodsInfo = $this->goodsDao->getGoodsInfo([
            ['id', '=', $goods_id]
        ]);
        if (empty($goodsInfo)) {
            throw new CommonException('商品不存在');
        }
        if ($goodsInfo['goods_status'] != 1) {
            throw new CommonException('商品未上架，无法设置分销');
        }

        if ($data['is_distributor_goods'] != 1) {
            return $this->cancelDistributorGoods($goods_id);
        }

        if ($distributor_goods_type != DistributorGoodsEnum::TYPE_DEFAULT) {
            $skuList = $this->goodsSkuDao->getGoodsSkuList([
                ['goods_id', '=', $goods_id]
            ]);
            $skuIds = array_column($data['sku_distributor_list'], 'sku_id');
            $validSkuIds = array_column($skuList, 'id');
            if (count(array_diff($skuIds, $validSkuIds)) > 0) {
                throw new CommonException('存在无效的商品规格');
            }

            $levelList = $this->distributorLevelDao->getDistributorLevelList([]);
            $levelIds = [];
            foreach ($data['sku_distributor_list'] as $skuData) {
                $levelIds = array_merge($levelIds, array_column($skuData['distributor_levels'], 'distributor_level_id'));
            }
            $levelIds = array_unique($levelIds);
            $validLevelIds = array_column($levelList, 'id');
            if (count(array_diff($levelIds, $validLevelIds)) > 0) {
                throw new CommonException('存在无效的分销等级');
            }

            $skuPrices = array_column($skuList, 'sku_price', 'id');
            foreach ($data['sku_distributor_list'] as $skuData) {
                $skuPrice = $skuPrices[$skuData['sku_id']] ?? 0;

                foreach ($skuData['distributor_levels'] as $levelData) {
                    $levelInfo = array_filter($levelList, function ($level) use ($levelData) {
                        return $level['id'] == $levelData['distributor_level_id'];
                    });
                    $levelInfo = reset($levelInfo);

                    if ($distributor_goods_type == DistributorGoodsEnum::TYPE_RATIO) {
                        if ($levelData['goods_self_ratio'] < $levelInfo['base_self_ratio']) {
                            throw new CommonException('自购佣金比例不能低于分销等级基础比例(' . $levelInfo['base_self_ratio'] . '%)');
                        }
                        if ($levelData['goods_parent1_ratio'] < $levelInfo['base_parent1_ratio']) {
                            throw new CommonException('一级佣金比例不能低于分销等级基础比例(' . $levelInfo['base_parent1_ratio'] . '%)');
                        }
                        if ($levelData['goods_parent2_ratio'] < $levelInfo['base_parent2_ratio']) {
                            throw new CommonException('二级佣金比例不能低于分销等级基础比例(' . $levelInfo['base_parent2_ratio'] . '%)');
                        }

                        if ($levelData['goods_self_ratio'] < 0 || $levelData['goods_self_ratio'] > 100) {
                            throw new CommonException('自购佣金比例必须在0-100之间');
                        }
                        if ($levelData['goods_parent1_ratio'] < 0 || $levelData['goods_parent1_ratio'] > 100) {
                            throw new CommonException('一级佣金比例必须在0-100之间');
                        }
                        if ($levelData['goods_parent2_ratio'] < 0 || $levelData['goods_parent2_ratio'] > 100) {
                            throw new CommonException('二级佣金比例必须在0-100之间');
                        }

                        $totalRatio = $levelData['goods_self_ratio'] +
                            $levelData['goods_parent1_ratio'] +
                            $levelData['goods_parent2_ratio'];
                        if ($totalRatio > 100) {
                            throw new CommonException('总佣金比例不能超过100%');
                        }
                    }

                    if ($distributor_goods_type == DistributorGoodsEnum::TYPE_FIXED) {
                        $minSelfAmount = round($skuPrice * $levelInfo['base_self_ratio'] / 100, 4);
                        $minParent1Amount = round($skuPrice * $levelInfo['base_parent1_ratio'] / 100, 4);
                        $minParent2Amount = round($skuPrice * $levelInfo['base_parent2_ratio'] / 100, 4);

                        if ($levelData['goods_self_amount'] < $minSelfAmount) {
                            throw new CommonException('自购佣金金额不能低于基础比例金额(' . $minSelfAmount . '元)');
                        }
                        if ($levelData['goods_parent1_amount'] < $minParent1Amount) {
                            throw new CommonException('一级佣金金额不能低于基础比例金额(' . $minParent1Amount . '元)');
                        }
                        if ($levelData['goods_parent2_amount'] < $minParent2Amount) {
                            throw new CommonException('二级佣金金额不能低于基础比例金额(' . $minParent2Amount . '元)');
                        }

                        if ($levelData['goods_self_amount'] > $skuPrice) {
                            throw new CommonException('自购佣金金额不能超过商品价格');
                        }
                        if ($levelData['goods_parent1_amount'] > $skuPrice) {
                            throw new CommonException('一级佣金金额不能超过商品价格');
                        }
                        if ($levelData['goods_parent2_amount'] > $skuPrice) {
                            throw new CommonException('二级佣金金额不能超过商品价格');
                        }

                        $totalAmount = $levelData['goods_self_amount'] +
                            $levelData['goods_parent1_amount'] +
                            $levelData['goods_parent2_amount'];
                        if ($totalAmount > $skuPrice) {
                            throw new CommonException('总佣金金额不能超过商品价格');
                        }
                    }
                }
            }
        }

        $this->distributorGoodsDao->deleteDistributorGoods([
            ['goods_id', '=', $goods_id]
        ]);

        if ($distributor_goods_type != DistributorGoodsEnum::TYPE_DEFAULT) {
            $allData = array();
            foreach ($data['sku_distributor_list'] as $skuData) {
                foreach ($skuData['distributor_levels'] as $levelData) {
                    $saveData = [
                        'goods_id' => $goods_id,
                        'sku_id' => $skuData['sku_id'],
                        'distributor_level_id' => $levelData['distributor_level_id'],
                        'create_at' => time(),
                        'update_at' => time()
                    ];

                    if ($distributor_goods_type == DistributorGoodsEnum::TYPE_RATIO) {
                        $saveData['goods_self_ratio'] = $levelData['goods_self_ratio'];
                        $saveData['goods_parent1_ratio'] = $levelData['goods_parent1_ratio'];
                        $saveData['goods_parent2_ratio'] = $levelData['goods_parent2_ratio'];
                        $saveData['goods_self_amount'] = 0;
                        $saveData['goods_parent1_amount'] = 0;
                        $saveData['goods_parent2_amount'] = 0;
                    }

                    if ($distributor_goods_type == DistributorGoodsEnum::TYPE_FIXED) {
                        $saveData['goods_self_ratio'] = 0;
                        $saveData['goods_parent1_ratio'] = 0;
                        $saveData['goods_parent2_ratio'] = 0;
                        $saveData['goods_self_amount'] = $levelData['goods_self_amount'];
                        $saveData['goods_parent1_amount'] = $levelData['goods_parent1_amount'];
                        $saveData['goods_parent2_amount'] = $levelData['goods_parent2_amount'];
                    }
                    $allData[] = $saveData;
                }
            }
            $this->distributorGoodsDao->createDistributorGoodsAll($allData);
        }

        $this->goodsDao->updateGoods([['id', '=', $goods_id]], [
            'distributor_goods_type' => $distributor_goods_type,
            'is_distributor_goods' => 1,
        ]);

        return true;
    }

    /**
     * 取消商品分销佣金
     */
    public function cancelDistributorGoods($goods_id)
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

        if ($goodsInfo['is_distributor_goods'] != 1) {
            return true;
        }

        $this->distributorGoodsDao->deleteDistributorGoods([
            ['goods_id', '=', $goods_id]
        ]);

        $this->goodsDao->updateGoods([['id', '=', $goods_id]], [
            'distributor_goods_type' => DistributorGoodsEnum::TYPE_DEFAULT,
            'is_distributor_goods' => 0,
        ]);

        return true;
    }
}
