<template>
    <view class="notice-detail">
        <view v-if="noticeInfo" class="notice-card">
            <view class="notice-title">{{ noticeInfo.title }}</view>
            <view class="notice-meta">
                <text class="notice-time">{{ noticeInfo.create_at }}</text>
            </view>
            <view class="notice-content">{{ noticeInfo.content }}</view>
        </view>
        
        <view v-else class="loading-state">
            <text>加载中...</text>
        </view>
    </view>
</template>

<script setup lang="ts">
import { getUserNoticeLogInfo } from '@/shared/api/userNotice'
import { onLoad } from '@dcloudio/uni-app'
import { ref } from 'vue'


// 定义通知数据类型接口
interface NoticeInfo {
    id: number
    user_id: number
    key: string
    notice_channel: string
    notice_channel_desc: string
    receiver: string
    title: string
    content: string
    is_read: number
    send_status: number
    send_params: any
    send_result: any
    create_at: string
    update_at: string
}

const noticeInfo = ref<NoticeInfo | null>(null)

const fetchUserNoticeLogInfo = async (id: string | number) => {
    try {
        uni.showLoading({ title: '加载中' })
        const result = await getUserNoticeLogInfo(Number(id))
        if (result.code === 10000) {
            noticeInfo.value = result.data
        } else {
            uni.showToast({ title: '获取通知详情失败', icon: 'none' })
        }
    } catch (error) {
        console.error('获取通知详情出错：', error)
        uni.showToast({ title: '获取通知详情出错', icon: 'none' })
    } finally {
        uni.hideLoading()
    }
}

onLoad((options) => {
    if (options?.id) {
        fetchUserNoticeLogInfo(options.id)
    } else {
        uni.showToast({ title: '参数错误', icon: 'none' })
        setTimeout(() => {
            uni.navigateBack()
        }, 1500)
    }
})
</script>

<style lang="scss" scoped>
.notice-detail {
    padding: 30rpx;
    min-height: 100vh;
    background-color: #ffffff;
    
    .notice-card {
        .notice-title {
            font-size: 36rpx;
            font-weight: 600;
            color: #333;
            margin-bottom: 20rpx;
            line-height: 1.4;
        }
        
        .notice-meta {
            margin-bottom: 40rpx;
            
            .notice-time {
                font-size: 24rpx;
                color: #999;
            }
        }
        
        .notice-content {
            font-size: 30rpx;
            color: #333;
            line-height: 1.8;
            padding-bottom: 50rpx;
        }
    }
    
    .loading-state {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200rpx;
        color: #999;
        font-size: 28rpx;
    }
}
</style>
