<template>
    <view class="technician-swiper" :style="{ height: windowWidth + 'px' }" v-if="technicianDetail?.slide_image?.length > 0">
        <view class="technician-images">
            <swiper :indicator-dots="true" :autoplay="true" :interval="3000" :duration="500" circular class="swiper">
                <swiper-item v-for="(item, index) in technicianDetail?.slide_image" :key="index">
                    <image :src="formatImageUrl(item, ThumbnailPresets.large)" mode="aspectFill" class="swiper-img" />
                </swiper-item>
            </swiper>
        </view>
    </view>
</template>


<script setup lang="ts">
import { ref, computed } from 'vue';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';

const props = defineProps({
    technicianDetail: {
        type: Object,
        default: () => ({})
    },
});

// 获取窗口宽度
const windowWidth = uni.getSystemInfoSync().windowWidth;
</script>


<style lang="scss" scoped>
.technician-swiper {
    position: relative;
    width: 100%;
    
    .technician-images {
        height: 100%;
        width: 100%;
        position: relative;
        
        .swiper {
            width: 100%;
            height: 100%;
            
            .swiper-img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
        }
        
        &::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60rpx;
            background: linear-gradient(to top, rgba(0,0,0,0.05), transparent);
            z-index: 1;
        }
    }
}

/* 自定义指示点样式 */
:deep(.uni-swiper-dot) {
    width: 12rpx;
    height: 12rpx;
    border-radius: 6rpx;
    background: rgba(255, 255, 255, 0.6);
    transition: all 0.3s;
}

:deep(.uni-swiper-dot-active) {
    width: 24rpx;
    background: #FF6034;
}
</style>

