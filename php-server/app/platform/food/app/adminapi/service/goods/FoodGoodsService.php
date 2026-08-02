<?php

namespace app\platform\food\app\adminapi\service\goods;

use app\adminapi\service\goods\BasePlatformGoodsService;

class FoodGoodsService extends BasePlatformGoodsService
{
    public function __construct()
    {
        parent::__construct('food');
    }

    public function getFoodGoodsPages(array $data): array
    {
        return $this->getGoodsPages($data);
    }

    public function addFoodGoods(array $data)
    {
        return $this->addGoods($data);
    }

    public function updateFoodGoods(array $data)
    {
        return $this->updateGoods($data);
    }

    public function getFoodGoodsUpdateInfo(array $data)
    {
        return $this->getGoodsUpdateInfo($data);
    }

    public function softDeleteFoodGoods(array $data)
    {
        return $this->softDeleteGoods($data);
    }
}
