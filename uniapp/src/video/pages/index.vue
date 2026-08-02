<template>
    <view class="page-container">
        <view class="video-home">
            <!-- 顶部Tab切换 -->
            <view class="top-tabs">
                <!-- 左侧分类图标 -->
                <view class="left-icon" @click="openBloggerInfoPopup">
                    <uni-icons type="bars" size="20" color="#666"></uni-icons>
                </view>

                <!-- 中间Tab切换 -->
                <view class="tab-container">
                    <view v-for="(tab, index) in tabs" :key="index"
                        :class="['tab-item', { active: currentTab === index }]" @click="switchTab(index)">
                        {{ tab.name }}
                    </view>
                </view>

                <!-- 右侧搜索图标 -->
                <view class="right-icon">
                    <uni-icons type="search" size="20" color="#666"></uni-icons>
                </view>
            </view>

            <!-- 内容区域 - 懒加载组件 -->
            <swiper :current="currentTab" @change="onSwiperChange" class="content-swiper">
                <!-- 推荐 - 首次加载 -->
                <swiper-item>
                    <IndexRecommend v-if="loadedTabs && loadedTabs.includes(0)" :is-active="currentTab === 0" />
                </swiper-item>

                <!-- 短视频 - 懒加载 -->
                <swiper-item>
                    <IndexFollow v-if="loadedTabs && loadedTabs.includes(1)" :is-active="currentTab === 1" />
                </swiper-item>
            </swiper>

            <VideoSidebar ref="bloggerInfoPopup" />
        </view>

        <!-- 底部导航 -->
        <VideoBottomNav current="home" theme="light" />
    </view>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import VideoSidebar from '@/video/components/sidebar/index.vue'
import VideoBottomNav from '@/video/components/video-bottom-nav/index.vue'

// 直接导入组件
import IndexRecommend from './index-recommend.vue'
import IndexFollow from './index-follow.vue'

const currentTab = ref(0)
const loadedTabs = ref<number[]>([0]) // 初始只加载第一个tab，添加类型注解

const tabs = ref([
    { name: '推荐', type: 'recommend' },
    // { name: '短视频', type: 'short' },
])

const switchTab = (index: number) => {
    currentTab.value = index

    // 懒加载当前tab和相邻tab
    loadTabIfNeeded(index)
    loadTabIfNeeded(index - 1)
    loadTabIfNeeded(index + 1)
}

const loadTabIfNeeded = (index: number) => {
    if (index >= 0 && index < tabs.value.length && loadedTabs.value && !loadedTabs.value.includes(index)) {
        loadedTabs.value.push(index)
    }
}

const onSwiperChange = (e: any) => {
    switchTab(e.detail.current)
}

// 确保初始化
onMounted(() => {
    // 确保loadedTabs已初始化
    if (!loadedTabs.value) {
        loadedTabs.value = [0]
    }
})

const bloggerInfoPopup = ref();
// 打开地区选择器
const openBloggerInfoPopup = () => {
    bloggerInfoPopup.value.open();
};


</script>

<style lang="scss" scoped>
.page-container {
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.video-home {
    flex: 1;
    display: flex;
    flex-direction: column;

    .top-tabs {
        display: flex;
        align-items: center;
        height: 88rpx;
        background: #fff;
        padding: 0 32rpx;
        position: relative;

        .left-icon {
            width: 60rpx;
            height: 60rpx;
            margin-right: 40rpx;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;

            &:active {
                opacity: 0.6;
                transform: scale(0.95);
            }
        }

        .tab-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 60rpx;

            .tab-item {
                padding: 20rpx 0;
                font-size: 32rpx;
                color: #999;
                font-weight: 400;
                transition: all 0.3s ease;
                position: relative;
                line-height: 1;

                &.active {
                    color: #333;
                    font-weight: 600;

                    &::after {
                        content: '';
                        position: absolute;
                        bottom: -16rpx;
                        left: 50%;
                        transform: translateX(-50%);
                        width: 48rpx;
                        height: 6rpx;
                        background: #ff2442;
                        border-radius: 3rpx;
                        animation: slideIn 0.3s ease;
                    }
                }

                &:active {
                    opacity: 0.6;
                }
            }
        }

        .right-icon {
            width: 60rpx;
            height: 60rpx;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 40rpx;
            transition: all 0.2s ease;

            &:active {
                opacity: 0.6;
                transform: scale(0.95);
            }
        }
    }

    .content-swiper {
        flex: 1;
        height: calc(100vh - 88rpx - 100rpx);
        /* 减去顶部导航和底部导航的高度 */
        padding-bottom: constant(safe-area-inset-bottom);
        padding-bottom: env(safe-area-inset-bottom);
    }
}

@keyframes slideIn {
    from {
        width: 0;
    }

    to {
        width: 48rpx;
    }
}

/* 响应式适配 */
@media screen and (max-width: 375px) {
    .video-home {
        .top-tabs {
            padding: 0 24rpx;

            .left-icon,
            .right-icon {
                width: 52rpx;
                height: 52rpx;
            }

            .tab-container {
                gap: 40rpx;

                .tab-item {
                    font-size: 26rpx;
                }
            }
        }
    }
}
</style>