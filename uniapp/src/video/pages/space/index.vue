<template>
    <view class="space-container">
        <!-- 顶部状态栏占位 -->
        <view class="status-bar" :style="{ height: statusBarHeight + 'px' }"></view>

        <!-- 自定义导航栏 -->
        <view class="custom-navbar">
            <view class="nav-left" @click="goToPage({ url: '/video/pages/index' })">
                <uni-icons type="left" size="20" color="#333"></uni-icons>
            </view>
            <view class="nav-center">
                <text class="nav-title">{{ bloggerDetail.blogger_name || '用户主页' }}</text>
            </view>
            <view class="nav-right" @click="showMore">
                <uni-icons type="more-filled" size="20" color="#333"></uni-icons>
            </view>
        </view>

        <!-- 主内容区域 -->
        <scroll-view class="main-content" scroll-y enhanced>
            <!-- 用户信息卡片 -->
            <view class="user-card">
                <!-- 背景装饰 -->
                <view class="card-bg">
                    <view class="bg-decoration"></view>
                </view>

                <!-- 用户头像和基本信息 -->
                <view class="user-profile-section">
                    <!-- 用户头像 -->
                    <view class="avatar-section">
                        <image :src="formatImageUrl(bloggerDetail.avatar, ThumbnailPresets.small, 'avatar')" class="user-avatar" mode="aspectFill" />
                        <view class="verified-badge" v-if="bloggerDetail.verification_status === 1">
                            <uni-icons type="checkmarkempty" size="12" color="#fff"></uni-icons>
                        </view>
                    </view>

                    <!-- 用户基本信息 -->
                    <view class="user-info">
                        <view class="username">{{ bloggerDetail.blogger_name || '用户' }}</view>
                        <view class="user-id">ID号：{{ bloggerDetail.douyin_id || bloggerDetail.id }}</view>
                        <view class="user-bio" v-if="bloggerDetail.description">{{ bloggerDetail.description }}</view>
                    </view>

                    <!-- 操作按钮 -->
                    <view class="action-buttons">
                        <BloggerFollow :bloggerId="bloggerId" :isFollowed="bloggerDetail.is_followed || false"
                            theme="light" size="small" @followChanged="onFollowChanged" />
                    </view>
                </view>

                <!-- 数据统计 -->
                <view class="stats-section">
                    <view class="stat-item" @click="showFollowList('works')">
                        <text class="stat-number">{{ formatCount(bloggerDetail.video_count || 0) }}</text>
                        <text class="stat-label">作品</text>
                    </view>
                    <view class="stat-item" @click="showFollowList('followers')">
                        <text class="stat-number">{{ formatCount(bloggerDetail.follower_count || 0) }}</text>
                        <text class="stat-label">粉丝</text>
                    </view>
                    <view class="stat-item" @click="showFollowList('following')">
                        <text class="stat-number">{{ formatCount(bloggerDetail.following_count || 0) }}</text>
                        <text class="stat-label">关注</text>
                    </view>
                    <view class="stat-item">
                        <text class="stat-number">{{ formatCount(bloggerDetail.total_likes || 0) }}</text>
                        <text class="stat-label">获赞</text>
                    </view>
                </view>
            </view>

            <!-- 内容标签页 -->
            <view class="content-tabs">
                <view v-for="(tab, index) in contentTabs" :key="tab.key"
                    :class="['tab-item', { active: activeTabIndex === index }]" @click="switchTab(index)">
                    <uni-icons :type="tab.icon" size="18"
                        :color="activeTabIndex === index ? '#ff2442' : '#999'"></uni-icons>
                    <text class="tab-text">{{ tab.name }}</text>
                    <text class="tab-count" v-if="tab.count !== undefined">({{ tab.count }})</text>
                </view>
            </view>

            <!-- 作品展示区域 -->
            <view class="content-display">
                <!-- 当前博主的作品 -->
                <VideoShortList 
                    v-if="activeTabIndex === 0" 
                    :bloggerId="bloggerId" 
                    :query_type="'default'"
                    :blogger_id="bloggerDetail.id"
                />
                
                <!-- 当前博主关联的用户喜欢的短视频 -->
                <VideoShortList 
                    v-else-if="activeTabIndex === 1" 
                    :bloggerId="bloggerId" 
                    :query_type="'user_liked'"
                    :user_id="bloggerDetail.user.id"
                />
                
                <!-- 当前博主关联的用户收藏的短视频 -->
                <VideoShortList 
                    v-else-if="activeTabIndex === 2" 
                    :bloggerId="bloggerId" 
                    :query_type="'user_collected'"
                    :user_id="bloggerDetail.user.id"
                />
            </view>
        </scroll-view>

        <!-- 更多操作弹窗 -->
        <uni-popup ref="morePopup" type="bottom">
            <view class="more-actions">
                <view class="action-title">更多操作</view>
                <view class="action-list">
                    <view class="action-item" @click="copyProfile">
                        <uni-icons type="loop" size="20" color="#666"></uni-icons>
                        <text>复制链接</text>
                    </view>
                </view>
                <view class="cancel-btn" @click="closeMorePopup">取消</view>
            </view>
        </uni-popup>
    </view>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { onLoad, onReachBottom } from '@dcloudio/uni-app'
import { getBloggerDetail } from '@/video/api/home/blogger'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
// @ts-ignore
import BloggerFollow from '@/video/components/interaction/blogger-follow.vue'
// @ts-ignore
import VideoShortList from './components/video-short-list.vue'


// 页面参数
const bloggerId = ref<string | number>('')

// 状态栏高度
const statusBarHeight = ref(0)

// 博主详情数据
const bloggerDetail = ref<any>({})

// 内容标签页
const contentTabs = ref([
    { key: 'works', name: '作品', icon: 'videocam', count: undefined },
    { key: 'liked', name: '点赞', icon: 'heart' },
    { key: 'collected', name: '收藏', icon: 'star' }
])

// 当前标签页索引
const activeTabIndex = ref(0)

// 弹窗引用
const morePopup = ref<any>(null)

// 格式化数字显示
const formatCount = (count: number) => {
    if (count < 1000) return count.toString()
    if (count < 10000) return (count / 1000).toFixed(1) + 'k'
    if (count < 100000) return (count / 10000).toFixed(1) + 'w'
    return (count / 10000).toFixed(0) + 'w'
}

// 获取博主详情
const fetchBloggerDetail = async () => {
    try {
        const res = await getBloggerDetail(Number(bloggerId.value))
        if (res.code === 10000) {
            bloggerDetail.value = res.data
            // 更新作品数量
            contentTabs.value[0].count = res.data.video_count || 0
        }
    } catch (error) {
        console.error('获取博主详情失败:', error)
        uni.showToast({
            title: '获取用户信息失败',
            icon: 'none'
        })
    }
}

// 切换标签页
const switchTab = (index: number) => {
    activeTabIndex.value = index
}



// 显示更多操作
const showMore = () => {
    morePopup.value?.open()
}

// 关闭更多操作弹窗
const closeMorePopup = () => {
    morePopup.value?.close()
}

// 显示关注/粉丝列表
const showFollowList = (type: string) => {
    uni.showToast({
        title: `${type === 'followers' ? '粉丝' : type === 'following' ? '关注' : '作品'}列表功能开发中`,
        icon: 'none'
    })
}

// 关注状态变化回调
const onFollowChanged = (data: any) => {
    bloggerDetail.value.is_followed = data.isFollowed
    // 更新粉丝数
    if (data.isFollowed) {
        bloggerDetail.value.follower_count = (bloggerDetail.value.follower_count || 0) + 1
    } else {
        bloggerDetail.value.follower_count = Math.max((bloggerDetail.value.follower_count || 0) - 1, 0)
    }
}

// 举报用户
const reportUser = () => {
    closeMorePopup()
    uni.showToast({
        title: '举报功能开发中',
        icon: 'none'
    })
}

// 拉黑用户
const blockUser = () => {
    closeMorePopup()
    uni.showModal({
        title: '确认拉黑',
        content: `确定要拉黑 ${bloggerDetail.value.blogger_name} 吗？`,
        success: (res) => {
            if (res.confirm) {
                uni.showToast({
                    title: '拉黑功能开发中',
                    icon: 'none'
                })
            }
        }
    })
}

// 复制个人资料链接
const copyProfile = () => {
    closeMorePopup()
    const profileUrl = `https://your-domain.com/video/pages/space/index?id=${bloggerId.value}`
    uni.setClipboardData({
        data: profileUrl,
        success: () => {
            uni.showToast({
                title: '链接已复制',
                icon: 'success'
            })
        }
    })
}

// 页面加载
onLoad((options) => {
    bloggerId.value = options?.id || ''
    if (!bloggerId.value) {
        uni.showToast({
            title: '参数错误',
            icon: 'none'
        })
        setTimeout(() => {
            uni.navigateBack()
        }, 1500)
        return
    }

    // 获取状态栏高度
    const systemInfo = uni.getSystemInfoSync()
    statusBarHeight.value = systemInfo.statusBarHeight || 0

    // 加载数据
    fetchBloggerDetail()
})

// 触底加载更多
onReachBottom(() => {
    // 实现触底加载更多逻辑
})
</script>

<style lang="scss" scoped>
.space-container {
    min-height: 100vh;
    background: #f8f8f8;
}

.status-bar {
    background: #fff;
}

.custom-navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 88rpx;
    background: #fff;
    padding: 0 32rpx;
    border-bottom: 1rpx solid #f0f0f0;

    .nav-left,
    .nav-right {
        width: 60rpx;
        height: 60rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s ease;

        &:active {
            background: #f0f0f0;
        }
    }

    .nav-center {
        flex: 1;
        text-align: center;

        .nav-title {
            font-size: 32rpx;
            font-weight: 600;
            color: #333;
        }
    }
}

.main-content {
    height: calc(100vh - 88rpx);
}

.user-card {
    background: #fff;
    margin-bottom: 20rpx;
    position: relative;
    overflow: hidden;

    .card-bg {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 300rpx;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

        .bg-decoration {
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
    }

    .user-profile-section {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 60rpx 40rpx 32rpx;

        .avatar-section {
            position: relative;
            margin-right: 32rpx;
            flex-shrink: 0;

            .user-avatar {
                width: 160rpx;
                height: 160rpx;
                border-radius: 50%;
                border: 6rpx solid rgba(255, 255, 255, 0.8);
                box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.15);
            }

            .verified-badge {
                position: absolute;
                bottom: 8rpx;
                right: 16rpx;
                width: 32rpx;
                height: 32rpx;
                background: #1890ff;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 3rpx solid #fff;
            }
        }

        .user-info {
            flex: 1;
            text-align: left;
            margin-right: 32rpx;

            .username {
                font-size: 36rpx;
                font-weight: 600;
                color: #fff;
                margin-bottom: 12rpx;
                text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.3);
            }

            .user-id {
                font-size: 26rpx;
                color: rgba(255, 255, 255, 0.8);
                margin-bottom: 16rpx;
                text-shadow: 0 1rpx 2rpx rgba(0, 0, 0, 0.3);
            }

            .user-bio {
                font-size: 28rpx;
                color: rgba(255, 255, 255, 0.9);
                line-height: 1.6;
                margin-bottom: 20rpx;
                text-shadow: 0 1rpx 2rpx rgba(0, 0, 0, 0.3);
            }


        }

        .action-buttons {
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }
    }

    .stats-section {
        display: flex;
        justify-content: space-around;
        padding: 40rpx;
        border-top: 1rpx solid #f0f0f0;
        border-bottom: 1rpx solid #f0f0f0;

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;

            .stat-number {
                font-size: 32rpx;
                font-weight: 600;
                color: #333;
                margin-bottom: 8rpx;
            }

            .stat-label {
                font-size: 24rpx;
                color: #999;
            }
        }
    }
}

.content-tabs {
    display: flex;
    background: #fff;
    border-bottom: 1rpx solid #f0f0f0;
    
    .tab-item {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8rpx;
        height: 88rpx;
        position: relative;
        transition: all 0.3s ease;
        
        &.active {
            .tab-text {
                color: #ff2442;
                font-weight: 600;
            }
            
            &::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 60rpx;
                height: 4rpx;
                background: #ff2442;
                border-radius: 2rpx;
            }
        }
        
        .tab-text {
            font-size: 28rpx;
            color: #666;
            transition: color 0.3s ease;
        }
        
        .tab-count {
            font-size: 22rpx;
            color: #999;
        }
    }
}

.content-display {
    padding:0rpx;
}

.more-actions {
    background: #fff;
    border-radius: 20rpx 20rpx 0 0;
    padding: 40rpx;

    .action-title {
        text-align: center;
        font-size: 32rpx;
        font-weight: 600;
        color: #333;
        margin-bottom: 40rpx;
    }

    .action-list {
        .action-item {
            display: flex;
            align-items: center;
            gap: 20rpx;
            height: 88rpx;
            border-bottom: 1rpx solid #f0f0f0;

            &:last-child {
                border-bottom: none;
            }

            text {
                font-size: 28rpx;
                color: #333;
            }
        }
    }

    .cancel-btn {
        margin-top: 40rpx;
        height: 88rpx;
        background: #f8f8f8;
        border-radius: 12rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28rpx;
        color: #666;
    }
}
</style>
