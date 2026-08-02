<?php

namespace app\adminapi\controller\traits;

trait PlatformGoodsControllerTrait
{
    protected function getPlatformGoodsData(): array
    {
        return array(
            'goods_name' => input('param.goods_name'),
            'goods_advword' => input('param.goods_advword'),
            'goods_body' => input('param.goods_body'),
            'goods_status' => input('param.goods_status'),
            'brand_id' => input('param.brand_id'),
            'goods_video' => input('param.goods_video'),
            'category_ids' => input('param.category_ids'),
            'slide_image' => input('param.slide_image'),
            'stock_num' => input('param.stock_num'),
            'virtual_sales_num' => input('param.virtual_sales_num'),
            'goods_sort' => input('param.goods_sort'),
            'goods_parameters' => input('param.goods_parameters'),
            'mall_express_type' => input('param.mall_express_type'),
            'mall_express_tpl_id' => input('param.mall_express_tpl_id'),
            'mall_express_fee' => input('param.mall_express_fee'),
            'spec_type' => input('param.spec_type'),
            'single' => input('param.single/a'),
            'goods_sku_list' => input('param.goods_sku_items/a'),
            'goods_spec_format' => input('param.goods_spec_format/a'),
        );
    }
}
