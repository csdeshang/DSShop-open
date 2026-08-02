<template>
    <view class="goods-detail-page">
        <!-- 轮播图 -->
        <GoodsSwiper :goods-detail="goodsDetail" />

        <!--- 商品基础信息 -->
        <GoodsInfoBase :goods-detail="goodsDetail" :selected-sku="selectedSku" />

        <!-- 商品参数 -->
        <GoodsParameters :goods-detail="goodsDetail" />

        <!-- 已选规格 -->
        <GoodsSkuSelected :goods-detail="goodsDetail" :selected-sku="selectedSku" @click="handleShowSku" />

        <!-- 课时 -->
        <GoodsKmsLesson :goods-detail="goodsDetail" />

        <!-- 店铺信息 -->
        <StoreCoupon :goods-detail="goodsDetail" />

        <!-- 商品评论 -->
        <GoodsComment :goods-detail="goodsDetail" />

        <!-- 商品详情 -->
        <GoodsBody :goods-detail="goodsDetail" />


        <!-- SKU选择弹窗 -->
        <GoodsSkuPopup ref="skuPopup" :goods-detail="goodsDetail" :selected-sku="selectedSku"
            @confirm="handleSkuConfirm" />

        <!-- 底部操作栏 -->
        <GoodsFooter :goods-detail="goodsDetail" @addToCart="handleAddToCart" @buyNow="handleBuyNow" />
    </view>
</template>



<script lang="ts" setup>
import { getTblGoodsDetail } from '@/home/api/tbl-goods/tblGoods'
import { onLoad } from '@dcloudio/uni-app';
import { reactive, ref } from 'vue';




// import GoodsSwiper from './component/goods-swiper.vue';
// import GoodsInfoBase from './component/goods-info-base.vue';
// import GoodsFooter from './component/goods-footer.vue';
// import GoodsSkuPopup from './component/goods-sku-popup.vue';
// import GoodsSkuSelected from './component/goods-sku-selected.vue';
// import StoreCoupon from './component/store-coupon.vue';
// import StoreInfo from './component/store-info.vue';
// import GoodsComment from './component/goods-comment.vue';
// import GoodsBody from './component/goods-body.vue';

// 此处使用公共组件， 如需单独样式 可移至当前目录
import GoodsSwiper from '@/home/components/tbl-goods/detail/component/goods-swiper.vue';
import GoodsInfoBase from '@/home/components/tbl-goods/detail/component/goods-info-base.vue';
import GoodsParameters from '@/home/components/tbl-goods/detail/component/goods-parameters.vue';
import GoodsFooter from '@/home/components/tbl-goods/detail/component/goods-footer.vue';
import GoodsSkuPopup from '@/home/components/tbl-goods/detail/component/goods-sku-popup.vue';
import GoodsSkuSelected from '@/home/components/tbl-goods/detail/component/goods-sku-selected.vue';
import StoreCoupon from '@/home/components/tbl-goods/detail/component/store-coupon.vue';
import GoodsComment from '@/home/components/tbl-goods/detail/component/goods-comment.vue';
import GoodsBody from '@/home/components/tbl-goods/detail/component/goods-body.vue';
import GoodsKmsLesson from '@/home/components/tbl-goods/detail/component/goods-kms-lesson.vue';


const loading = ref(false);
const goodsDetail = ref({});
const skuPopup = ref();

// 定义已选参数
const selectedSku = reactive<{
    sku_id: number | undefined;
    sku_name: string;
    sku_price: number;
    sku_stock: number;
    quantity: number;
    promotion_price?: number;
    promotion_type?: string;
    promotion_type_desc?: string;
}>({
    sku_id: undefined,
    sku_name: '',
    sku_price: 0,
    sku_stock: 0,
    quantity: 1
});

const loadGoodsDetail = async (params: any) => {
    loading.value = true;
    try {
        const res = await getTblGoodsDetail(params, 'kms');
        goodsDetail.value = res.data;

        // 将 defaultSku 赋值给 selectedSku
        if (res.data.defaultSku) {
            selectedSku.sku_id = res.data.defaultSku.id;
            selectedSku.sku_name = res.data.defaultSku.sku_name;
            selectedSku.sku_price = res.data.defaultSku.sku_price;
            selectedSku.sku_stock = res.data.defaultSku.sku_stock;
            selectedSku.quantity = 1; // 或者根据需要设置默认数量
            
            // 处理促销信息
            if (res.data.defaultSku.promotion_price && 
                res.data.defaultSku.promotion_price < res.data.defaultSku.sku_price) {
                selectedSku.promotion_price = res.data.defaultSku.promotion_price;
                selectedSku.promotion_type = res.data.defaultSku.promotion_type;
                
                // 获取促销类型描述
                if (res.data.defaultSku.promotions && 
                    res.data.defaultSku.promotion_type && 
                    res.data.defaultSku.promotions[res.data.defaultSku.promotion_type]) {
                    selectedSku.promotion_type_desc = 
                        res.data.defaultSku.promotions[res.data.defaultSku.promotion_type].promotion_type_desc || '';
                }
            }
        }
    } catch (error) {
        uni.showToast({
            title: '加载失败',
            icon: 'none'
        });
    } finally {
        loading.value = false;
    }
};

// 处理规格选择确认
const handleSkuConfirm = (data: any) => {
    selectedSku.sku_id = data.sku.id;
    selectedSku.sku_name = data.sku.sku_name;
    selectedSku.sku_price = data.sku.sku_price;
    selectedSku.sku_stock = data.sku.sku_stock;
    selectedSku.quantity = data.quantity;
    
    // 如果存在促销价格，同时保存促销价格信息
    if (data.sku.promotion_price && data.sku.promotion_price < data.sku.sku_price) {
        // @ts-ignore
        selectedSku.promotion_price = data.sku.promotion_price;
        // @ts-ignore
        selectedSku.promotion_type = data.sku.promotion_type;
        // @ts-ignore
        selectedSku.promotion_type_desc = data.sku.promotions?.[data.sku.promotion_type]?.promotion_type_desc || '';
    }
};

// 显示SKU选择
const handleShowSku = () => {
    // 只做 SKU选择弹窗 具体逻辑在组件中实现
    skuPopup.value?.open('show');
};

const handleAddToCart = () => {
    // 只做 SKU选择弹窗 具体逻辑在组件中实现
    skuPopup.value?.open('addToCart');
};

const handleBuyNow = () => {
    // 只做 SKU选择弹窗 具体逻辑在组件中实现
    skuPopup.value?.open('buyNow');
};



onLoad((options) => {
    const params = {
        goods_id: options.goods_id ? Number(options.goods_id) : undefined,
        spec_id: options.spec_id ? Number(options.spec_id) : undefined
    };
    loadGoodsDetail(params);
})


</script>

<style lang="scss" scoped>
.goods-detail-page {
    position: relative;
    min-height: 100vh;
    background-color: #f6f7f9;
}


</style>