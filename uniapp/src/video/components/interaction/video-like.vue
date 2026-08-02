<template>
    <!-- 点赞组件 - 短视频/短剧 -->
    <view class="like-component" @click="handleLike">
        <uni-icons 
            :type="isLikedInternal ? 'heart-filled' : 'heart'" 
            :color="isLikedInternal ? '#ff2442' : (props.theme === 'dark' ? '#fff' : '#666')"
            :size="props.size"
        />
        <text class="like-count" :class="{ active: isLikedInternal, 'white-text': props.theme === 'dark' }">{{ currentLikeCount }}</text>
    </view>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { toggleVideoLike } from '@/video/api/user/videoLike'

// Props
const props = defineProps({
    contentId: {
        type: [String, Number],
        required: true
    },
    contentType: {
        type: String,
        default: 'short'
    },
    likeCount: {
        type: Number,
        default: 0
    },
    isLiked: {
        type: Boolean,
        default: false
    },
    theme: {
        type: String,
        default: 'dark', // dark: 深色主题(白色图标), light: 浅色主题(深色图标)
        validator: (value: string) => ['dark', 'light'].includes(value)
    },
    size: {
        type: [String, Number],
        default: 22
    }
})

// 响应式数据
const isLikedInternal = ref(props.isLiked)
const currentLikeCount = ref(props.likeCount)
const isLiking = ref(false)

// 监听外部传入的点赞数变化
watch(() => props.likeCount, (newCount) => {
    currentLikeCount.value = newCount
})

// 监听外部传入的点赞状态变化
watch(() => props.isLiked, (newLiked) => {
    isLikedInternal.value = newLiked
})

// 处理点赞
const handleLike = async () => {
    if (isLiking.value) return
    
    // 乐观更新UI
    const originalIsLiked = isLikedInternal.value
    const originalCount = currentLikeCount.value
    
    isLikedInternal.value = !originalIsLiked
    currentLikeCount.value = originalIsLiked ? originalCount - 1 : originalCount + 1
    isLiking.value = true
    
    try {
        // 调用真实的点赞API
        const result = await toggleVideoLike({
            content_id: props.contentId,
            content_type: props.contentType
        })

        if (result.code === 10000) {
            // API调用成功，使用返回的数据更新状态
            if (result.data) {
                isLikedInternal.value = result.data.is_liked !== undefined ? result.data.is_liked : isLikedInternal.value
                currentLikeCount.value = result.data.like_count !== undefined ? result.data.like_count : currentLikeCount.value
            }
            
            // 显示反馈
            uni.showToast({
                title: isLikedInternal.value ? '点赞成功' : '取消点赞',
                icon: 'none'
            })
        } else {
            throw new Error(result.message || '操作失败')
        }
        
    } catch (error: any) {
        console.error('点赞失败:', error)
        
        // 恢复原始状态
        isLikedInternal.value = originalIsLiked
        currentLikeCount.value = originalCount
        
        uni.showToast({
            title: error.message || '操作失败，请重试',
            icon: 'none'
        })
    } finally {
        isLiking.value = false
    }
}
</script>

<style scoped lang="scss">
.like-component {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8rpx;
    cursor: pointer;
    
    .like-count {
        font-size: 24rpx;
        color: #666;
        
        &.active {
            color: #ff2442;
        }
        
        &.white-text {
            color: #fff;
        }
    }
}
</style>

<script lang="ts">
export default {
    name: 'VideoLike'
}
</script>