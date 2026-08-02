<template>
    <!-- 博主信息侧边弹窗 -->
    <uni-popup ref="popupRef" type="left" background-color="#fff" width="75%">
        <view class="sidebar-container">
            <!-- 头部用户信息区域 -->
            <view class="user-header"
                @click="isLogin ? goToSpace() : navigateToAuth('login', '/video/pages/index')">
                <view class="user-avatar">
                    <image v-if="isLogin" :src="formatImageUrl(userInfo.blogger.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill"
                        class="avatar-img"></image>
                    <view v-else class="avatar-placeholder">
                        <uni-icons type="person" size="24" color="#999"></uni-icons>
                    </view>
                </view>
                <view class="user-info">
                    <text class="username">{{ userInfo?.blogger?.blogger_name || '未登录' }}</text>
                    <text class="user-desc">{{ userInfo?.blogger?.description || '暂无简介' }}</text>
                </view>
            </view>

            <!-- 菜单列表 -->
            <view class="menu-list">


                <view class="menu-item" @click="goToPage({ url: '/video/pages/user/index' })">
                    <view class="menu-icon">
                        <uni-icons type="gear" size="20" color="#666"></uni-icons>
                    </view>
                    <text class="menu-text">创作者中心</text>
                    <view class="menu-arrow">
                        <uni-icons type="right" size="14" color="#ccc"></uni-icons>
                    </view>
                </view>

                <view class="menu-item" @click="goToPage({ url: '/video/pages/blogger/short/publish' })">
                    <view class="menu-icon">
                        <uni-icons type="gear" size="20" color="#666"></uni-icons>
                    </view>
                    <text class="menu-text">发布作品</text>
                    <view class="menu-arrow">
                        <uni-icons type="right" size="14" color="#ccc"></uni-icons>
                    </view>
                </view>

                <view class="menu-item" @click="goToPage({ url: '/video/pages/user/video-short-view-log' })">
                    <view class="menu-icon">
                        <uni-icons type="spinner-cycle" size="20" color="#666"></uni-icons>
                    </view>
                    <text class="menu-text">浏览记录</text>
                    <view class="menu-arrow">
                        <uni-icons type="right" size="14" color="#ccc"></uni-icons>
                    </view>
                </view>


            </view>

            <!-- 底部功能按钮 -->
            <view class="bottom-actions">
                <view class="action-item" @click="goToPage({ url: '/shared/pages/contact_us' })">
                    <view class="action-icon">
                        <uni-icons type="help" size="18" color="#666"></uni-icons>
                    </view>
                    <text class="action-text">帮助与客服</text>
                </view>

                <view class="action-item">
                    <view class="action-icon">
                        <uni-icons type="gear" size="18" color="#666"></uni-icons>
                    </view>
                    <text class="action-text">设置</text>
                </view>
            </view>
        </view>
    </uni-popup>
</template>


<script setup lang="ts">
import { ref } from 'vue'
import useUserStore from '@/stores/user'
import { storeToRefs } from 'pinia'
import { goToPage, navigateToAuth } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

const userStore = useUserStore()
const { userInfo, isLogin } = storeToRefs(userStore)

const popupRef = ref()


const goToSpace = () => {
    goToPage({ url: '/video/pages/space/index?id=' + userInfo.value.blogger.id })
}


// 打开弹窗
const open = () => {
    popupRef.value.open()
    // 获取用户信息
    userStore.getUserInfoWithRoles()
}

// 关闭弹窗
const close = () => {
    popupRef.value.close()
}

defineExpose({
    open,
    close
})
</script>


<style lang="scss" scoped>
.sidebar-container {
    width: 500rpx;
    height: 100vh;
    background: #fff;
    display: flex;
    flex-direction: column;

    .user-header {
        padding: 100rpx 40rpx 50rpx;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        position: relative;

        &::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        }

        .user-avatar {
            width: 100rpx;
            height: 100rpx;
            margin-right: 24rpx;
            position: relative;
            z-index: 2;

            .avatar-img {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                border: 3rpx solid rgba(255, 255, 255, 0.3);
                box-shadow: 0 6rpx 20rpx rgba(0, 0, 0, 0.15);
            }

            .avatar-placeholder {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.2);
                display: flex;
                align-items: center;
                justify-content: center;
                border: 3rpx solid rgba(255, 255, 255, 0.3);
                box-shadow: 0 6rpx 20rpx rgba(0, 0, 0, 0.15);
                backdrop-filter: blur(10rpx);
            }
        }

        .user-info {
            flex: 1;
            position: relative;
            z-index: 2;

            .username {
                display: block;
                font-size: 28rpx;
                font-weight: 600;
                color: #fff;
                margin-bottom: 6rpx;
                text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.1);
            }

            .user-desc {
                font-size: 22rpx;
                color: rgba(255, 255, 255, 0.9);
            }
        }
    }

    .menu-list {
        flex: 1;
        padding: 40rpx 0;

        .menu-item {
            display: flex;
            align-items: center;
            padding: 32rpx 40rpx;
            transition: background 0.2s ease;

            &:active {
                background: #f5f5f5;
            }

            .menu-icon {
                width: 60rpx;
                height: 60rpx;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 32rpx;
            }

            .menu-text {
                flex: 1;
                font-size: 26rpx;
                color: #333;
                font-weight: 400;
            }

            .menu-arrow {
                width: 32rpx;
                height: 32rpx;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }
    }

    .bottom-actions {
        padding: 40rpx 40rpx 200rpx 40rpx;
        border-top: 1rpx solid #f0f0f0;
        display: flex;
        justify-content: space-around;

        .action-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20rpx;
            transition: opacity 0.2s ease;

            &:active {
                opacity: 0.6;
            }

            .action-icon {
                width: 60rpx;
                height: 60rpx;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f8f8f8;
                border-radius: 50%;
                margin-bottom: 16rpx;
            }

            .action-text {
                font-size: 20rpx;
                color: #666;
                text-align: center;
            }
        }
    }
}
</style>
