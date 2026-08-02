<template>
    <view class="video-list-page">


        <!-- 短视频列表 -->
        <z-paging ref="pagingRef" v-model="videoList" @query="queryVideoList">
            <template #top>
                <!-- 搜索筛选区域 -->
                <view class="search-section">
                    <view class="search-bar">
                        <uni-search-bar v-model="searchParams.title" placeholder="搜索视频标题" @confirm="refreshList"
                            @clear="handleClearSearch" cancelButton="none" />
                    </view>

                    <view class="filter-row">
                        <view class="filter-item">
                            <picker :range="auditStatusOptions" range-key="label" :value="auditStatusIndex"
                                @change="handleAuditStatusChange">
                                <view class="picker-item">
                                    <text>{{ auditStatusOptions[auditStatusIndex].label }}</text>
                                    <uni-icons type="down" size="14" color="#999" />
                                </view>
                            </picker>
                        </view>

                        <view class="filter-item">
                            <picker :range="contentTypeOptions" range-key="label" :value="contentTypeIndex"
                                @change="handleContentTypeChange">
                                <view class="picker-item">
                                    <text>{{ contentTypeOptions[contentTypeIndex].label }}</text>
                                    <uni-icons type="down" size="14" color="#999" />
                                </view>
                            </picker>
                        </view>
                    </view>
                </view>
            </template>

            <view class="video-list">
                <view class="video-item" v-for="item in videoList" :key="item.id" @click="viewVideoDetail(item)">
                    <view class="video-item-content">


                        <!-- 视频封面/缩略图 -->
                        <view class="video-cover">
                            <image :src="formatImageUrl(item.cover_image, ThumbnailPresets.medium)" mode="aspectFill" class="cover-image" />
                            <!-- 视频类型标识 -->
                            <view class="video-type-badge" v-if="item.type === 1">
                                <uni-icons type="videocam" size="16" color="#fff" />
                            </view>
                            <view class="image-type-badge" v-else>
                                <uni-icons type="image" size="16" color="#fff" />
                            </view>

                        </view>

                        <!-- 视频信息 -->
                        <view class="video-info">
                            <view class="video-header">
                                <view class="video-title">{{ item.title }}</view>
                                <view class="audit-status">
                                    <text class="status-text" :class="getAuditStatusClass(item.audit_status)">
                                        {{ item.audit_status_desc }}
                                    </text>
                                </view>
                            </view>

                            <view class="video-description">
                                {{ item.description }}
                            </view>

                            <!-- 数据统计 -->
                            <view class="video-stats">
                                <view class="stat-item">
                                    <uni-icons type="eye" size="14" color="#999" />
                                    <text>{{ formatNumber(item.view_count) }}</text>
                                </view>
                                <view class="stat-item">
                                    <uni-icons type="heart" size="14" color="#999" />
                                    <text>{{ formatNumber(item.like_count) }}</text>
                                </view>
                                <view class="stat-item">
                                    <uni-icons type="chatbubble" size="14" color="#999" />
                                    <text>{{ formatNumber(item.comment_count) }}</text>
                                </view>
                                <view class="publish-time">
                                    {{ item.publish_at || item.create_at }}
                                </view>
                            </view>

                            <!-- 特殊标签 -->
                            <view class="video-tags" v-if="hasSpecialTags(item)">
                                <text class="tag recommend" v-if="item.is_recommend">推荐</text>
                                <text class="tag top" v-if="item.is_top">置顶</text>
                                <text class="tag hot" v-if="item.is_hot">热门</text>
                            </view>

                        </view>
                    </view>

                    <!-- 操作按钮 -->
                    <view class="video-action-buttons">
                        <button class="ds-btn-sm ds-btn-white" @click.stop="confirmDeleteVideo(item)">
                            删除
                        </button>
                    </view>


                </view>
            </view>

        </z-paging>



        <!-- 发布视频按钮 -->
        <view class="fab-button" @click="goToPublish">
            <uni-icons type="plus" size="24" color="#fff" />
        </view>
    </view>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { getVideoShortPages, deleteVideoShort } from '@/video/api/blogger/videoShort'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'


// 分页相关
const pagingRef = ref<any>(null)
const videoList = ref<any[]>([])

// 搜索参数
const searchParams = reactive({
    title: '',
    audit_status: '',
    type: ''
})


// 筛选选项
const auditStatusOptions = [
    { label: '全部状态', value: '' },
    { label: '审核中', value: '0' },
    { label: '已发布', value: '1' },
    { label: '已下架', value: '2' }
]

const contentTypeOptions = [
    { label: '全部类型', value: '' },
    { label: '视频', value: '1' },
    { label: '图片', value: '2' }
]

// 当前选择的筛选索引
const auditStatusIndex = ref(0)
const contentTypeIndex = ref(0)

// 查询短视频列表
const queryVideoList = async (page_current: number, page_size: number) => {
    try {
        const params = {
            page_current,
            page_size,
            ...searchParams
        }

        const result = await getVideoShortPages(params)

        if (result.code === 10000) {
            pagingRef.value?.complete(result.data.data)
        } else {
            pagingRef.value?.complete(false)
            uni.showToast({
                title: result.message || '加载失败',
                icon: 'none'
            })
        }
    } catch (error) {
        console.error('短视频列表加载失败:', error)
        pagingRef.value?.complete(false)
        uni.showToast({
            title: '网络错误',
            icon: 'none'
        })
    }
}

// 刷新列表
const refreshList = () => {
    pagingRef.value?.reload()
}

// 清空搜索
const handleClearSearch = () => {
    searchParams.title = ''
    refreshList()
}

// 审核状态筛选
const handleAuditStatusChange = (e: any) => {
    auditStatusIndex.value = e.detail.value
    searchParams.audit_status = auditStatusOptions[e.detail.value].value
    refreshList()
}

// 内容类型筛选
const handleContentTypeChange = (e: any) => {
    contentTypeIndex.value = e.detail.value
    searchParams.type = contentTypeOptions[e.detail.value].value
    refreshList()
}

// 格式化数字
const formatNumber = (num: number) => {
    if (num >= 10000) {
        return (num / 10000).toFixed(1) + 'w'
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k'
    }
    return num.toString()
}

// 获取审核状态样式类
const getAuditStatusClass = (status: number) => {
    switch (status) {
        case 0: return 'pending'
        case 1: return 'published'
        case 2: return 'rejected'
        default: return 'unknown'
    }
}

// 检查是否有特殊标签
const hasSpecialTags = (item: any) => {
    return item.is_recommend || item.is_top || item.is_hot
}

// 查看视频详情
const viewVideoDetail = (item: any) => {
    uni.navigateTo({
        url: `/video/pages/home/detail?id=${item.id}`
    })
}

// 确认删除视频
const confirmDeleteVideo = (item: any) => {
    uni.showModal({
        title: '提示',
        content: '确定要删除这个短视频吗？删除后无法恢复。',
        success: (res) => {
            if (res.confirm) {
                handleDeleteVideo(item)
            }
        }
    })
}

// 删除视频
const handleDeleteVideo = async (item: any) => {
    try {
        const result = await deleteVideoShort(item.id)
        if (result.code === 10000) {
            uni.showToast({
                title: '删除成功',
                icon: 'success'
            })
            refreshList()
        } else {
            uni.showToast({
                title: result.message || '删除失败',
                icon: 'none'
            })
        }
    } catch (error) {
        uni.showToast({
            title: '删除失败',
            icon: 'none'
        })
    }
}

// 去发布页面
const goToPublish = () => {
    goToPage({
        url: '/video/pages/blogger/short/publish'
    })
}

onShow(() => {
    // 页面显示时刷新列表
    if (pagingRef.value) {
        refreshList()
    }
})
</script>

<style lang="scss" scoped>
.video-list-page {
    min-height: 100vh;
    background-color: #f8f8f8;
    padding-bottom: 120rpx;
}

// 搜索筛选区域
.search-section {
    background: #fff;
    padding: 20rpx;

    .search-bar {
        margin-bottom: 20rpx;
    }

    .filter-row {
        display: flex;
        gap: 20rpx;

        .filter-item {
            flex: 1;

            .picker-item {
                height: 60rpx;
                border: 2rpx solid #e5e5e5;
                border-radius: 8rpx;
                padding: 0 20rpx;
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 28rpx;
                color: #333;
            }
        }
    }
}

// 视频列表
.video-list {
    padding: 0 20rpx;

    .video-item {
        background: #fff;
        border-radius: 12rpx;
        margin-bottom: 20rpx;
        padding: 24rpx;
        position: relative;
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.05);

        .video-item-content {
            display: flex;
        }

        .video-cover {
            position: relative;
            width: 160rpx;
            height: 120rpx;
            border-radius: 8rpx;
            overflow: hidden;
            margin-right: 20rpx;
            flex-shrink: 0;

            .cover-image {
                width: 100%;
                height: 100%;
                background-color: #f5f5f5;
            }

            .video-type-badge,
            .image-type-badge {
                position: absolute;
                top: 8rpx;
                right: 8rpx;
                width: 32rpx;
                height: 32rpx;
                background: rgba(0, 0, 0, 0.6);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }


        }

        .video-info {
            flex: 1;
            display: flex;
            flex-direction: column;

            .video-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 12rpx;

                .video-title {
                    font-size: 30rpx;
                    color: #333;
                    font-weight: 600;
                    line-height: 1.4;
                    flex: 1;
                    margin-right: 20rpx;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                }

                .audit-status {
                    flex-shrink: 0;

                    .status-text {
                        font-size: 22rpx;
                        padding: 4rpx 12rpx;
                        border-radius: 20rpx;

                        &.pending {
                            background: #fff7e6;
                            color: #fa8c16;
                        }

                        &.published {
                            background: #f6ffed;
                            color: #52c41a;
                        }

                        &.rejected {
                            background: #fff2f0;
                            color: #ff4d4f;
                        }
                    }
                }
            }

            .video-description {
                font-size: 26rpx;
                color: #666;
                line-height: 1.4;
                margin-bottom: 16rpx;
            }

            .video-stats {
                display: flex;
                align-items: center;
                margin-bottom: 12rpx;

                .stat-item {
                    display: flex;
                    align-items: center;
                    margin-right: 32rpx;
                    font-size: 24rpx;
                    color: #999;

                    text {
                        margin-left: 8rpx;
                    }
                }

                .publish-time {
                    margin-left: auto;
                    font-size: 24rpx;
                    color: #999;
                }
            }

            .video-tags {
                display: flex;
                gap: 8rpx;

                .tag {
                    font-size: 20rpx;
                    padding: 2rpx 8rpx;
                    border-radius: 8rpx;

                    &.recommend {
                        background: #e6f7ff;
                        color: #1890ff;
                    }

                    &.top {
                        background: #fff0f6;
                        color: #eb2f96;
                    }

                    &.hot {
                        background: #fff2e8;
                        color: #fa541c;
                    }
                }
            }
        }

        .video-action-buttons {
            margin-top: 16rpx;
            display: flex;
            justify-content: flex-end;
        }
    }
}



// 悬浮按钮
.fab-button {
    position: fixed;
    bottom: 120rpx;
    right: 40rpx;
    width: 100rpx;
    height: 100rpx;
    background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8rpx 20rpx rgba(255, 107, 107, 0.3);
    z-index: 999;

    &:active {
        transform: scale(0.95);
    }
}
</style>