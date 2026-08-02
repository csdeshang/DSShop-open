<template>
    <view v-if="visible" class="video-preview-modal" @touchmove.stop.prevent="">
        <!-- 背景遮罩 -->
        <view class="modal-overlay" @click="close"></view>

        <!-- 视频播放器容器 -->
        <view class="video-container">
            <!-- 头部信息栏 -->
            <view class="video-header">
                <text class="video-title">{{ videoData.title || '视频预览' }}</text>
                <view class="close-btn" @click="close">
                    <uni-icons type="close" size="22" color="#fff"></uni-icons>
                </view>
            </view>

            <!-- 视频播放器 -->
            <video id="preview-video-player" :src="videoData.url" :poster="videoData.poster" :controls="true"
                :autoplay="true" :show-play-btn="true" :show-fullscreen-btn="true" :enable-progress-gesture="true"
                :object-fit="'contain'" class="video-player" @error="onVideoError" @play="onVideoPlay"
                @pause="onVideoPause"></video>

            <!-- 底部操作栏（可选） -->
            <view class="video-actions" v-if="showActions">
                <view class="action-btn" @click="handleDownload">
                    <uni-icons type="download" size="20" color="#fff"></uni-icons>
                    <text>下载</text>
                </view>
                <view class="action-btn" @click="handleShare">
                    <uni-icons type="redo" size="20" color="#fff"></uni-icons>
                    <text>分享</text>
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

interface VideoData {
    url: string
    title?: string
    poster?: string
}

interface Props {
    showActions?: boolean // 是否显示操作按钮
}

const props = withDefaults(defineProps<Props>(), {
    showActions: false
})

const emit = defineEmits(['close', 'download', 'share'])

// 响应式数据
const visible = ref(false)
const videoData = ref<VideoData>({ url: '' })
const videoContext = ref<UniApp.VideoContext | null>(null)
const isPlaying = ref(false)

// 显示视频预览
const show = (data: VideoData) => {
    videoData.value = { ...data }
    visible.value = true

    // 延迟创建video上下文
    setTimeout(() => {
        try {
            videoContext.value = uni.createVideoContext('preview-video-player')
        } catch (error) {
            console.error('创建视频上下文失败:', error)
        }
    }, 150)
}

// 关闭视频预览
const close = () => {
    // 先暂停视频
    if (videoContext.value && isPlaying.value) {
        try {
            videoContext.value.pause()
        } catch (error) {
            console.error('暂停视频失败:', error)
        }
    }

    visible.value = false
    isPlaying.value = false

    // 延迟清理，确保动画完成
    setTimeout(() => {
        videoData.value = { url: '' }
        videoContext.value = null
    }, 300)

    emit('close')
}

// 视频播放事件
const onVideoPlay = () => {
    isPlaying.value = true
}

// 视频暂停事件
const onVideoPause = () => {
    isPlaying.value = false
}

// 视频错误处理
const onVideoError = (e: any) => {
    console.error('视频播放失败:', e)
    uni.showToast({
        title: '视频播放失败',
        icon: 'none'
    })
}

// 下载处理
const handleDownload = () => {
    emit('download', videoData.value)
}

// 分享处理
const handleShare = () => {
    emit('share', videoData.value)
}

// 监听visible变化，控制状态栏
watch(visible, (newVisible) => {
    if (newVisible) {
        // 可以在这里添加一些显示时的逻辑
        uni.setNavigationBarColor({
            frontColor: '#ffffff',
            backgroundColor: '#000000'
        }).catch(() => {
            // 某些平台可能不支持，忽略错误
        })
    } else {
        // 恢复状态栏
        uni.setNavigationBarColor({
            frontColor: '#000000',
            backgroundColor: '#ffffff'
        }).catch(() => {
            // 某些平台可能不支持，忽略错误
        })
    }
})

// 暴露方法给外部调用
defineExpose({
    show,
    close
})
</script>

<style lang="scss" scoped>
.video-preview-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease-out;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(4rpx);
}

.video-container {
    position: relative;
    width: 90%;
    max-width: 800rpx;
    background: #000;
    border-radius: 20rpx;
    overflow: hidden;
    box-shadow: 0 20rpx 60rpx rgba(0, 0, 0, 0.3);
    animation: slideIn 0.3s ease-out;
}

.video-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24rpx 30rpx;
    background: linear-gradient(180deg, rgba(0, 0, 0, 0.7) 0%, transparent 100%);
    position: relative;
    z-index: 10;

    .video-title {
        color: #fff;
        font-size: 32rpx;
        font-weight: 500;
        flex: 1;
        margin-right: 20rpx;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .close-btn {
        width: 60rpx;
        height: 60rpx;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;

        &:active {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(0.95);
        }
    }
}

.video-player {
    width: 100%;
    min-height: 400rpx;
    max-height: 70vh;
    background: #000;
}

.video-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 60rpx;
    padding: 30rpx;
    background: linear-gradient(0deg, rgba(0, 0, 0, 0.7) 0%, transparent 100%);

    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10rpx;
        padding: 20rpx;
        border-radius: 12rpx;
        background: rgba(255, 255, 255, 0.1);
        transition: all 0.2s ease;

        text {
            color: #fff;
            font-size: 24rpx;
        }

        &:active {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(0.95);
        }
    }
}

// 动画
@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: scale(0.8) translateY(100rpx);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>