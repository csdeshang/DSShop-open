<template>
    <view class="points-goods-swiper">
        <swiper 
            class="swiper" 
            :indicator-dots="true" 
            :autoplay="false" 
            :circular="true"
            :indicator-color="'rgba(255, 255, 255, 0.5)'"
            :indicator-active-color="'#E1251B'"
        >
            <swiper-item v-for="(image, index) in slideImages" :key="index">
                <image 
                    class="swiper-image" 
                    :src="formatImageUrl(image, ThumbnailPresets.large, 'goods')" 
                    mode="aspectFill"
                    @click="previewImage(index)"
                />
            </swiper-item>
        </swiper>
    </view>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

const props = defineProps<{
    goodsDetail: any
}>()

// 处理轮播图数据
const slideImages = computed(() => {
    if (!props.goodsDetail?.slide_image) return []
    
    if (Array.isArray(props.goodsDetail.slide_image)) {
        return props.goodsDetail.slide_image
    }
    
    if (typeof props.goodsDetail.slide_image === 'string') {
        return props.goodsDetail.slide_image.split(',').filter(Boolean)
    }
    
    return []
})

// 预览图片
const previewImage = (current: number) => {
    const urls = slideImages.value.map((img: string) => formatImageUrl(img, ThumbnailPresets.original))
    uni.previewImage({
        current,
        urls
    })
}
</script>

<style lang="scss" scoped>
.points-goods-swiper {
    width: 100%;
    height: 750rpx;
    background-color: #f5f5f5;
    
    .swiper {
        width: 100%;
        height: 100%;
        
        .swiper-image {
            width: 100%;
            height: 100%;
        }
    }
}
</style>
