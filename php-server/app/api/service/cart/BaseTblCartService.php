<?php

namespace app\api\service\cart;

use app\deshang\base\service\BaseUserService;
use app\deshang\service\cart\DeshangTblCartService;

/**
 * 购物车服务基类
 */
abstract class BaseTblCartService extends BaseUserService
{
    protected $platform;

    public function __construct($platform)
    {
        parent::__construct();
        $this->platform = $platform;
    }

    /**
     * 添加商品到购物车
     */
    public function addTblCart($data)
    {
        $data['user_id'] = $this->user_id;
        $data['platform'] = $this->platform;

        // 调用核心服务
        $result = (new DeshangTblCartService())->addTblCart($data);

        // 钩子方法，处理平台特定逻辑
        $this->afterAddTblCart($result, $data);

        return $result;
    }


    /**
     * 获取购物车列表（单店铺扁平结构）
     */
    public function getTblCartList($data = [])
    {
        $userId = $this->user_id;
        $platform = $this->platform;
        $cartIds = $data['cart_ids'] ?? [];

        // 调用核心服务
        $result = (new DeshangTblCartService())->getTblCartList($userId, $platform, $cartIds);

        // 钩子方法，处理平台特定逻辑
        $this->afterGetTblCartList($result, $data);

        return $result;
    }
    /**
     * 更新购物车商品数量
     */
    public function updateTblCart($data)
    {
        $data['user_id'] = $this->user_id;
        $data['platform'] = $this->platform;

        // 调用核心服务
        $result = (new DeshangTblCartService())->updateTblCart($data);

        // 钩子方法，处理平台特定逻辑
        $this->afterUpdateTblCart($result, $data);

        return $result;
    }

    /**
     * 删除购物车商品
     */
    public function deleteTblCart($cart_ids)
    {
        $data = [
            'user_id' => $this->user_id,
            'platform' => $this->platform,
            'cart_ids' => $cart_ids
        ];

        // 调用核心服务
        $result = (new DeshangTblCartService())->deleteTblCart($data);

        // 钩子方法，处理平台特定逻辑
        $this->afterDeleteTblCart($result, $data);

        return $result;
    }
    /**
     * 获取购物车总数
     */
    public function getTblCartTotalQuantity()
    {
        return (new DeshangTblCartService())->getTblCartTotalQuantity($this->user_id);
    }

    // 钩子方法，由子类实现
    protected function afterAddTblCart(&$result, $data) {}
    protected function afterGetTblCartList(&$result, $data) {}
    protected function afterUpdateTblCart(&$result, $data) {}
    protected function afterDeleteTblCart(&$result, $data) {}
}
