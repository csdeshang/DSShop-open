<template>

    <view class="rider-dashboard">
        <!-- 使用z-paging组件 -->
        <z-paging ref="paging" v-model="dataList" @query="queryList">
            <template #top>
                <!-- 顶部导航图标 -->
                <view class="nav-icons">
                    <view class="icon-left" @tap="goToPage({ url: '/rider/pages/rider/index' })">
                        <uni-icons type="person-filled" size="20" color="#fff"></uni-icons>
                    </view>
                    <view class="icon-right" @tap="goToPage({ url: '/shared/pages/system/notice/index' })">
                        <uni-icons type="notification-filled" size="20" color="#fff"></uni-icons>
                    </view>
                </view>
                <!-- 顶部状态栏 -->
                <view class="status-bar">
                    <view class="status-item" :class="{ active: currentTab === 'grab' }" @tap="switchTab('grab')">
                        待抢单
                    </view>
                    <view class="status-item" :class="{ active: currentTab === 'pickup' }" @tap="switchTab('pickup')">
                        待取货
                    </view>
                    <view class="status-item" :class="{ active: currentTab === 'delivery' }" @tap="switchTab('delivery')">
                        待送达
                    </view>
                </view>
            </template>

            <view class="order-list-container">
                <!-- 使用统一的列表展示 -->
                <view v-for="orderDelivery in dataList" :key="orderDelivery.order_id" class="order-item">
                    <view class="order-info">
                        <view class="total-distance">
                            <view class="distance-value">{{ formatDistance(orderDelivery.rider_distance) }}</view>
                        </view>
                        <view class="order-address">
                            <view class="pickup">
                                <view class="location-icon">
                                    <uni-icons type="shop-filled" size="24" color="#FFA500"></uni-icons>
                                </view>
                                <view class="location-content">
                                    <view class="location-name">{{ orderDelivery.shipper_name }}</view>
                                    <view class="location-address">{{ orderDelivery.shipper_address ? orderDelivery.shipper_address :
                                        '店铺未设置地址' }}</view>
                                    <view class="location-distance">{{ formatDistance(orderDelivery.store_distance) }}
                                    </view>
                                </view>
                            </view>
                            <view class="delivery">
                                <view class="location-icon">
                                    <uni-icons type="location-filled" size="24" color="#33c9a6"></uni-icons>
                                </view>
                                <view class="location-content">
                                    <view class="location-name">{{ orderDelivery.reciver_name }}</view>
                                    <view class="location-address">{{ orderDelivery.reciver_address }}</view>
                                    <view class="location-distance">{{ formatDistance(orderDelivery.buyer_distance) }}
                                    </view>
                                </view>
                            </view>
                        </view>
                        <!-- 根据当前标签展示不同内容 -->
                        <view class="order-price">配送费：¥{{ orderDelivery.rider_fee }}</view>
                    </view>
                    <view class="order-actions">
                        <!-- 只在抢单状态显示抢单按钮 -->
                        <button v-if="orderDelivery.rider_available_actions.includes('grab')" class="grab-btn"
                            @tap="handleGrabOrder(orderDelivery.order_id)" :disabled="isSubmitting">抢单</button>
                        <button v-if="orderDelivery.rider_available_actions.includes('cancel')" class="cancel-btn"
                            @tap="handleCancel(orderDelivery.order_id)" :disabled="isSubmitting">取消</button>
                        <button v-if="orderDelivery.rider_available_actions.includes('arrived')" class="arrived-btn"
                            @tap="handleArrived(orderDelivery.order_id)" :disabled="isSubmitting">已到店</button>
                        <button v-if="orderDelivery.rider_available_actions.includes('pickup')" class="pickup-btn"
                            @tap="handlePickup(orderDelivery.order_id)" :disabled="isSubmitting">取货</button>
                        <button v-if="orderDelivery.rider_available_actions.includes('complete')" class="complete-btn"
                            @tap="handleComplete(orderDelivery.order_id)" :disabled="isSubmitting">完成送达</button>
                    </view>

                </view>
            </view>

            <template #bottom>
                <!-- 固定在底部的上线/下线功能区 -->
                <view class="rider-info-fixed">
                    <view class="rider-info-content">
                        <view class="rider-status">
                            <view class="status-label">接单状态：</view>
                            <view class="status-value">
                                {{ isOnline ? '上线中' : '已下线' }}
                            </view>
                        </view>
                        <view class="status-switch">
                            <button class="ds-btn-md" :class="isOnline ? 'ds-btn-green' : 'ds-btn-blue'" @tap="toggleOnlineStatus">
                                {{ isOnline ? '下线' : '上线' }}
                            </button>
                        </view>
                    </view>
                </view>
            </template>
        </z-paging>

        <LocationReporter :interval="10 * 60 * 1000" :report-in-background="true" />
    </view>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import { getWorkingOrderDeliveryList, grabOrderDelivery, arrivedOrderDelivery, pickupOrderDelivery, completeOrderDelivery, cancelOrderDelivery } from '@/rider/api/order/orderDelivery'
import { setRiderInfoStatus } from '@/rider/api/rider'
import { useLbsStore } from '@/stores/modules/lbs'
import useUserStore from '@/stores/user'
import { storeToRefs } from 'pinia'
import { goToPage } from '@/utils/util'
import LocationReporter from '@/rider/components/location-reporter.vue';

// 获取用户信息，包含骑手状态
const userStore = useUserStore()
const { userInfo } = storeToRefs(userStore)

const lbsStore = useLbsStore()
const currentTab = ref('grab')
const dataList = ref([])
const paging = ref<any>(null)
const isSubmitting = ref(false)

// 计算属性：骑手是否上线
const isOnline = computed(() => {
    return userInfo.value?.rider?.status == '1'
})

// 切换上线/下线状态
const toggleOnlineStatus = async () => {
    await setRiderInfoStatus({
        status: isOnline.value ? '0' : '1'
    })
    await userStore.getUserInfoWithRoles(true)
}

// 格式化距离
const formatDistance = (distance: number) => {
    if (!distance) return '';
    if (distance < 1000) {
        return `${distance.toFixed(0)}m`;
    } else {
        return `${(distance / 1000).toFixed(1)}km`;
    }
};



// 切换标签
const switchTab = (tab: string) => {
    currentTab.value = tab
    if (paging.value) {
        paging.value.reload()
    }
}

// 查询列表数据
const queryList = async (page_current: number, page_size: number) => {
    try {
        // 检查骑手是否上线
        if (!isOnline.value) {
            paging.value?.complete([])
            return
        }

        // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
        // 暂时设置为 1 天
        await lbsStore.getLocation(true, 24 * 60 * 60)

        // 使用统一的API获取不同状态的订单
        const res = await getWorkingOrderDeliveryList({
            page_current,
            page_size,
            tab_selected: currentTab.value,
            latitude: lbsStore.latitude,
            longitude: lbsStore.longitude
        })

        paging.value?.complete(res.data.data || [])
    } catch (error) {
        console.error('加载订单列表失败：', error)
        uni.showToast({
            title: '加载失败',
            icon: 'none'
        })
        paging.value?.complete(false)
    }
}

// 抢单
const handleGrabOrder = async (order_id: any) => {
    if (isSubmitting.value) return
    isSubmitting.value = true

    // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
    // 暂时设置为 1 天
    await lbsStore.getLocation(true, 24 * 60 * 60)

    const res = await grabOrderDelivery({
        order_id: order_id,
        latitude: lbsStore.latitude,
        longitude: lbsStore.longitude
    })

    if (res.code === 10000) {
        uni.showToast({
            title: '抢单成功',
            icon: 'success'
        })
        paging.value?.reload()
    } else {
        uni.showToast({
            title: res.message,
            icon: 'none'
        })
    }
    isSubmitting.value = false
}

// 取消
const handleCancel = async (order_id: any) => {
    if (isSubmitting.value) return
    isSubmitting.value = true

    // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
    // 暂时设置为 1 天
    await lbsStore.getLocation(true, 24 * 60 * 60)

    const res = await cancelOrderDelivery({
        order_id: order_id,
        latitude: lbsStore.latitude,
        longitude: lbsStore.longitude
    })

    if (res.code === 10000) {
        uni.showToast({
            title: '取消成功',
            icon: 'success'
        })
        paging.value?.reload()
    } else {
        uni.showToast({
            title: res.message,
            icon: 'none'
        })
    }
    isSubmitting.value = false
}

// 已到店
const handleArrived = async (order_id: any) => {
    if (isSubmitting.value) return
    isSubmitting.value = true

    // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
    // 暂时设置为 1 天
    await lbsStore.getLocation(true, 24 * 60 * 60)

    const res = await arrivedOrderDelivery({
        order_id: order_id,
        latitude: lbsStore.latitude,
        longitude: lbsStore.longitude
    })

    if (res.code === 10000) {
        uni.showToast({
            title: '已到店',
            icon: 'success'
        })
        paging.value?.reload()
    } else {
        uni.showToast({
            title: res.message,
            icon: 'none'
        })
    }
    isSubmitting.value = false
}

// 取货
const handlePickup = async (order_id: any) => {
    if (isSubmitting.value) return
    isSubmitting.value = true

    // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
    // 暂时设置为 1 天
    await lbsStore.getLocation(true, 24 * 60 * 60)

    const res = await pickupOrderDelivery({
        order_id: order_id,
        latitude: lbsStore.latitude,
        longitude: lbsStore.longitude
    })

    if (res.code === 10000) {
        uni.showToast({
            title: '取货成功',
            icon: 'success'
        })
        paging.value?.reload()
    } else {
        uni.showToast({
            title: res.message,
            icon: 'none'
        })
    }
    isSubmitting.value = false
}

// 完成送达
const handleComplete = async (order_id: any) => {
    if (isSubmitting.value) return
    isSubmitting.value = true

    // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
    // 暂时设置为 1 天
    await lbsStore.getLocation(true, 24 * 60 * 60)

    const res = await completeOrderDelivery({
        order_id: order_id,
        latitude: lbsStore.latitude,
        longitude: lbsStore.longitude
    })

    if (res.code === 10000) {
        uni.showToast({
            title: '完成送达',
            icon: 'success'
        })
        paging.value?.reload()
    } else {
        uni.showToast({
            title: res.message,
            icon: 'none'
        })
    }
    isSubmitting.value = false
}

</script>

<style lang="scss" scoped>
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 80rpx;
    background-color: #fff;
    padding: 0 20rpx;
}

.rider-dashboard {
    min-height: 100vh;
    background-color: #f5f5f5;
}

.nav-icons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20rpx 30rpx;
    background-color: #000;

    .icon-left {
        text-align: left;
        color: #fff;
    }

    .icon-right {
        text-align: right;
        color: #fff;
    }
}

.status-bar {
    display: flex;
    background-color: #fff;
    padding: 20rpx;
    border-bottom: 1rpx solid #eee;

    .status-item {
        flex: 1;
        text-align: center;
        padding: 20rpx 0;
        font-size: 28rpx;
        color: #666;

        &.active {
            color: #007AFF;
            font-weight: bold;
            position: relative;

            &::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 40rpx;
                height: 4rpx;
                background-color: #007AFF;
            }
        }
    }
}

.order-list-container {
    padding: 20rpx;
}

.order-item {
    background-color: #fff;
    border-radius: 12rpx;
    padding: 24rpx;
    margin-bottom: 24rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.order-info {
    position: relative;
    padding-bottom: 20rpx;
    margin-bottom: 16rpx;
    border-bottom: 1px dashed #eee;

    /* 总配送距离 */
    .total-distance {
        position: absolute;
        right: 0;
        top: 0;
        background-color: rgba(0, 122, 255, 0.1);
        padding: 8rpx 20rpx;
        border-radius: 30rpx;

        .distance-value {
            font-size: 26rpx;
            color: #007AFF;
            font-weight: bold;
        }
    }

    .order-address {
        padding-top: 10rpx;
        margin-bottom: 24rpx;

        .pickup,
        .delivery {
            position: relative;
            display: flex;
            align-items: flex-start;
            margin-bottom: 24rpx;

            /* 图标容器 */
            .location-icon {
                width: 40rpx;
                height: 40rpx;
                margin-right: 10rpx;
                display: flex;
                justify-content: center;
                align-items: center;

                /* uniapp图标 */
                text {
                    font-size: 36rpx;
                }
            }

            /* 内容容器 */
            .location-content {
                flex: 1;
                overflow: hidden;
            }

            /* 名称 */
            .location-name {
                font-size: 30rpx;
                font-weight: bold;
                color: #333;
                margin-bottom: 8rpx;
            }

            /* 地址 */
            .location-address {
                font-size: 26rpx;
                color: #666;
                margin-bottom: 8rpx;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }

            /* 距离 */
            .location-distance {
                font-size: 24rpx;
                color: #ff5722;

                &::before {
                    content: '距离：';
                    color: #999;
                }
            }
        }

        /* 取货地址样式 */
        .pickup {
            .location-icon {
                text {
                    color: #FFA500;
                }
            }

            .location-name {
                color: #FFA500;
            }
        }

        /* 送达地址样式 */
        .delivery {
            .location-icon {
                text {
                    color: #33c9a6;
                }
            }

            .location-name {
                color: #33c9a6;
            }
        }
    }

    .order-price {
        display: flex;
        align-items: center;
        padding: 8rpx 0;

        &::before {
            content: '';
            display: inline-block;
            width: 8rpx;
            height: 8rpx;
            background-color: #ff5722;
            border-radius: 50%;
            margin-right: 10rpx;
        }

        font-size: 32rpx;
        color: #ff5722;
        font-weight: bold;
    }
}

.order-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 16rpx;
    margin-top: 16rpx;

    button {
        flex: 1;
        min-width: 160rpx;
        padding: 20rpx 10rpx;
        text-align: center;
        margin: 0;
        height: 80rpx;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: 500;
        white-space: nowrap;

        &:only-child {
            flex: 1 1 100%;
        }

        // 单个按钮占满整行
        &:only-child {
            flex: 1 1 100%;
        }
        
        // 两个按钮时各占50%
        &:first-child:nth-last-child(2) {
            flex: 1 1 calc(50% - 8rpx);
        }
        &:nth-child(2):last-child {
            flex: 1 1 calc(50% - 8rpx);
        }

        // 三个按钮时各占33.33%
        &:first-child:nth-last-child(3) {
            flex: 1 1 calc(33.33% - 11rpx);
        }
        &:nth-child(2):nth-last-child(2) {
            flex: 1 1 calc(33.33% - 11rpx);
        }
        &:nth-child(3):last-child {
            flex: 1 1 calc(33.33% - 11rpx);
        }

        // 四个或更多按钮时按50%显示并换行
        &:first-child:nth-last-child(n+4) {
            flex: 0 1 calc(50% - 8rpx);
            margin-bottom: 16rpx;
        }
        &:nth-child(n+2):nth-last-child(n+3) {
            flex: 0 1 calc(50% - 8rpx);
            margin-bottom: 16rpx;
        }
        &:nth-child(n+3):nth-last-child(n+2) {
            flex: 0 1 calc(50% - 8rpx);
            margin-bottom: 16rpx;
        }
        &:last-child:nth-child(n+4) {
            flex: 0 1 calc(50% - 8rpx);
        }
    }
}

/* 按钮样式 */
button {
    font-size: 28rpx;
    border-radius: 8rpx;
    border: none;
    margin: 0;

    &[disabled] {
        opacity: 0.5;
    }
}

.grab-btn {
    background-color: #007AFF;
    color: #fff;
    box-shadow: 0 4rpx 8rpx rgba(0, 122, 255, 0.2);
}

.cancel-btn {
    background-color: #f7f7f7;
    color: #666;
    border: 1px solid #e5e5e5;
}

.arrived-btn {
    background-color: #FFA500;
    color: #fff;
    box-shadow: 0 4rpx 8rpx rgba(255, 165, 0, 0.2);
}

.pickup-btn {
    background-color: #33c9a6;
    color: #fff;
    box-shadow: 0 4rpx 8rpx rgba(51, 201, 166, 0.2);
}

.complete-btn {
    background-color: #4cd964;
    color: #fff;
    box-shadow: 0 4rpx 8rpx rgba(76, 217, 100, 0.2);
}

/* 骑手状态区域 - 固定在底部 */
.rider-info-fixed {
    background-color: #fff;
    box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
    border-top: 1rpx solid #eee;
    padding-bottom: env(safe-area-inset-bottom);

    .rider-info-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20rpx 40rpx;

        .rider-status {
            display: flex;
            align-items: center;

            .status-label {
                font-size: 28rpx;
                color: #333;
                font-weight: bold;
            }

            .status-value {
                font-size: 28rpx;
                font-weight: bold;
                color: #333;
            }
        }

        .status-switch {
        }
    }
}
</style>
