<template>
    <view class="goods-swiper" :style="{ height: windowWidth + 'px' }">
        <!-- 视频播放按钮 -->
        <view class="video-btn" v-if="hasVideo" @click="toggleVideoPlay">
            <uni-icons :type="isPlayingVideo ? 'image-filled' : 'videocam-filled'" color="#fff" size="30"></uni-icons>
        </view>
        
        <!-- 商品图片轮播 -->
        <view class="goods-images" v-show="!isPlayingVideo">
            <swiper :indicator-dots="true" :autoplay="true" :interval="3000" :duration="500" circular class="swiper">
                <swiper-item v-for="(item, index) in goodsDetail?.slide_image" :key="index">
                    <image :src="formatImageUrl(item, ThumbnailPresets.large, 'goods')" mode="aspectFill" class="swiper-img" />
                </swiper-item>
            </swiper>
        </view>
        
        <!-- 商品视频 -->
        <view class="goods-video" v-show="isPlayingVideo && hasVideo">
            <video 
                id="goods-video"
                :src="formatFileUrl(goodsDetail?.goods_video)" 
                :controls="true"
                :autoplay="isPlayingVideo"
                :loop="true"
                :show-play-btn="true"
                :enable-progress-gesture="true"
                object-fit="cover"
                class="video-player"
                @ended="onVideoEnded"
                @error="onVideoError"
            ></video>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { ref, computed, onUnmounted } from 'vue';
import { formatFileUrl } from '@/utils/util';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';

const props = defineProps({
    goodsDetail: {
        type: Object,
        default: () => ({})
    },
});

// 获取窗口宽度
const windowWidth = uni.getSystemInfoSync().windowWidth;

// 视频相关状态
const isPlayingVideo = ref(false);
const videoContext = ref<UniApp.VideoContext | null>(null);

// 判断是否有视频
const hasVideo = computed(() => {
    return !!props.goodsDetail?.goods_video;
});

// 切换视频播放状态
const toggleVideoPlay = () => {
    isPlayingVideo.value = !isPlayingVideo.value;
    
    if (isPlayingVideo.value) {
        // 延迟创建video上下文，确保DOM已渲染
        setTimeout(() => {
            videoContext.value = uni.createVideoContext('goods-video');
            videoContext.value?.play();
        }, 100);
    } else {
        videoContext.value?.pause();
    }
};

// 视频播放结束处理
const onVideoEnded = () => {
    if (isPlayingVideo.value) {
        // 可以选择自动重播或切回图片轮播
        // isPlayingVideo.value = false;
    }
};

// 视频加载错误处理
const onVideoError = (err: any) => {
    console.error('视频加载错误:', err);

    isPlayingVideo.value = false;
};

// 组件卸载时处理视频
onUnmounted(() => {
    if (videoContext.value) {
        videoContext.value.pause();
        videoContext.value = null;
    }
});
</script>

<style lang="scss" scoped>
.goods-swiper {
    position: relative;
    width: 100%;
    
    .goods-images {
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
    
    .goods-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #000;
        z-index: 2;
        
        .video-player {
            width: 100%;
            height: 100%;
        }
    }
    
    .video-btn {
        position: absolute;
        bottom: 30rpx;
        right: 30rpx;
        width: 80rpx;
        height: 80rpx;
        border-radius: 50%;
        background-color: rgba(0, 0, 0, 0.5);
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 3;
        transition: all 0.3s;
        
        .iconfont {
            font-size: 40rpx;
        }
        
        &:active {
            transform: scale(0.9);
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