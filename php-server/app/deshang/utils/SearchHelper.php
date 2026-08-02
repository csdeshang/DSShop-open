<?php

namespace app\deshang\utils;

/**
 * 搜索辅助工具类
 * 
 * 提供通用的搜索功能，如模糊查询转ID列表等
 */
class SearchHelper
{
    /**
     * 根据用户名称模糊查询并返回ID列表
     * 
     * @param string $username 用户名称
     * @return array ID列表
     */
    public static function getUserIdsByUsername(string $username): array
    {
        if (empty($username)) {
            return [];
        }
        $userDao = new \app\common\dao\user\UserDao();
        return $userDao->getUserColumn([
            ['username', 'like', '%' . $username . '%']
        ], 'id');
    }

    /**
     * 根据商品名称模糊查询并返回ID列表
     * 
     * @param string $goodsName 商品名称
     * @return array ID列表
     */
    public static function getGoodsIdsByGoodsName(string $goodsName): array
    {
        if (empty($goodsName)) {
            return [];
        }
        
        $goodsDao = new \app\common\dao\goods\TblGoodsDao();
        return $goodsDao->getGoodsColumn([
            ['goods_name', 'like', '%' . $goodsName . '%']
        ], 'id');
    }

    /**
     * 根据订单编号模糊查询并返回订单ID列表
     * 
     * @param string $orderSn 订单编号
     * @return array 订单ID列表
     */
    public static function getOrderIdsByOrderSn(string $orderSn): array
    {
        if (empty($orderSn)) {
            return [];
        }
        
        $orderDao = new \app\common\dao\order\TblOrderDao();
        return $orderDao->getOrderColumn([
            ['order_sn', 'like', '%' . $orderSn . '%']
        ], 'id');
    }
}
