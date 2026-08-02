<?php

namespace app\platform\mall\app\api\controller\goods;

use app\deshang\base\controller\BaseApiController;


use app\platform\mall\app\api\service\goods\MallGoodsService;

// 获取Mall 商城商品详情
class MallGoods extends BaseApiController
{

    public function getMallGoodsDetail(){
        $data = array(
            'id' => input('param.goods_id'),
            'spec_id' => input('param.spec_id'),
        );
        $result = (new MallGoodsService())->getMallGoodsDetail($data);
        return ds_json_success('操作成功',$result);
    }

}


