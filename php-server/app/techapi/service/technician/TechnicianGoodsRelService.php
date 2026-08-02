<?php

namespace app\techapi\service\technician;

use app\deshang\base\service\BaseTechnicianService;

use app\common\dao\goods\TblGoodsDao;
use app\common\dao\technician\TechnicianGoodsRelDao;

use app\common\enum\goods\TblGoodsEnum;

use app\deshang\exceptions\CommonException;

class TechnicianGoodsRelService extends BaseTechnicianService
{

    public function __construct()
    {
        parent::__construct();
    }

    // 获取师傅商品关联
    public function getTechnicianGoodsRelPages()
    {
        $technician_id = $this->technician_id;

        // 平台自营：可关联全部上架商品
        $goods_result = (new TblGoodsDao())->getGoodsPages([
            ['is_deleted', '=', 0],
            ['goods_status', '=', TblGoodsEnum::STATUS_SHELVED],
        ], '*', 'id desc');

        // 获取师傅已关联的商品ID列表
        $rel_goods_ids = (new TechnicianGoodsRelDao())->getTechnicianGoodsRelColumn([
            'technician_id' => $technician_id
        ], 'goods_id');

        // 标记商品是否已关联
        foreach ($goods_result['data'] as &$goods) {
            $goods['technician_is_rel'] = in_array($goods['id'], $rel_goods_ids) ? 1 : 0;
        }

        return $goods_result;
    }

    // 师傅商品关联更新
    public function updateTechnicianGoodsRel($goods_id)
    {

        $technician_id = $this->technician_id;

        // 验证商品是否存在且可关联
        $goods_info = (new TblGoodsDao())->getGoodsInfo([
            ['id', '=', $goods_id],
            ['is_deleted', '=', 0],
        ]);

        if (empty($goods_info)) {
            throw new CommonException('商品不存在或不可关联');
        }

        $dao = new TechnicianGoodsRelDao();

        // 检查是否已存在关联
        $count = $dao->getTechnicianGoodsRelCount([
            'goods_id' => $goods_id,
            'technician_id' => $technician_id
        ]);

        if ($count > 0) {
            // 如果已存在关联，则删除
            $result = $dao->deleteTechnicianGoodsRel([
                'goods_id' => $goods_id,
                'technician_id' => $technician_id
            ]);
            return true;
        } else {
            // 如果不存在关联，则添加
            $data = [
                'goods_id' => $goods_id,
                'technician_id' => $technician_id,
            ];
            $result = $dao->createTechnicianGoodsRel($data);
            return $result > 0;
        }
    }
}
