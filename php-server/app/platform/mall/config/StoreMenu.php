<?php

namespace app\platform\mall\config;

class StoreMenu
{
    // 动态路由信息
    private $menu_tree = [];

    public function __construct()
    {
        $this->initializeMenuTree();
    }




    
    private function initializeMenuTree()
    {
        // 初始化菜单树
        $this->menu_tree = array(
            array(
                'component' => '',
                'path' => 'store/order',
                'name' => 'store_order',
                'title' => '订单管理',
                'icon' => 'element Monitor',
                'is_show' => 1,
                'type' => 'directory',
                'children' => array(
                    array(
                        'component' => 'pages-store/platform/mall/views/order/order/index',
                        'path' => 'store/order/index',
                        'name' => 'store_order_index',
                        'title' => '订单列表',
                        'icon' => 'element Monitor',
                        'is_show' => 1,
                        'type' => 'menu'
                    ),
                    array(
                        'component' => 'pages-store/platform/mall/views/order/refund/index',
                        'path' => 'store/order/refund',
                        'name' => 'store_order_refund',
                        'title' => '退款订单',
                        'icon' => 'iconfont icon-unorderedlist',
                        'is_show' => 1,
                        'type' => 'menu'
                    ),
                    // 买家提交的开票申请，店铺处理（接口：storeapi/tbl-order/invoices/*）
                    array(
                        'component' => 'pages-store/platform/mall/views/order/invoice/index',
                        'path' => 'store/order/invoice',
                        'name' => 'store_order_invoice',
                        'title' => '开票申请',
                        'icon' => 'element Tickets',
                        'is_show' => 1,
                        'type' => 'menu'
                    )
                )
            ),
            array(
                'component' => '',
                'path' => 'store/marketing',
                'name' => 'store_maketing',
                'title' => '营销管理',
                'icon' => 'element Brush',
                'is_show' => 1,
                'type' => 'directory',
                'children' => array(
                    array(
                        'component' => 'pages-store/platform/mall/views/marketing/store-coupon/index',
                        'path' => 'store/marketing/coupon',
                        'name' => 'store_marketing_coupon',
                        'title' => '优惠券',
                        'icon' => 'element Box',
                        'is_show' => 1,
                        'type' => 'menu'
                    ),
                    array(
                        'component' => 'pages-store/platform/mall/views/marketing/goods-flashsale/index',
                        'path' => 'store/marketing/goods-flashsale',
                        'name' => 'store_marketing_goods_flashsale',
                        'title' => '限时折扣',
                        'icon' => 'element Burger',
                        'is_show' => 1,
                        'type' => 'menu'
                    ),
                    array(
                        'component' => 'pages-store/platform/mall/views/marketing/goods-wholesale/index',
                        'path' => 'store/marketing/goods-wholesale',
                        'name' => 'store_marketing_goods_wholesale',
                        'title' => '批发设置',
                        'icon' => 'element Burger',
                        'is_show' => 1,
                        'type' => 'menu'
                    ),
                    array(
                        'component' => 'pages-store/platform/mall/views/marketing/goods-userdiscount/index',
                        'path' => 'store/marketing/goods-userdiscount',
                        'name' => 'store_marketing_goods_userdiscount',
                        'title' => '平台会员折扣',
                        'icon' => 'element User',
                        'is_show' => 1,
                        'type' => 'menu'
                    )
                )
            ),
            array(
                'component' => '',
                'path' => 'store/distributor',
                'name' => 'store_distributor',
                'title' => '分销管理',
                'icon' => 'element Brush',
                'is_show' => 1,
                'type' => 'directory',
                'children' => array(
                    // 分销商品 使用 默认商品列表  查询 Tblgoods 数据
                    array(
                        'component' => 'pages-store/main/views/distributor/goods/index',
                        'path' => 'store/distributor/goods',
                        'name' => 'store_distributor_goods',
                        'title' => '分销商品',
                        'icon' => 'element Box',
                        'is_show' => 1,
                        'type' => 'menu'
                    ),
                    // 分销订单 使用 默认订单列表  查询 Tblorder 数据
                    array(
                        'component' => 'pages-store/main/views/distributor/order/index',
                        'path' => 'store/distributor/order',
                        'name' => 'store_distributor_order',
                        'title' => '分销订单',
                        'icon' => 'element Box',
                        'is_show' => 1,
                        'type' => 'menu'
                    ),
                )
            ),
        );
    }

    // 获取动态路由信息
    public function getStoreMenuTree()
    {
        return $this->menu_tree;
    }
}
