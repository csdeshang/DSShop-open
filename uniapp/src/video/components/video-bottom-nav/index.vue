<template>
    <view class="video-bottom-nav" :class="`theme-${theme}`">
        <view class="nav-container">
            <!-- 首页 -->
            <view class="nav-item" :class="{ active: current === 'home' }" @click="navigateTo('home')">
                <text class="nav-text">首页</text>
            </view>

            <!-- 发现/热门 -->
            <view class="nav-item" :class="{ active: current === 'discover' }" @click="navigateTo('discover')">
                <text class="nav-text">发现</text>
            </view>

            <!-- 发布按钮 -->
            <view class="nav-item publish-item" @click="navigateTo('publish')">
                <view class="publish-btn">
                    <uni-icons type="plus" size="24" color="#fff" />
                </view>
            </view>

            <!-- 消息 -->
            <view class="nav-item" :class="{ active: current === 'message' }" @click="navigateTo('message')">
                <text class="nav-text">消息</text>
            </view>

            <!-- 我的 -->
            <view class="nav-item" :class="{ active: current === 'profile' }" @click="navigateTo('profile')">
                <text class="nav-text">我</text>
            </view>
        </view>

        <!-- 安全区域 -->
        <view class="safe-area"></view>
    </view>
</template>

<script setup lang="ts">
// @ts-ignore
import { goToPage } from '@/utils/util';

// Props 定义
const props = defineProps({
    // 当前激活的页面
    current: {
        type: String,
        default: 'home'
    },
    // 主题风格
    theme: {
        type: String,
        default: 'light', // light: 浅色主题, dark: 深色主题
        validator: (value: string) => ['light', 'dark'].includes(value)
    }
})

// 导航方法
const navigateTo = (type: string) => {
    // 如果是当前页面，不处理
    if (type === props.current && type !== 'publish') {
        return
    }

    // 根据不同类型进行页面跳转
    switch (type) {
        case 'home':
            goToPage({
                url: '/video/pages/index'
            })
            break
        case 'discover':
            goToPage({
                url: '/video/pages/short/detail/video'
            })
            break
        case 'publish':
            // 发布页面通常是 navigateTo，不是 switchTab
            goToPage({
                url: '/video/pages/blogger/short/publish'
            })
            break
        case 'message':
            goToPage({
                url: '/shared/pages/system/notice/index'
            })
            break
        case 'profile':

            goToPage({
                url: '/video/pages/user/index'
            })
            break
    }
}
</script>

<style lang="scss" scoped>
.video-bottom-nav {
    position: relative;
    width: 100%;
    min-height: 100rpx;
    flex-shrink: 0;
    z-index: 999;

    // 浅色主题
    &.theme-light {
        background: #fff;
        border-top: 1rpx solid #eee;

        .nav-item {
            .nav-text {
                color: #999;
            }

            &.active .nav-text {
                color: #333;
                font-weight: 600;
            }
        }

        .safe-area {
            background: #fff;
        }
    }

    // 深色主题
    &.theme-dark {
        background: rgba(0, 0, 0, 0.9);
        backdrop-filter: blur(10rpx);
        border-top: 1rpx solid rgba(255, 255, 255, 0.1);

        .nav-item {
            .nav-text {
                color: rgba(255, 255, 255, 0.7);
            }

            &.active .nav-text {
                color: #fff;
                font-weight: 600;
            }
        }

        .safe-area {
            background: rgba(0, 0, 0, 0.8);
        }
    }
}

.nav-container {
    display: flex;
    align-items: center;
    height: 100rpx;
    padding: 0 16rpx;
}

.nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.2s ease;

    &:active {
        opacity: 0.7;
    }

    .nav-text {
        font-size: 28rpx;
        transition: all 0.2s ease;
        line-height: 1;
    }
}

// 发布按钮特殊样式
.publish-item {
    .publish-btn {
        width: 72rpx;
        height: 56rpx;
        background: linear-gradient(135deg, #ff2442 0%, #ff6b6b 100%);
        border-radius: 8rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;

        &:active {
            transform: scale(0.95);
        }
    }
}

// 安全区域
.safe-area {
    height: constant(safe-area-inset-bottom);
    height: env(safe-area-inset-bottom);
}
</style>
