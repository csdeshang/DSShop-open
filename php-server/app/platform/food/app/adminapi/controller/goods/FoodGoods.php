<?php

namespace app\platform\food\app\adminapi\controller\goods;

use app\adminapi\controller\traits\PlatformGoodsControllerTrait;
use app\deshang\base\controller\BaseAdminController;
use app\platform\food\app\adminapi\service\goods\FoodGoodsService;

class FoodGoods extends BaseAdminController
{
    use PlatformGoodsControllerTrait;

    public function getFoodGoodsPages()
    {
        $data = array(
            'tab_selected' => input('param.tab_selected'),
            'goods_name' => input('param.goods_name'),
        );
        $result = (new FoodGoodsService())->getFoodGoodsPages($data);
        return ds_json_success('操作成功', $result);
    }

    public function addFoodGoods()
    {
        $data = $this->getPlatformGoodsData();
        $result = (new FoodGoodsService())->addFoodGoods($data);
        return ds_json_success('操作成功', $result);
    }

    public function updateFoodGoods($id)
    {
        $data = $this->getPlatformGoodsData();
        $data['id'] = $id;
        (new FoodGoodsService())->updateFoodGoods($data);
        return ds_json_success('操作成功');
    }

    public function getFoodGoodsUpdateInfo($id)
    {
        $data = array('id' => $id);
        $result = (new FoodGoodsService())->getFoodGoodsUpdateInfo($data);
        return ds_json_success('操作成功', $result);
    }

    public function softDeleteFoodGoods()
    {
        $data = array('goods_ids' => input('param.goods_ids'));
        (new FoodGoodsService())->softDeleteFoodGoods($data);
        return ds_json_success('操作成功');
    }
}
