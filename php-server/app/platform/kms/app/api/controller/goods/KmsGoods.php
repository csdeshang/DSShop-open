<?php

namespace app\platform\kms\app\api\controller\goods;

use app\deshang\base\controller\BaseApiController;


use app\platform\kms\app\api\service\goods\KmsGoodsService;

// 获取Kms 客满商品详情
class KmsGoods extends BaseApiController
{

    public function getKmsGoodsDetail(){
        $data = array(
            'id' => input('param.goods_id'),
            'spec_id' => input('param.spec_id'),
        );
        $result = (new KmsGoodsService())->getKmsGoodsDetail($data);
        return ds_json_success('操作成功',$result);
    }

}


