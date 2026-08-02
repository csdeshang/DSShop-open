<template>
    <view class="lesson-player">
        <!-- 返回按钮 -->
        <view class="back-button" @click="handleBack">
            <uni-icons type="left" size="24" color="#fff" />
        </view>
        
        <!-- 视频播放器 -->
        <template v-if="lessonDetail.lesson_type === 1 && lessonDetail.video_url">
            <video 
                :src="formatFileUrl(lessonDetail.video_url)"
                controls
                class="video-player"
                :poster="getVideoPoster()"
                autoplay
            />
        </template>
        
        <!-- 音频播放器 - 使用video组件替代audio组件 -->
        <template v-else-if="lessonDetail.lesson_type === 3 && lessonDetail.audio_url">
            <view class="audio-container">
                <view class="audio-cover">
                    <uni-icons type="headphones" size="60" color="#667eea" />
                </view>
                <video 
                    :src="formatFileUrl(lessonDetail.audio_url)"
                    controls
                    class="audio-player"
                    :show-center-play-btn="false"
                    :show-play-btn="true"
                    :show-fullscreen-btn="false"
                    :show-progress="true"
                    :enable-progress-gesture="true"
                />
            </view>
        </template>
        
        <!-- 图文内容预览 -->
        <template v-else-if="lessonDetail.lesson_type === 2 && lessonDetail.content">
            <view class="text-preview">
                <view class="text-header">
                    <uni-icons type="document" size="24" color="#667eea" />
                    <text class="text-title">图文内容</text>
                </view>
                <rich-text 
                    :nodes="formatRichText(lessonDetail.content)"
                    class="text-content"
                />
            </view>
        </template>
        
        <!-- 默认状态 -->
        <template v-else>
            <view class="default-content">
                <view class="default-icon">
                    <uni-icons type="videocam" size="60" color="#ccc" />
                </view>
                <text class="default-text">请选择课时查看内容</text>
            </view>
        </template>
    </view>
</template>

<script setup lang="ts">
import { formatFileUrl, formatRichText } from '@/utils/util';




const props = defineProps({
    lessonDetail: {
        type: Object,
        default: () => ({})
    }
});

const getVideoPoster = (): string => {
    return props.lessonDetail.video_url || '/static/images/default-video-poster.png';
};

const handleBack = () => {
    uni.navigateBack();
};
</script>

<style lang="scss" scoped>
.lesson-player {
    position: relative;
    background: #000;
    min-height: 400rpx;
    
    .back-button {
        position: absolute;
        top: 60rpx;
        left: 30rpx;
        width: 80rpx;
        height: 80rpx;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    
    .video-player {
        width: 100%;
        height: 400rpx;
    }
    
    .audio-container {
        padding: 60rpx 40rpx;
        text-align: center;
        
        .audio-cover {
            margin-bottom: 40rpx;
        }
        
        .audio-player {
            width: 100%;
            height: 120rpx; /* 音频播放器高度 */
        }
    }
    
    .text-preview {
        padding: 60rpx 40rpx;
        color: #fff;
        
        .text-header {
            display: flex;
            align-items: center;
            margin-bottom: 30rpx;
            
            .text-title {
                margin-left: 20rpx;
                font-size: 32rpx;
                font-weight: bold;
            }
        }
        
        .text-content {
            line-height: 1.8;
            font-size: 28rpx;
            max-height: 300rpx;
            overflow: hidden;
        }
    }
    
    .default-content {
        padding: 120rpx 40rpx;
        text-align: center;
        color: #fff;
        
        .default-icon {
            margin-bottom: 30rpx;
        }
        
        .default-text {
            font-size: 28rpx;
            color: #ccc;
        }
    }
}
</style> 