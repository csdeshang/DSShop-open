<?php

namespace app\platform\house\app\adminapi\controller\goods;

use app\adminapi\controller\traits\PlatformGoodsControllerTrait;
use app\deshang\base\controller\BaseAdminController;
use app\platform\house\app\adminapi\service\goods\HouseGoodsService;

class HouseGoods extends BaseAdminController
{
    use PlatformGoodsControllerTrait;

    public function getHouseGoodsPages()
    {
        $data = array(
            'tab_selected' => input('param.tab_selected'),
            'goods_name' => input('param.goods_name'),
        );
        $result = (new HouseGoodsService())->getHouseGoodsPages($data);
        return ds_json_success('操作成功', $result);
    }

    public function addHouseGoods()
    {
        $data = $this->getPlatformGoodsData();
        $result = (new HouseGoodsService())->addHouseGoods($data);
        return ds_json_success('操作成功', $result);
    }

    public function updateHouseGoods($id)
    {
        $data = $this->getPlatformGoodsData();
        $data['id'] = $id;
        (new HouseGoodsService())->updateHouseGoods($data);
        return ds_json_success('操作成功');
    }

    public function getHouseGoodsUpdateInfo($id)
    {
        $data = array('id' => $id);
        $result = (new HouseGoodsService())->getHouseGoodsUpdateInfo($data);
        return ds_json_success('操作成功', $result);
    }

    public function softDeleteHouseGoods()
    {
        $data = array('goods_ids' => input('param.goods_ids'));
        (new HouseGoodsService())->softDeleteHouseGoods($data);
        return ds_json_success('操作成功');
    }
}
