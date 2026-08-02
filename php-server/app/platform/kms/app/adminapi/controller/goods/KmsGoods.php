<?php

namespace app\platform\kms\app\adminapi\controller\goods;

use app\adminapi\controller\traits\PlatformGoodsControllerTrait;
use app\deshang\base\controller\BaseAdminController;
use app\platform\kms\app\adminapi\service\goods\KmsGoodsService;

class KmsGoods extends BaseAdminController
{
    use PlatformGoodsControllerTrait;

    public function getKmsGoodsPages()
    {
        $data = array(
            'tab_selected' => input('param.tab_selected'),
            'goods_name' => input('param.goods_name'),
        );
        $result = (new KmsGoodsService())->getKmsGoodsPages($data);
        return ds_json_success('操作成功', $result);
    }

    public function addKmsGoods()
    {
        $data = $this->getPlatformGoodsData();
        $result = (new KmsGoodsService())->addKmsGoods($data);
        return ds_json_success('操作成功', $result);
    }

    public function updateKmsGoods($id)
    {
        $data = $this->getPlatformGoodsData();
        $data['id'] = $id;
        (new KmsGoodsService())->updateKmsGoods($data);
        return ds_json_success('操作成功');
    }

    public function getKmsGoodsUpdateInfo($id)
    {
        $data = array('id' => $id);
        $result = (new KmsGoodsService())->getKmsGoodsUpdateInfo($data);
        return ds_json_success('操作成功', $result);
    }

    public function softDeleteKmsGoods()
    {
        $data = array('goods_ids' => input('param.goods_ids'));
        (new KmsGoodsService())->softDeleteKmsGoods($data);
        return ds_json_success('操作成功');
    }
}
