<template>
    <view class="technician-page">
        <!-- 顶部个人信息区域 -->
        <view class="top-section" :style="topSectionStyle">
            <!-- 顶部操作按钮 -->
            <view class="top-actions">
                <view class="action-btn" @click="goToPage({ url: '/technician/pages/setting/index' })">
                    <uni-icons type="gear" size="22" color="#fff"></uni-icons>
                </view>
                <view class="action-btn" @click="goToPage({ url: '/shared/pages/system/notice/index' })">
                    <uni-icons type="notification" size="22" color="#fff"></uni-icons>
                </view>
            </view>

            <!-- 用户信息 -->
            <view class="user-profile">
                <view class="avatar-container" @click="goToPage({ url: '/technician/pages/profile/index' })">
                    <image class="avatar" :src="formatImageUrl(userInfo.technician.avatar, ThumbnailPresets.small, 'avatar') + '?' + time"
                        mode="aspectFill" />
                </view>
                <view class="user-name" @click="goToPage({ url: '/technician/pages/profile/index' })">{{ userInfo.technician?.name || userInfo.nickname }}</view>
                <view class="user-tag">师傅</view>
            </view>
        </view>

        <!-- 用户数据概览 -->
        <view class="user-stats-container">
            <view class="user-stats">
                <view class="stat-item" @click="goToPage({ url: '/technician/pages/balance/logs' })">
                    <view class="stat-value">{{ userInfo.technician ? userInfo.technician.balance : '0.00' }}</view>
                    <view class="stat-label">当前余额</view>
                </view>
                <view class="stat-item" @click="goToPage({ url: '/technician/pages/balance/logs' })">
                    <view class="stat-value">{{ userInfo.technician ? userInfo.technician.balance_in : '0.00' }}</view>
                    <view class="stat-label">总收入</view>
                </view>
                <view class="stat-item" @click="goToPage({ url: '/technician/pages/balance/logs' })">
                    <view class="stat-value">{{ userInfo.technician ? userInfo.technician.balance_out : '0.00' }}</view>
                    <view class="stat-label">总支出</view>
                </view>
            </view>
        </view>



        <!-- 订单模块 -->
        <view class="card">
            <view class="card-header" @click="goToPage({ url: '/technician/pages/order-delivery/index' })">
                <view class="header-title">
                    <view class="title-icon"><uni-icons type="paperplane" size="18" color="#fff"></uni-icons></view>
                    <text>服务订单</text>
                </view>
                <view class="header-action">
                    <text>查看全部订单</text>
                    <uni-icons type="right" size="14" color="#999"></uni-icons>
                </view>
            </view>

            <view class="order-icons" @click="goToPage({ url: '/technician/pages/order-delivery/index' })">
                <view class="order-icon-item" @click="goToPage({ url: '/technician/pages/order/pending' })">
                    <view class="icon-wrapper">
                        <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_order_04.png')"
                            class="icon-image" />
                    </view>
                    <text class="icon-text">待接单</text>
                </view>
                <view class="order-icon-item" @click="goToPage({ url: '/technician/pages/order/processing' })">
                    <view class="icon-wrapper">
                        <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_order_03.png')"
                            class="icon-image" />
                    </view>
                    <text class="icon-text">进行中</text>
                </view>
                <view class="order-icon-item" @click="goToPage({ url: '/technician/pages/order/completed' })">
                    <view class="icon-wrapper">
                        <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_order_06.png')" class="icon-image" />
                    </view>
                    <text class="icon-text">已完成</text>
                </view>
            </view>
        </view>

        <!-- 常用工具 -->
        <view class="card">
            <view class="card-header">
                <view class="header-title">
                    <view class="title-icon"><uni-icons type="gear" size="18" color="#fff"></uni-icons></view>
                    <text>常用工具</text>
                </view>
            </view>

            <view class="menu-grid">
                <view class="menu-item" @click="goToPage({ url: '/technician/pages/goods/index' })">
                    <view class="menu-icon-wrapper">
                        <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_02.png')" class="menu-icon" />
                    </view>
                    <text class="menu-text">服务项目</text>
                </view>
                <view class="menu-item" @click="goToPage({ url: '/technician/pages/balance/logs' })">
                    <view class="menu-icon-wrapper">
                        <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_01.png')" class="menu-icon" />
                    </view>
                    <text class="menu-text">收支明细</text>
                </view>
                <view class="menu-item" @click="goToPage({ url: '/technician/pages/balance/to-user-balance' })">
                    <view class="menu-icon-wrapper">
                        <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_19.png')" class="menu-icon" />
                    </view>
                    <text class="menu-text">转至用户余额</text>
                </view>
                <view class="menu-item" @click="goToPage({ url: '/shared/pages/system/notice/index' })">
                    <view class="menu-icon-wrapper">
                        <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_27.png')" class="menu-icon" />
                    </view>
                    <text class="menu-text">消息通知</text>
                </view>
                <view class="menu-item" @click="goToPage({ url: '/technician/pages/comment/index' })">
                    <view class="menu-icon-wrapper">
                        <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_20.png')" class="menu-icon" />
                    </view>
                    <text class="menu-text">我的评论</text>
                </view>
                <view class="menu-item" @click="goToPage({ url: '/shared/pages/contact_us' })">
                    <view class="menu-icon-wrapper">
                        <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_09.png')"
                            class="menu-icon" />
                    </view>
                    <text class="menu-text">联系客服</text>
                </view>
            </view>
        </view>

        <!-- 上下线切换按钮 -->
        <view class="online-status-card">
            <view class="online-status-info">
                <view class="status-label">当前状态:</view>
                <view class="status-value" :class="isOnline ? 'online' : 'offline'">
                    {{ isOnline ? '已上线' : '已下线' }}
                </view>
            </view>
            <view class="status-action">
                <button class="action-button" :class="isOnline ? 'offline-btn' : 'online-btn'"
                    @click="toggleOnlineStatus">
                    {{ isOnline ? '点击下线' : '点击上线' }}
                </button>
            </view>
        </view>

        <!-- 当前位置信息组件 -->
        <IndexLocation ref="locationRef" />


    </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import useUserStore from '@/stores/user'
import { storeToRefs } from 'pinia'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets, fetchRemoteIconUrl } from '@/utils/image'
import { onShow } from '@dcloudio/uni-app'
import { setTechnicianInfoStatus } from '@/technician/api/technician'
import IndexLocation from './index-location.vue'

const userStore = useUserStore()
const { userInfo } = storeToRefs(userStore)
const time = ref(Date.now())

// 计算属性：师傅是否上线
const isOnline = computed(() => {
    return userInfo.value?.technician?.technician_status == '1'
})

// 计算顶部区域的样式
const topSectionStyle = computed(() => {
    return {
      backgroundImage: `url(${fetchRemoteIconUrl('/uniapp/user_header_bg.png')})`
    }
})

// 切换上线/下线状态
const toggleOnlineStatus = async () => {
    if (!userInfo.value?.technician) {
        uni.showToast({
            title: '师傅信息不完整',
            icon: 'none'
        })
        return
    }

    try {
        await setTechnicianInfoStatus({
            status: isOnline.value ? '0' : '1'
        })
        // 强制刷新用户信息
        await userStore.getUserInfoWithRoles(true)

        uni.showToast({
            title: isOnline.value ? '已下线' : '已上线',
            icon: 'success'
        })
    } catch (error) {
        uni.showToast({
            title: '状态切换失败',
            icon: 'none'
        })
    }
}

onShow(async () => {
    await userStore.getUserInfoWithRoles()
    time.value = Date.now() // 更新时间戳避免头像缓存
})
</script>

<style lang="scss" scoped>
.technician-page {
    min-height: 100vh;
    background-color: #f8f8f8;
    padding-bottom: 40rpx;
}

// 顶部个人信息区域
.top-section {
    position: relative;
    height: 380rpx;
    overflow: visible;
    background-color: #4CAF50;
    background-size: 100% auto;
    background-position: center;
    background-repeat: no-repeat;

    .top-actions {
        position: absolute;
        top: 40rpx;
        left: 0;
        right: 0;
        z-index: 10;
        padding: 0 30rpx;
        display: flex;
        justify-content: space-between;
        pointer-events: none;

        .action-btn {
            width: 64rpx;
            height: 64rpx;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
            pointer-events: auto;

            &:active {
                transform: scale(0.95);
                background: rgba(255, 255, 255, 0.3);
            }
        }
    }

    .user-profile {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 40rpx;
        margin-bottom: 80rpx;
        z-index: 3;

        .avatar-container {
            width: 120rpx;
            height: 120rpx;
            border-radius: 60rpx;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 16rpx;
            border: 4rpx solid rgba(255, 255, 255, 0.6);
            transition: all 0.3s;

            &:active {
                transform: scale(0.98);
            }

            .avatar {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .avatar-placeholder {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
                background-color: #4CAF50;
            }
        }

        .user-name {
            font-size: 32rpx;
            color: #fff;
            font-weight: 600;
            margin-bottom: 8rpx;
            text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.1);
        }

        .user-tag {
            font-size: 22rpx;
            color: #fff;
            background: rgba(255, 255, 255, 0.2);
            padding: 4rpx 20rpx;
            border-radius: 30rpx;
            backdrop-filter: blur(4px);
        }
    }
}

// 用户数据统计区域
.user-stats-container {
    position: relative;
    height: 140rpx;
    margin-top: -70rpx;
    margin-bottom: 20rpx;
    z-index: 5;
}

.user-stats {
    margin: 0 20rpx;
    height: 140rpx;
    background: #fff;
    border-radius: 16rpx;
    box-shadow: 0 4rpx 16rpx rgba(76, 175, 80, 0.12);
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;

    &::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 200%;
        height: 200%;
        border: 2rpx solid #f5f5f5;
        transform: scale(0.5);
        transform-origin: 0 0;
        box-sizing: border-box;
        border-radius: 32rpx;
        pointer-events: none;
    }

    .stat-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;

        &:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 2rpx;
            height: 50rpx;
            background: #f5f5f5;
        }

        .stat-value {
            font-size: 32rpx;
            color: #4CAF50;
            font-weight: 600;
            margin-bottom: 8rpx;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            max-width: 90%;
        }

        .stat-label {
            font-size: 24rpx;
            color: #666;
        }
    }
}

// 卡片样式优化
.card {
    margin: 20rpx 20rpx;
    background: #fff;
    border-radius: 16rpx;
    position: relative;
    overflow: hidden;

    &::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 200%;
        height: 200%;
        border: 2rpx solid #f5f5f5;
        transform: scale(0.5);
        transform-origin: 0 0;
        box-sizing: border-box;
        border-radius: 32rpx;
        pointer-events: none;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24rpx 30rpx;
        position: relative;

        &::after {
            content: '';
            position: absolute;
            left: 30rpx;
            right: 30rpx;
            bottom: 0;
            height: 2rpx;
            background: #f5f5f5;
            transform: scaleY(0.5);
        }

        .header-title {
            display: flex;
            align-items: center;

            .title-icon {
                width: 40rpx;
                height: 40rpx;
                border-radius: 20rpx;
                background: linear-gradient(135deg, #4CAF50, #8BC34A);
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 12rpx;
                box-shadow: 0 4rpx 8rpx rgba(76, 175, 80, 0.2);
            }

            text {
                font-size: 30rpx;
                font-weight: 600;
                color: #333;
                background: linear-gradient(90deg, #333, #666);
                -webkit-background-clip: text;
                color: transparent;
            }
        }

        .header-action {
            display: flex;
            align-items: center;

            text {
                font-size: 26rpx;
                color: #999;
                margin-right: 6rpx;
            }
        }
    }
}

// 订单图标优化
.order-icons {
    display: flex;
    padding: 30rpx 20rpx;

    .order-icon-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;

        .icon-wrapper {
            position: relative;
            width: 96rpx;
            height: 96rpx;
            display: flex;
            align-items: center;
            justify-content: center;

            .icon-image {
                width: 66rpx;
                height: 66rpx;
            }

            .badge {
                position: absolute;
                top: -6rpx;
                right: -6rpx;
                min-width: 36rpx;
                height: 36rpx;
                line-height: 36rpx;
                padding: 0 8rpx;
                background: linear-gradient(135deg, #4CAF50, #8BC34A);
                color: #fff;
                font-size: 22rpx;
                border-radius: 18rpx;
                text-align: center;
                box-shadow: 0 4rpx 8rpx rgba(76, 175, 80, 0.3);
            }
        }

        .icon-text {
            font-size: 26rpx;
            color: #666;
            margin-top: 16rpx;
        }
    }
}

// 菜单网格优化
.menu-grid {
    display: flex;
    flex-wrap: wrap;
    padding: 10rpx 0;

    .menu-item {
        width: 25%;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20rpx 0;

        .menu-icon-wrapper {
            width: 88rpx;
            height: 88rpx;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12rpx;

            .menu-icon {
                width: 56rpx;
                height: 56rpx;
            }
        }

        .menu-text {
            font-size: 24rpx;
            color: #666;
        }
    }
}

// 上下线状态卡片
.online-status-card {
    margin: 20rpx;
    background: #fff;
    border-radius: 16rpx;
    padding: 30rpx;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
    position: relative;

    &::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 200%;
        height: 200%;
        border: 2rpx solid #f5f5f5;
        transform: scale(0.5);
        transform-origin: 0 0;
        box-sizing: border-box;
        border-radius: 32rpx;
        pointer-events: none;
    }

    .online-status-info {
        display: flex;
        align-items: center;

        .status-label {
            font-size: 28rpx;
            color: #333;
            margin-right: 10rpx;
        }

        .status-value {
            font-size: 28rpx;
            font-weight: bold;

            &.online {
                color: #4CAF50;
            }

            &.offline {
                color: #FF3D00;
            }
        }
    }

    .status-action {
        .action-button {
            height: 70rpx;
            padding: 0 40rpx;
            font-size: 28rpx;
            font-weight: bold;
            border-radius: 35rpx;
            line-height: 70rpx;

            &.online-btn {
                background: linear-gradient(135deg, #4CAF50, #8BC34A);
                color: #fff;
                box-shadow: 0 4rpx 8rpx rgba(76, 175, 80, 0.2);
            }

            &.offline-btn {
                background: #F5F5F5;
                color: #666;
                border: 1px solid #E0E0E0;
            }
        }
    }
}
</style>
