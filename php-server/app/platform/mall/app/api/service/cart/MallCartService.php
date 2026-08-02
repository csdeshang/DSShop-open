<?php

namespace app\platform\mall\app\api\service\cart;
use app\api\service\cart\BaseTblCartService;

class MallCartService extends BaseTblCartService
{
    public function __construct()
    {
        parent::__construct('mall');
    }


    // 添加商品到购物车
    public function addMallCart($data)
    {
        return $this->addTblCart($data);
    }


    // 获取购物车内容
    public function getMallCartList(array $data = [])
    {
        return $this->getTblCartList($data);
    }


    // 更新购物车商品数量
    public function updateMallCart($data)
    {
        return $this->updateTblCart($data);
    }

    // 删除购物车商品
    public function deleteMallCart($cart_ids)
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
