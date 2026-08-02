<template>
    <view>
        <z-paging ref="paging" v-model="dataList" @query="queryList">
            <view class="notice-list">
                <view class="notice-item" v-for="item in dataList" :key="item.id" @click="goNoticeDetail(item)">
                    <view class="item-header">
                        <view class="type-badge" :class="'channel-' + item.notice_channel">
                            {{ item.notice_channel_desc }}
                        </view>
                        <view class="read-status" :class="item.is_read ? 'read' : 'unread'">
                            {{ item.is_read ? '已读' : '未读' }}
                        </view>
                    </view>

                    <view class="item-body">
                        <view class="title-row">
                            <text class="title">{{ item.title }}</text>
                        </view>

                        <view class="content-row">
                            <text class="content">{{ item.content }}</text>
                        </view>

                        <view class="time-row">
                            <text class="time">{{ item.create_at }}</text>
                        </view>
                    </view>
                </view>
            </view>
        </z-paging>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { onLoad, onShow } from '@dcloudio/uni-app'
import { getUserNoticeLogPages } from '@/shared/api/userNotice'

// 定义通知数据类型接口
interface NoticeItem {
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

const paging = ref<any>(null)
const dataList = ref<NoticeItem[]>([])



onShow(() => {
    // 刷新数据到第一页
    paging.value?.reload()
})

const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getUserNoticeLogPages({
            page_current,
            page_size,
        })
        paging.value?.complete(result.data.data)
    } catch (e) {
        console.log('下拉加载', e)
        paging.value?.complete(false)
    }
}

const goNoticeDetail = (item: NoticeItem) => {
    uni.navigateTo({
        url: `/shared/pages/system/notice/detail?id=${item.id}`
    })
}

</script>

<style lang="scss" scoped>
.notice-list {
    padding: 20rpx;

    .notice-item {
        background-color: #ffffff;
        border-radius: 12rpx;
        margin-bottom: 20rpx;
        padding: 24rpx;
        box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16rpx;

            .type-badge {
                font-size: 24rpx;
                padding: 6rpx 12rpx;
                border-radius: 6rpx;
                background-color: #e6f7ff;
                color: #1890ff;

                &.channel-internal {
                    background-color: #e6f7ff;
                    color: #1890ff;
                }
            }

            .read-status {
                font-size: 24rpx;

                &.unread {
                    color: #f5222d;
                    font-weight: bold;
                }

                &.read {
                    color: #8c8c8c;
                }
            }
        }

        .item-body {
            .title-row {
                margin-bottom: 12rpx;

                .title {
                    font-size: 30rpx;
                    font-weight: 600;
                    color: #262626;
                }
            }

            .content-row {
                margin-bottom: 16rpx;

                .content {
                    font-size: 28rpx;
                    color: #595959;
                    line-height: 1.5;
                }
            }

            .time-row {
                .time {
                    font-size: 24rpx;
                    color: #8c8c8c;
                }
            }
        }
    }
}
</style>
