<?php

namespace app\adminapi\service\goods;

use app\deshang\base\service\BaseAdminService;
use app\deshang\exceptions\CommonException;

use app\common\dao\goods\TblGoodsFlashsaleDao;
use app\common\dao\goods\TblGoodsDao;
use app\common\dao\goods\TblGoodsSkuDao;

use app\common\enum\goods\TblGoodsFlashsaleEnum;
use app\common\enum\goods\TblGoodsEnum;

// 限时折扣商品
class TblGoodsFlashsaleService extends BaseAdminService
{

    protected $goodsDao;
    protected $goodsSkuDao;
    protected $goodsFlashsaleDao;

    public function __construct()
    {
        parent::__construct();
        $this->goodsDao = new TblGoodsDao();
        $this->goodsSkuDao = new TblGoodsSkuDao();
        $this->goodsFlashsaleDao = new TblGoodsFlashsaleDao();
    }

    // 获取限时折扣商品信息
    public function getTblGoodsFlashsaleInfo($goods_id)
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

        // 获取已设置的限时折扣数据
        $goodsFlashsaleList = $this->goodsFlashsaleDao->getGoodsFlashsaleList([
            ['goods_id', '=', $goods_id]
        ]);

        // 组合数据
        $skuFlashsaleList = [];
        foreach ($skuList as $sku) {
            $skuFlashsale = [
                'sku_id' => $sku['id'],
                'sku_name' => $sku['sku_name'],
                'sku_price' => $sku['sku_price'],
                'flashsale_price' => 0,
                'start_time' => 0,
                'end_time' => 0,
                'status' => 0
            ];

            // 查找已设置的数据
            foreach ($goodsFlashsaleList as $flash) {
                if ($flash['sku_id'] == $sku['id']) {
                    $skuFlashsale = array_merge($skuFlashsale, [
                        'flashsale_price' => $flash['flashsale_price'],
                        'start_time' => $flash['start_time'],
                        'end_time' => $flash['end_time'],
                        'status' => $flash['status'],
                        'promotion_id' => $flash['promotion_id']
                    ]);
                    break;
                }
            }

            $skuFlashsaleList[] = $skuFlashsale;
        }

        return [
            'goods_info' => $goodsInfo,
            'sku_flashsale_list' => $skuFlashsaleList
        ];
    }

    // 更新商品限时折扣
    public function updateTblGoodsFlashsale(int $goods_id, array $data)
    {
        // 验证参数
        if (empty($goods_id) || empty($data['sku_flashsale_list'])) {
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
            throw new CommonException('商品未上架，无法设置限时折扣');
        }

        if ($data['is_flashsale_goods'] != 1) {
            return $this->deleteTblGoodsFlashsale($goods_id);
        }

        // 验证规格
        $skuList = $this->goodsSkuDao->getGoodsSkuList([
            ['goods_id', '=', $goods_id]
        ]);
        $skuIds = array_column($data['sku_flashsale_list'], 'sku_id');
        $validSkuIds = array_column($skuList, 'id');
        if (count(array_diff($skuIds, $validSkuIds)) > 0) {
            throw new CommonException('存在无效的商品规格');
        }

        // 验证限时折扣数据
        $skuPrices = array_column($skuList, 'sku_price', 'id');
        foreach ($data['sku_flashsale_list'] as $skuData) {
            $skuPrice = $skuPrices[$skuData['sku_id']] ?? 0;
            $startTime = strtotime($skuData['start_time']);
            $endTime = strtotime($skuData['end_time']);

            // 验证折扣价
            if ($skuData['flashsale_price'] <= 0) {
                throw new CommonException('折扣价必须大于0');
            }

            if ($skuData['flashsale_price'] >= $skuPrice) {
                throw new CommonException('折扣价必须低于原价');
            }

            // 验证时间
            if (empty($startTime) || empty($endTime)) {
                throw new CommonException('开始时间和结束时间不能为空');
            }

            if ($startTime >= $endTime) {
                throw new CommonException('开始时间必须早于结束时间');
            }
        }

        // 删除原有数据
        $this->goodsFlashsaleDao->deleteGoodsFlashsale([
            ['goods_id', '=', $goods_id]
        ]);

        // 保存新数据
        $allData = array();
        foreach ($data['sku_flashsale_list'] as $skuData) {
            $startTime = strtotime($skuData['start_time']);
            $endTime = strtotime($skuData['end_time']);
            $now = time();
            $status = 0;
            if ($startTime <= $now && $endTime > $now) {
                $status = TblGoodsFlashsaleEnum::STATUS_START;
            } else if ($endTime <= $now) {
                $status = TblGoodsFlashsaleEnum::STATUS_END;
            } else {
                $status = TblGoodsFlashsaleEnum::STATUS_NOT_START;
            }

            $saveData = [
                'goods_id' => $goods_id,
                'sku_id' => $skuData['sku_id'],
                'promotion_id' => $skuData['promotion_id'] ?? 0,
                'flashsale_price' => $skuData['flashsale_price'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status,
                'create_at' => time(),
                'update_at' => time()
            ];

            $allData[] = $saveData;
        }

        if (!empty($allData)) {
            $this->goodsFlashsaleDao->createGoodsFlashsaleAll($allData);
            // 更新商品数据表
            $this->goodsDao->updateGoods([['id', '=', $goods_id]], [
                'flashsale_goods_type' => $status,
                'is_flashsale_goods' => 1,
            ]);
        }

        return true;
    }

    // 删除限时折扣商品
    public function deleteTblGoodsFlashsale(int $goods_id)
    {
        // 验证参数
        if (empty($goods_id)) {
            throw new CommonException('商品ID不能为空');
        }

        // 验证商品
        $goodsInfo = $this->goodsDao->getGoodsInfo([
            ['id', '=', $goods_id]
        ]);
        if (empty($goodsInfo)) {
            throw new CommonException('商品不存在');
        }

        // 删除限时折扣数据
        $this->goodsFlashsaleDao->deleteGoodsFlashsale([
            ['goods_id', '=', $goods_id]
        ]);

        // 更新商品数据表
        $this->goodsDao->updateGoods([['id', '=', $goods_id]], [
            'flashsale_goods_type' => TblGoodsFlashsaleEnum::STATUS_NOT_START,
            'is_flashsale_goods' => 0,
        ]);

        return true;
    }
}
