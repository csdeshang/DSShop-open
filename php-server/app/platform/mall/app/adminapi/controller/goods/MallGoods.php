<?php

namespace app\platform\mall\app\adminapi\controller\goods;

use app\adminapi\controller\traits\PlatformGoodsControllerTrait;
use app\deshang\base\controller\BaseAdminController;
use app\platform\mall\app\adminapi\service\goods\MallGoodsService;

class MallGoods extends BaseAdminController
{
    use PlatformGoodsControllerTrait;

    public function getMallGoodsPages()
    {
        $data = array(
            'tab_selected' => input('param.tab_selected'),
            'goods_name' => input('param.goods_name'),
        );

        $result = (new MallGoodsService())->getMallGoodsPages($data);
        return ds_json_success('操作成功', $result);
    }

    public function addMallGoods()
    {
        $data = $this->getPlatformGoodsData();
        $result = (new MallGoodsService())->addMallGoods($data);
        return ds_json_success('操作成功', $result);
    }

    public function updateMallGoods($id)
    {
        $data = $this->getPlatformGoodsData();
        $data['id'] = $id;
        (new MallGoodsService())->updateMallGoods($data);
        return ds_json_success('操作成功');
    }

    public function getMallGoodsUpdateInfo($id)
    {
        $data = array('id' => $id);
        $result = (new MallGoodsService())->getMallGoodsUpdateInfo($data);
        return ds_json_success('操作成功', $result);
    }

    public function softDeleteMallGoods()
    {
        $data = array('goods_ids' => input('param.goods_ids'));
        (new MallGoodsService())->softDeleteMallGoods($data);
        return ds_json_success('操作成功');
    }
}
