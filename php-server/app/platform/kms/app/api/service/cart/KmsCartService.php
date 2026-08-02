<?php

namespace app\platform\kms\app\api\service\cart;
use app\api\service\cart\BaseTblCartService;

class KmsCartService extends BaseTblCartService
{
    public function __construct()
    {
        parent::__construct('kms');
    }


    // 添加商品到购物车
    public function addKmsCart($data)
    {
        return $this->addTblCart($data);
    }


    // 获取购物车内容
    public function getKmsCartList(array $data = [])
    {
        return $this->getTblCartList($data);
    }


    // 更新购物车商品数量
    public function updateKmsCart($data)
    {
        return $this->updateTblCart($data);
    }

    // 删除购物车商品
    public function deleteKmsCart($cart_ids)
    {
        return $this->deleteTblCart($cart_ids); 
    }

    // 获取购物车总数
    public function getTotalQuantity()
    {
        return $this->getTblCartTotalQuantity();
    }
    
    /**
     * 添加商品到购物车后的钩子方法
     */
    protected function afterAddTblCart(&$result, $data)
    {
       
    }

}
