<?php

namespace app\platform\mall\app\adminapi\service\goods;

use app\adminapi\service\goods\BasePlatformGoodsService;

class MallGoodsService extends BasePlatformGoodsService
{
    public function __construct()
    {
        parent::__construct('mall');
    }

    public function getMallGoodsPages(array $data): array
    {
        return $this->getGoodsPages($data);
    }

    public function addMallGoods(array $data)
    {
        return $this->addGoods($data);
    }

    public function updateMallGoods(array $data)
    {
        return $this->updateGoods($data);
    }

    public function getMallGoodsUpdateInfo(array $data)
    {
        return $this->getGoodsUpdateInfo($data);
    }

    public function softDeleteMallGoods(array $data)
    {
        return $this->softDeleteGoods($data);
    }
}
