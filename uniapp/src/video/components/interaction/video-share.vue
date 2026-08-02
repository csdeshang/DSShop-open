<template>
    <!-- 分享组件 - 短视频/短剧 -->
    <view class="share-component" @click="handleShare">
        <uni-icons 
            type="redo" 
            :color="props.theme === 'dark' ? '#fff' : '#666'"
            :size="props.size"
        />
        <text class="share-text" :class="{ 'white-text': props.theme === 'dark' }">分享</text>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'

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
    shareTitle: {
        type: String,
        default: '精彩内容分享'
    },
    shareDesc: {
        type: String,
        default: ''
    },
    shareImage: {
        type: String,
        default: ''
    },
    theme: {
        type: String,
        default: 'light', // dark: 深色主题(白色图标), light: 浅色主题(深色图标)
        validator: (value: string) => ['dark', 'light'].includes(value)
    },
    size: {
        type: [String, Number],
        default: 20
    }
})

// 响应式数据
const isSharing = ref(false)

// 处理分享
const handleShare = async () => {
    if (isSharing.value) return
    
    isSharing.value = true
    
    try {
        // 分享到微信朋友圈和微信好友
        const shareOptions = {
            provider: 'weixin',
            type: 0, // 图文
            title: props.shareTitle,
            summary: props.shareDesc,
            imageUrl: props.shareImage
        }
        
        // 显示分享菜单
        uni.showActionSheet({
            itemList: ['分享到微信好友', '分享到朋友圈', '复制链接'],
            success: async (res) => {
                const tapIndex = res.tapIndex
                
                switch (tapIndex) {
                    case 0:
                        // 分享到微信好友
                        await shareToWeChat('WXSceneSession')
                        break
                    case 1:
                        // 分享到朋友圈
                        await shareToWeChat('WXSceneTimeline')
                        break
                    case 2:
                        // 复制链接
                        await copyShareLink()
                        break
                }
            }
        })
        
    } catch (error: any) {
        console.error('分享失败:', error)
        uni.showToast({
            title: '分享失败，请重试',
            icon: 'none'
        })
    } finally {
        isSharing.value = false
    }
}

// 分享到微信
const shareToWeChat = async (scene: string) => {
    try {
        // #ifdef APP-PLUS
        // 调用微信分享 - 简化版本避免类型错误
        const shareObj: any = {
            provider: 'weixin',
            scene: scene,
            type: 0,
            title: props.shareTitle,
            summary: props.shareDesc,
            imageUrl: props.shareImage,
            href: generateShareUrl()
        }
        
        uni.share(shareObj)
        // #endif
        
        // #ifdef H5 || MP-WEIXIN
        // H5或小程序环境的分享逻辑
        uni.showToast({
            title: '请使用右上角分享功能',
            icon: 'none'
        })
        // #endif
        
    } catch (error: any) {
        console.error('微信分享失败:', error)
        uni.showToast({
            title: '分享失败',
            icon: 'none'
        })
    }
}

// 复制分享链接
const copyShareLink = async () => {
    try {
        const shareUrl = generateShareUrl()
        
        uni.setClipboardData({
            data: shareUrl,
            success: () => {
                uni.showToast({
                    title: '链接已复制',
                    icon: 'success'
                })
            }
        })
        
    } catch (error: any) {
        console.error('复制链接失败:', error)
        uni.showToast({
            title: '复制失败',
            icon: 'none'
        })
    }
}

// 生成分享链接
const generateShareUrl = () => {
    // TODO: 根据实际需求生成分享链接
    const baseUrl = 'https://your-domain.com'
    const shareUrl = `${baseUrl}/video/pages/short/detail/image?id=${props.contentId}`
    return shareUrl
}
</script>

<style scoped lang="scss">
.share-component {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8rpx;
    cursor: pointer;
    
    .share-text {
        font-size: 24rpx;
        color: #666;
        
        &.white-text {
            color: #fff;
        }
    }
}
</style>

<script lang="ts">
export default {
    name: 'VideoShare'
}
</script>
