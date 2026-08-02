<template>
    <view class="view-log-page">
        <!-- 主要内容 -->
        <view class="content-container">
            <z-paging ref="pagingRef" v-model="dataList" @query="queryList">

                <!-- 导航栏 -->
                <uni-nav-bar title="浏览记录" :border="false" background-color="#fff" color="#333" left-icon="left"
                    @clickLeft="goBack">
                    <template #right>
                        <view class="nav-right">
                            <view class="nav-btn clear-btn" @click="handleClearAll" v-if="dataList.length > 0">
                                <uni-icons type="trash" size="20" color="#ff6b6b" />
                            </view>
                        </view>
                    </template>
                </uni-nav-bar>

                <!-- 视频列表 -->
                <view class="video-list">
                    <view class="video-item" v-for="item in dataList" :key="item.id" @click="handleItemClick(item)">
                        <!-- 视频封面 -->
                        <view class="video-cover">
                            <image :src="formatImageUrl(item.videoShort?.cover_image || '', ThumbnailPresets.medium)"
                                mode="aspectFill" class="cover-image" />
                            <!-- 视频时长标识 -->
                            <view class="video-duration" v-if="item.videoShort?.type === 1">
                                <uni-icons type="videocam-filled" size="12" color="#fff" />
                            </view>
                        </view>

                        <!-- 视频信息 -->
                        <view class="video-info">
                            <view class="video-title">
                                {{ item.videoShort?.title || '视频标题' }}
                            </view>
                            <view class="video-desc">
                                {{ item.videoShort?.description || '暂无描述' }}
                            </view>
                            <view class="video-meta">
                                <text class="view-time">{{ formatViewTime(item.update_at) }}</text>
                            </view>
                        </view>
                    </view>
                </view>


            </z-paging>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getVideoViewLogPages, clearVideoViewLog } from '@/video/api/user/videoViewLog'

import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

// 数据项接口定义
interface VideoViewLogItem {
    id: number
    user_id: number
    content_type: string
    content_id: number
    blogger_id: number
    ip_address: string
    create_at: string
    update_at: string
    videoShort?: {
        id: number
        blogger_id: number
        type: number
        title: string
        description: string
        cover_image: string
    }
}

// z-paging 实例引用
const pagingRef = ref<any>(null)
// 列表数据
const dataList = ref<VideoViewLogItem[]>([])

// 分页查询方法
const queryList = async (page_current: number, page_size: number) => {
    try {
        const params = {
            page_current,
            page_size,
            content_type: 'short'
        }

        const result = await getVideoViewLogPages(params)

        if (result.code === 10000) {
            // 完成分页加载，传入当前页数据
            pagingRef.value?.complete(result.data.data)
        } else {
            // 加载失败
            pagingRef.value?.complete(false)
            uni.showToast({
                title: result.message || '加载失败',
                icon: 'none'
            })
        }
    } catch (error) {
        console.error('分页加载失败:', error)
        // 加载异常
        pagingRef.value?.complete(false)
        uni.showToast({
            title: '网络错误',
            icon: 'none'
        })
    }
}

// 返回上一页
const goBack = () => {
    uni.navigateBack()
}

// 处理项目点击
const handleItemClick = (item: VideoViewLogItem) => {
    // 跳转到视频详情页
    uni.navigateTo({
        url: `/video/pages/short/detail/index?id=${item.content_id}`
    })
}

// 清空所有记录
const handleClearAll = () => {
    uni.showModal({
        title: '提示',
        content: '确定要清空所有浏览记录吗？此操作不可恢复。',
        success: async (res) => {
            if (res.confirm) {
                try {
                    uni.showLoading({ title: '清空中...' })

                    const result = await clearVideoViewLog({
                        content_type: 'short'
                    })

                    if (result.code === 10000) {
                        uni.showToast({
                            title: '清空成功',
                            icon: 'success'
                        })
                        // 刷新列表
                        pagingRef.value?.reload()
                    } else {
                        uni.showToast({
                            title: result.message || '清空失败',
                            icon: 'none'
                        })
                    }
                } catch (error) {
                    console.error('清空记录失败:', error)
                    uni.showToast({
                        title: '网络错误',
                        icon: 'none'
                    })
                } finally {
                    uni.hideLoading()
                }
            }
        }
    })
}

// 格式化浏览时间
const formatViewTime = (timeString: string) => {
    try {
        // 处理字符串格式的时间 "2025-07-01 22:51:46"
        const date = new Date(timeString)
        const now = new Date()
        const diff = now.getTime() - date.getTime()

        const minute = 60 * 1000
        const hour = 60 * minute
        const day = 24 * hour
        const month = 30 * day

        if (diff < minute) {
            return '刚刚'
        } else if (diff < hour) {
            return `${Math.floor(diff / minute)}分钟前`
        } else if (diff < day) {
            return `${Math.floor(diff / hour)}小时前`
        } else if (diff < month) {
            return `${Math.floor(diff / day)}天前`
        } else {
            return date.toLocaleDateString()
        }
    } catch (error) {
        return timeString
    }
}

// 手动刷新方法
const refresh = () => {
    pagingRef.value?.reload()
}

// 暴露刷新方法
defineExpose({
    refresh
})
</script>

<style scoped lang="scss">
.view-log-page {
    min-height: 100vh;
    background: #f8f8f8;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 16rpx;

    .nav-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40rpx;
        height: 40rpx;
        border-radius: 8rpx;
        transition: all 0.2s ease;

        &:active {
            opacity: 0.6;
            background: rgba(0, 0, 0, 0.08);
            transform: scale(0.95);
        }
    }
}

.content-container {
    padding-top: 20rpx;
}

.video-list {
    margin-top: 20rpx;
    padding: 0 20rpx;
}

.video-item {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 12rpx;
    padding: 24rpx;
    margin-bottom: 20rpx;
    box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;

    &:active {
        opacity: 0.9;
        transform: scale(0.98);
    }

    .video-cover {
        width: 120rpx;
        height: 90rpx;
        border-radius: 8rpx;
        overflow: hidden;
        margin-right: 20rpx;
        position: relative;
        background: #f0f0f0;

        .cover-image {
            width: 100%;
            height: 100%;
        }

        .video-duration {
            position: absolute;
            top: 4rpx;
            right: 4rpx;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 4rpx;
            padding: 2rpx 4rpx;
            display: flex;
            align-items: center;
        }
    }

    .video-info {
        flex: 1;
        min-width: 0;

        .video-title {
            font-size: 30rpx;
            color: #333;
            font-weight: 500;
            line-height: 1.4;
            margin-bottom: 8rpx;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .video-desc {
            font-size: 26rpx;
            color: #666;
            line-height: 1.3;
            margin-bottom: 12rpx;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }

        .video-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;

            .view-time {
                font-size: 24rpx;
                color: #999;
            }
        }
    }
}

</style>