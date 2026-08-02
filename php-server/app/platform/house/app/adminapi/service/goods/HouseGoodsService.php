<?php

namespace app\platform\house\app\adminapi\service\goods;

use app\adminapi\service\goods\BasePlatformGoodsService;

class HouseGoodsService extends BasePlatformGoodsService
{
    public function __construct()
    {
        parent::__construct('house');
    }

    public function getHouseGoodsPages(array $data): array
    {
        return $this->getGoodsPages($data);
    }

    public function addHouseGoods(array $data)
    {
        return $this->addGoods($data);
    }

    public function updateHouseGoods(array $data)
    {
        return $this->updateGoods($data);
    }

    public function getHouseGoodsUpdateInfo(array $data)
    {
        return $this->getGoodsUpdateInfo($data);
    }

    public function softDeleteHouseGoods(array $data)
    {
        return $this->softDeleteGoods($data);
    }
}
