<?php

namespace app\platform\food\app\api\controller\goods;

use app\deshang\base\controller\BaseApiController;


use app\platform\food\app\api\service\goods\FoodGoodsService;

// 获取Food 美食商品详情
class FoodGoods extends BaseApiController
{

    public function getFoodGoodsDetail(){
        $data = array(
            'id' => input('param.goods_id'),
            'spec_id' => input('param.spec_id'),
        );
        $result = (new FoodGoodsService())->getFoodGoodsDetail($data);
        return ds_json_success('操作成功',$result);
    }

}


