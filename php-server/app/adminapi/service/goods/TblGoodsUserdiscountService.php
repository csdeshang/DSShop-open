<?php

namespace app\adminapi\service\goods;

use app\deshang\base\service\BaseAdminService;
use app\deshang\exceptions\CommonException;

use app\common\dao\goods\TblGoodsUserdiscountDao;
use app\common\dao\goods\TblGoodsDao;
use app\common\dao\goods\TblGoodsSkuDao;
use app\common\dao\user\UserGrowthLevelDao;

use app\common\enum\goods\TblGoodsEnum;

// 平台会员等级价格阶梯
class TblGoodsUserdiscountService extends BaseAdminService
{
    protected $goodsDao;
    protected $goodsSkuDao;
    protected $goodsUserdiscountDao;
    protected $userGrowthLevelDao;

    public function __construct()
    {
        parent::__construct();
        $this->goodsDao = new TblGoodsDao();
        $this->goodsSkuDao = new TblGoodsSkuDao();
        $this->goodsUserdiscountDao = new TblGoodsUserdiscountDao();
        $this->userGrowthLevelDao = new UserGrowthLevelDao();
    }

    /**
     * 获取商品平台会员等级价格信息
     */
    public function getTblGoodsUserdiscountInfo($goods_id)
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

        $levelList = $this->userGrowthLevelDao->getUserGrowthLevelList([], '*', 'min_growth asc');

        $goodsUserdiscountList = $this->goodsUserdiscountDao->getGoodsUserdiscountList([
            ['goods_id', '=', $goods_id]
        ]);

        $skuUserdiscountList = [];
        foreach ($skuList as $sku) {
            $skuUserdiscount = [
                'sku_id' => $sku['id'],
                'sku_name' => $sku['sku_name'],
                'sku_price' => $sku['sku_price'],
                'user_levels' => []
            ];

            foreach ($levelList as $level) {
                $levelDiscount = [
                    'user_level_id' => $level['id'],
                    'level_name' => $level['level_name'],
                    'min_growth' => $level['min_growth'],
                    'user_level_price' => 0
                ];

                foreach ($goodsUserdiscountList as $discount) {
                    if ($discount['sku_id'] == $sku['id'] && $discount['user_level_id'] == $level['id']) {
                        $levelDiscount['user_level_price'] = $discount['user_level_price'];
                        break;
                    }
                }

                $skuUserdiscount['user_levels'][] = $levelDiscount;
            }

            $skuUserdiscountList[] = $skuUserdiscount;
        }

        return [
            'goods_info' => $goodsInfo,
            'sku_userdiscount_list' => $skuUserdiscountList
        ];
    }

    /**
     * 更新商品平台会员等级价格
     */
    public function updateTblGoodsUserdiscount(int $goods_id, array $data)
    {
        if (empty($goods_id) || empty($data['sku_userdiscount_list'])) {
            throw new CommonException('参数错误');
        }

        $goodsInfo = $this->goodsDao->getGoodsInfo([
            ['id', '=', $goods_id]
        ]);
        if (empty($goodsInfo)) {
            throw new CommonException('商品不存在');
        }
        if ($goodsInfo['goods_status'] != TblGoodsEnum::STATUS_SHELVED) {
            throw new CommonException('商品未上架，无法设置会员价格');
        }

        if (empty($data['is_userdiscount_goods']) || $data['is_userdiscount_goods'] != 1) {
            return $this->deleteTblGoodsUserdiscount($goods_id);
        }

        $skuList = $this->goodsSkuDao->getGoodsSkuList([
            ['goods_id', '=', $goods_id]
        ]);
        $skuIds = array_column($data['sku_userdiscount_list'], 'sku_id');
        $validSkuIds = array_column($skuList, 'id');
        if (count(array_diff($skuIds, $validSkuIds)) > 0) {
            throw new CommonException('存在无效的商品规格');
        }

        $levelList = $this->userGrowthLevelDao->getUserGrowthLevelList([]);
        $levelIds = [];
        foreach ($data['sku_userdiscount_list'] as $skuData) {
            if (!empty($skuData['user_levels'])) {
                $levelIds = array_merge($levelIds, array_column($skuData['user_levels'], 'user_level_id'));
            }
        }
        $levelIds = array_unique($levelIds);
        $validLevelIds = array_column($levelList, 'id');
        if (count(array_diff($levelIds, $validLevelIds)) > 0) {
            throw new CommonException('存在无效的会员等级');
        }

        $skuPrices = array_column($skuList, 'sku_price', 'id');
        foreach ($data['sku_userdiscount_list'] as $skuData) {
            $skuPrice = $skuPrices[$skuData['sku_id']] ?? 0;

            if (empty($skuData['user_levels'])) {
                continue;
            }

            foreach ($skuData['user_levels'] as $levelData) {
                if ($levelData['user_level_price'] <= 0) {
                    throw new CommonException('会员价格必须大于0');
                }

                if ($levelData['user_level_price'] >= $skuPrice) {
                    throw new CommonException('会员价格必须低于原价');
                }
            }
        }

        $this->goodsUserdiscountDao->deleteGoodsUserdiscount([
            ['goods_id', '=', $goods_id]
        ]);

        $allData = array();
        foreach ($data['sku_userdiscount_list'] as $skuData) {
            if (empty($skuData['user_levels'])) {
                continue;
            }

            foreach ($skuData['user_levels'] as $levelData) {
                if ($levelData['user_level_price'] <= 0) {
                    continue;
                }

                $saveData = [
                    'goods_id' => $goods_id,
                    'sku_id' => $skuData['sku_id'],
                    'user_level_id' => $levelData['user_level_id'],
                    'user_level_price' => $levelData['user_level_price'],
                    'create_at' => time()
                ];

                $allData[] = $saveData;
            }
        }

        if (!empty($allData)) {
            $this->goodsUserdiscountDao->createGoodsUserdiscountAll($allData);

            $this->goodsDao->updateGoods([['id', '=', $goods_id]], [
                'is_userdiscount_goods' => 1,
            ]);
        }

        return true;
    }

    /**
     * 删除会员价格设置
     */
    public function deleteTblGoodsUserdiscount(int $goods_id)
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

        $this->goodsUserdiscountDao->deleteGoodsUserdiscount([
            ['goods_id', '=', $goods_id]
        ]);

        $this->goodsDao->updateGoods([['id', '=', $goods_id]], [
            'is_userdiscount_goods' => 0,
        ]);

        return true;
    }

    /**
     * 获取商品的会员价格
     */
    public function getGoodsUserdiscountPrice(int $goods_id, int $sku_id, int $user_level_id)
    {
        if (empty($user_level_id)) {
            return 0;
        }

        $skuInfo = $this->goodsSkuDao->getGoodsSkuInfo([
            ['id', '=', $sku_id],
            ['goods_id', '=', $goods_id]
        ]);

        if (empty($skuInfo)) {
            return 0;
        }

        $userdiscountInfo = $this->goodsUserdiscountDao->getGoodsUserdiscountInfo([
            ['goods_id', '=', $goods_id],
            ['sku_id', '=', $sku_id],
            ['user_level_id', '=', $user_level_id]
        ]);

        if (empty($userdiscountInfo) || $userdiscountInfo['user_level_price'] <= 0) {
            return $skuInfo['sku_price'];
        }

        return $userdiscountInfo['user_level_price'];
    }
}
