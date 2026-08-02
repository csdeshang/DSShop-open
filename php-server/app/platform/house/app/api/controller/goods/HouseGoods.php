<?php

namespace app\platform\house\app\api\controller\goods;

use app\deshang\base\controller\BaseApiController;


use app\platform\house\app\api\service\goods\HouseGoodsService;

// 获取House 房产商品详情
class HouseGoods extends BaseApiController
{

    public function getHouseGoodsDetail(){
        $data = array(
            'id' => input('param.goods_id'),
            'spec_id' => input('param.spec_id'),
        );
        $result = (new HouseGoodsService())->getHouseGoodsDetail($data);
        return ds_json_success('操作成功',$result);
    }

}


