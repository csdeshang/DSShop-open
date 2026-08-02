<?php

namespace app\platform\kms\app\adminapi\service\goods;

use app\adminapi\service\goods\BasePlatformGoodsService;

class KmsGoodsService extends BasePlatformGoodsService
{
    public function __construct()
    {
        parent::__construct('kms');
    }

    public function getKmsGoodsPages(array $data): array
    {
        return $this->getGoodsPages($data);
    }

    public function addKmsGoods(array $data)
    {
        return $this->addGoods($data);
    }

    public function updateKmsGoods(array $data)
    {
        return $this->updateGoods($data);
    }

    public function getKmsGoodsUpdateInfo(array $data)
    {
        return $this->getGoodsUpdateInfo($data);
    }

    public function softDeleteKmsGoods(array $data)
    {
        return $this->softDeleteGoods($data);
    }
}
