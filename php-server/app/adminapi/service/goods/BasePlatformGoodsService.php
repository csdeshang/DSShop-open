<?php

namespace app\adminapi\service\goods;

use app\common\dao\goods\TblGoodsDao;
use app\common\enum\goods\TblGoodsEnum;
use app\deshang\base\service\BaseAdminService;
use app\deshang\service\goods\DeshangTblGoodsService;
use think\facade\Db;

/**
 * 平台自营商品服务基类（B2C：无店铺维度）
 */
abstract class BasePlatformGoodsService extends BaseAdminService
{
    protected string $platform;

    public function __construct(string $platform)
    {
        parent::__construct();
        $this->platform = $platform;
    }

    public function getGoodsPages(array $data): array
    {
        $condition = [
            ['platform', '=', $this->platform],
            ['is_deleted', '=', 0],
        ];

        $tabConditions = [
            'pending' => [['goods_status', '=', TblGoodsEnum::STATUS_SHELVED]],
            'un_send' => [['goods_status', '=', TblGoodsEnum::STATUS_UNSHELVED]],
            'pending_review' => [['sys_status', '=', TblGoodsEnum::SYS_STATUS_PENDING_REVIEW]],
            'review_failed' => [['sys_status', '=', TblGoodsEnum::SYS_STATUS_FAILED]],
            'violated' => [['sys_status', '=', TblGoodsEnum::SYS_STATUS_VIOLATED]],
        ];

        if (isset($data['tab_selected']) && isset($tabConditions[$data['tab_selected']])) {
            $condition = array_merge($condition, $tabConditions[$data['tab_selected']]);
        }

        if (!empty($data['goods_name'])) {
            $condition[] = ['goods_name', 'like', '%' . $data['goods_name'] . '%'];
        }

        $this->applyAdditionalFilters($condition, $data);

        return (new TblGoodsDao())->getWithRelGoodsPages($condition);
    }

    public function addGoods(array $data)
    {
        $data['platform'] = $this->platform;

        Db::startTrans();
        try {
            $goods_id = (new DeshangTblGoodsService())->addTblGoods($data);
            $this->afterAddGoods($goods_id, $data);
            Db::commit();
            return $goods_id;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function updateGoods(array $data)
    {
        $data['platform'] = $this->platform;

        Db::startTrans();
        try {
            (new DeshangTblGoodsService())->updateTblGoods($data);
            $this->afterUpdateGoods($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function getGoodsUpdateInfo(array $data)
    {
        $data['platform'] = $this->platform;

        $goods_info = (new DeshangTblGoodsService())->getTblGoodsUpdateInfo($data);
        $this->afterGetGoodsUpdateInfo($goods_info);
        return $goods_info;
    }

    public function softDeleteGoods(array $data)
    {
        $data['platform'] = $this->platform;

        Db::startTrans();
        try {
            $goods_ids = (new DeshangTblGoodsService())->softDeleteTblGoods($data);
            $this->afterSoftDeleteGoods($goods_ids, $data);
            Db::commit();
            return $goods_ids;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    protected function applyAdditionalFilters(array &$condition, array $data): void {}
    protected function afterAddGoods($goods_id, array $data): void {}
    protected function afterUpdateGoods(array $data): void {}
    protected function afterGetGoodsUpdateInfo(array &$goods_info): void {}
    protected function afterSoftDeleteGoods($goods_ids, array $data): void {}
}
