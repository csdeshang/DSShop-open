<template>
    <!-- 博主关注组件 -->
    <button 
        class="follow-btn" 
        :class="{ 
            'followed': isFollowedInternal,
            'light-theme': theme === 'light',
            'dark-theme': theme === 'dark'
        }" 
        @click="handleFollow"
        :disabled="isFollowing"
    >
        {{ isFollowedInternal ? '已关注' : '关注' }}
    </button>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { toggleBloggerFollow } from '@/video/api/user/bloggerFollow'

// Props
const props = defineProps({
    bloggerId: {
        type: [String, Number],
        required: true
    },
    isFollowed: {
        type: Boolean,
        default: false
    },
    theme: {
        type: String,
        default: 'light', // light: 浅色主题, dark: 深色主题
        validator: (value: string) => ['light', 'dark'].includes(value)
    },
    size: {
        type: String,
        default: 'normal', // normal, small, large
        validator: (value: string) => ['small', 'normal', 'large'].includes(value)
    }
})

// 响应式数据
const isFollowedInternal = ref(props.isFollowed)
const isFollowing = ref(false)

// 监听外部传入的关注状态变化
watch(() => props.isFollowed, (newFollowed) => {
    isFollowedInternal.value = newFollowed
})

// 处理关注
const handleFollow = async () => {
    if (isFollowing.value) return
    
    // 乐观更新UI
    const originalIsFollowed = isFollowedInternal.value
    isFollowedInternal.value = !originalIsFollowed
    isFollowing.value = true
    
    try {
        // 调用真实的关注API
        const result = await toggleBloggerFollow(Number(props.bloggerId))

        if (result.code === 10000) {
            // API调用成功，使用返回的数据更新状态
            if (result.data && result.data.is_followed !== undefined) {
                isFollowedInternal.value = result.data.is_followed
            }
            
            // 显示反馈
            uni.showToast({
                title: isFollowedInternal.value ? '关注成功' : '取消关注',
                icon: 'none'
            })
        } else {
            throw new Error(result.message || '操作失败')
        }
        
    } catch (error: any) {
        console.error('关注操作失败:', error)
        
        // 恢复原始状态
        isFollowedInternal.value = originalIsFollowed
        
        uni.showToast({
            title: error.message || '操作失败，请重试',
            icon: 'none'
        })
    } finally {
        isFollowing.value = false
    }
}
</script>

<style scoped lang="scss">
.follow-btn {
    border: none;
    border-radius: 40rpx;
    padding: 12rpx 32rpx;
    font-size: 26rpx;
    cursor: pointer;
    transition: all 0.3s ease;
    
    &:disabled {
        opacity: 0.6;
    }
    
    // 浅色主题 (默认)
    &.light-theme {
        background: #ff2442;
        color: #fff;
        
        &.followed {
            background: #f0f0f0;
            color: #666;
        }
        
        &:active {
            opacity: 0.8;
        }
    }
    
    // 深色主题
    &.dark-theme {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        border: 1rpx solid rgba(255, 255, 255, 0.3);
        
        &.followed {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }
        
        &:active {
            background: rgba(255, 255, 255, 0.3);
        }
    }
    
    // 尺寸变体
    &.small {
        padding: 8rpx 20rpx;
        font-size: 22rpx;
        border-radius: 30rpx;
    }
    
    &.large {
        padding: 16rpx 40rpx;
        font-size: 30rpx;
        border-radius: 50rpx;
    }
    
    // 移除默认按钮样式
    &::after {
        border: none;
    }
}
</style>

<script lang="ts">
export default {
    name: 'BloggerFollow'
}
</script>
