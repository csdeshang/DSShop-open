<?php

namespace app\platform\food\app\api\service\goods;


use app\deshang\service\goods\DeshangTblGoodsDisplayService;
use app\deshang\base\service\BaseApiService;
use app\common\enum\goods\TblGoodsEnum;
use app\common\dao\goods\TblGoodsDao;



class FoodGoodsService extends BaseApiService
{

    public function __construct()
    {
        parent::__construct();
    }

    // 获取商城商品列表 [暂时不用，单独逻辑可使用]  当前使用通过 TblGoodsService
    public function getFoodGoodsList(array $data) : array
    {

    }

    public function getFoodGoodsDetail(array $data) : array
    {
        $data['platform'] = 'food';
        $data['user_id'] = $this->user_id ?? 0;
        $result =  (new DeshangTblGoodsDisplayService())->getTblGoodsDetail($data);
        return $result;
    }



}