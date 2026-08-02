<template>
    <view class="t echnician-detail-page">
        <!-- 轮播图 -->
        <TechnicianSwiper :technician-detail="technicianDetail" />


        <!-- 师傅基础信息 -->
        <TechnicianInfo :technician-detail="technicianDetail" />

        <!-- 师傅商品SKU列表 -->
        <TechnicianGoodsSku :technician-detail="technicianDetail" />

        <!-- 师傅评价 -->
        <TechnicianComment :technician-detail="technicianDetail" />

        <!-- 安全区域 -->
        <view class="safe-area-inset-bottom"></view>
    </view>
</template>



<script setup lang="ts">
import { onLoad } from "@dcloudio/uni-app";
import { ref } from "vue";

import { getTechnicianDetail } from '@/home/api/technician/technician'

import TechnicianSwiper from './detail-swiper.vue';
import TechnicianInfo from './detail-info.vue';
import TechnicianGoodsSku from './detail-goods-sku.vue';
import TechnicianComment from './detail-comment.vue';



const loading = ref(false);
const technicianDetail = ref<any>({});




const loadTechnicianDetail = async (params: any) => {
    loading.value = true;
    try {
        const res = await getTechnicianDetail(params);
        technicianDetail.value = res.data;
    } catch (error) {
        uni.showToast({
            title: '加载失败',
            icon: 'none'
        });
    } finally {
        loading.value = false;
    }
};

onLoad((options) => {
    const params = {
        id: options?.id ? Number(options.id) : undefined,
    };
    loadTechnicianDetail(params);
})

</script>



<style lang="scss" scoped>
.technician-detail-page {
    position: relative;
    min-height: 100vh;
    background-color: #f6f7f9;
    padding-bottom: calc(100rpx + env(safe-area-inset-bottom));
}

.safe-area-inset-bottom {
    height: 50rpx;
}
</style>