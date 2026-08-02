<template>
    <!-- 收藏组件 - 短视频/短剧 -->
    <view class="collect-component" @click="handleCollect">
        <uni-icons 
            :type="isCollectedInternal ? 'star-filled' : 'star'" 
            :color="isCollectedInternal ? '#ffaa00' : (props.theme === 'dark' ? '#fff' : '#666')"
            :size="props.size"
        />
        <text class="collect-count" :class="{ active: isCollectedInternal, 'white-text': props.theme === 'dark' }">{{ currentCollectCount }}</text>
    </view>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { toggleVideoCollect } from '@/video/api/user/videoCollect'

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
    collectCount: {
        type: Number,
        default: 0
    },
    isCollected: {
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
const isCollectedInternal = ref(props.isCollected)
const currentCollectCount = ref(props.collectCount)
const isCollecting = ref(false)

// 监听外部传入的收藏数变化
watch(() => props.collectCount, (newCount) => {
    currentCollectCount.value = newCount
})

// 监听外部传入的收藏状态变化
watch(() => props.isCollected, (newCollected) => {
    isCollectedInternal.value = newCollected
})

// 处理收藏
const handleCollect = async () => {
    if (isCollecting.value) return
    
    // 乐观更新UI
    const originalIsCollected = isCollectedInternal.value
    const originalCount = currentCollectCount.value
    
    isCollectedInternal.value = !originalIsCollected
    currentCollectCount.value = originalIsCollected ? originalCount - 1 : originalCount + 1
    isCollecting.value = true
    
    try {
        // 调用真实的收藏API
        const result = await toggleVideoCollect({
            content_id: props.contentId,
            content_type: props.contentType
        })

        if (result.code === 10000) {
            // API调用成功，使用返回的数据更新状态
            if (result.data) {
                isCollectedInternal.value = result.data.is_collected !== undefined ? result.data.is_collected : isCollectedInternal.value
                currentCollectCount.value = result.data.collect_count !== undefined ? result.data.collect_count : currentCollectCount.value
            }
            
            // 显示反馈
            uni.showToast({
                title: isCollectedInternal.value ? '收藏成功' : '取消收藏',
                icon: 'none'
            })
        } else {
            throw new Error(result.message || '操作失败')
        }
        
    } catch (error: any) {
        console.error('收藏失败:', error)
        
        // 恢复原始状态
        isCollectedInternal.value = originalIsCollected
        currentCollectCount.value = originalCount
        
        uni.showToast({
            title: error.message || '操作失败，请重试',
            icon: 'none'
        })
    } finally {
        isCollecting.value = false
    }
}
</script>

<style scoped lang="scss">
.collect-component {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8rpx;
    cursor: pointer;
    
    .collect-count {
        font-size: 24rpx;
        color: #666;
        
        &.active {
            color: #ffaa00;
        }
        
        &.white-text {
            color: #fff;
        }
    }
}
</style>

<script lang="ts">
export default {
    name: 'VideoCollect'
}
</script>
