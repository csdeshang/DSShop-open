<template>
    <view class="order-container">
        <z-paging ref="paging" v-model="dataList" @query="queryList">
            <!-- 订单状态选项卡 -->

            <view class="order-tabs">
                <view v-for="(tab, index) in tabs" :key="index" class="tab-item"
                    :class="{ active: currentTab === index }" @click="switchTab(index)">
                    {{ tab.label }}
                </view>
            </view>


            <!-- 订单列表 -->
            <view class="order-list" v-if="dataList.length > 0">
                <view class="order-item" v-for="orderDelivery in dataList" :key="orderDelivery.id">
                    <!-- 订单头部信息 -->
                    <view class="order-header">
                        <view class="order-id">订单编号：{{ orderDelivery.order.order_sn }}</view>
                        <view class="order-status">{{ orderDelivery.delivery_status_desc }}</view>
                    </view>

                    <!-- 服务信息 -->
                    <view class="service-info" @click="goToOrderDeliveryDetail(orderDelivery)">
                        <view class="service-item" v-for="(service, sIndex) in orderDelivery.orderGoodsList"
                            :key="sIndex">
                            <image class="service-image" :src="formatImageUrl(service.goods_image, ThumbnailPresets.small, 'goods')" mode="aspectFill" />
                            <view class="service-details">
                                <view class="service-name">{{ service.goods_name }}</view>
                                <view class="service-sku">{{ service.sku_name }}</view>
                                <view class="service-price-qty">
                                    <text class="service-price">¥{{ service.pay_price }}</text>
                                    <text class="service-qty">x{{ service.goods_num }}</text>
                                </view>
                            </view>
                        </view>
                    </view>

                    <!-- 订单信息 -->
                    <view class="order-details">
                        <view class="detail-item">
                            <view class="detail-label">预约时间：</view>
                            <view class="detail-value">{{ orderDelivery.technician_appt_time || '尽快上门' }}</view>
                        </view>
                        <view class="detail-item">
                            <view class="detail-label">服务费：</view>
                            <view class="detail-value price-value">¥{{ orderDelivery.technician_fee > 0 ?
                                orderDelivery.technician_fee : 0 }}</view>
                        </view>
                        <view class="detail-item">
                            <view class="detail-label">备注：</view>
                            <view class="detail-value">{{ orderDelivery.order.user_remark || '无' }}</view>
                        </view>
                    </view>

                    <!-- 订单操作按钮 -->
                    <view class="order-actions">
                        <OrderButtons :orderDelivery="orderDelivery" @refresh="refreshPage" />
                    </view>
                </view>
            </view>
        </z-paging>

        <!-- 位置上报组件 10分钟 -->
        <LocationReporter :interval="10 * 60 * 1000" :report-in-background="true" />

    </view>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { goToPage } from '@/utils/util';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import LocationReporter from '@/technician/components/location-reporter.vue';
import {
    getOrderDeliveryPages,
} from '@/technician/api/orderDelivery';

import OrderButtons from './order-buttons.vue';

import { useLbsStore } from '@/stores/modules/lbs'
const lbsStore = useLbsStore()



// 定义订单状态选项卡
const tabs = [
    { label: '全部', value: '' },
    { label: '待抢单', value: 'grab' },
    { label: '待确认', value: 'courier_accept' },
    { label: '待出发', value: 'courier_departure' },
    { label: '服务中', value: 'courier_service' },
    { label: '已完成', value: 'courier_complete' },
    { label: '已取消', value: 'courier_cancel' }
];

// 当前选中的选项卡
const currentTab = ref(0);

// 订单列表
const dataList = ref([]);

// z-paging 引用
const paging = ref<any>(null);

// 切换选项卡
const switchTab = (index: number) => {
    if (currentTab.value !== index) {
        currentTab.value = index;
        // 使用z-paging提供的刷新方法
        if (paging.value) {
            paging.value.reload();
        }
    }
};

// 查询列表数据 - 提供给z-paging的回调函数
const queryList = async (page_current: number, page_size: number) => {
    try {

        // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
        // 暂时设置为 1 天
        await lbsStore.getLocation(true, 24 * 60 * 60)


        const result = await getOrderDeliveryPages({
            page_current,
            page_size,
            tab_selected: tabs[currentTab.value].value,
            latitude: lbsStore.latitude,
            longitude: lbsStore.longitude
        });

        // 处理完成后通知z-paging
        if (paging.value) {
            paging.value.complete(result.data.data);
        }
    } catch (e) {
        console.error('查询订单列表失败:', e);
        if (paging.value) {
            paging.value.complete(false);
        }
        uni.showToast({
            title: '加载订单失败',
            icon: 'none'
        });
    }
};

// 刷新页面
const refreshPage = () => {
    if (paging.value) {
        paging.value.reload();
    }
}
// 跳转到订单详情
const goToOrderDeliveryDetail = (orderDelivery: any) => {

    // 抢单不能看详情
    if (orderDelivery.technician_id == 0) {
        return;
    }

    goToPage({ url: `/technician/pages/order-delivery/detail?order_id=${orderDelivery.order_id}` });
};



</script>

<style lang="scss" scoped>
.order-container {
    background-color: #f5f5f5;
    min-height: 100vh;
}

.order-tabs {
    display: flex;
    background-color: #fff;
    padding: 20rpx 0;
    border-radius: 10rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

    .tab-item {
        flex: 1;
        text-align: center;
        font-size: 26rpx;
        color: #666;
        position: relative;
        padding-bottom: 10rpx;
    }

    .tab-item.active {
        color: #33a9ff;
        font-weight: bold;
    }

    .tab-item.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 40rpx;
        height: 4rpx;
        background-color: #33a9ff;
        border-radius: 2rpx;
    }
}

.order-list {
    padding: 20rpx;
}

.order-item {
    background-color: #fff;
    border-radius: 10rpx;
    margin-bottom: 20rpx;
    padding: 20rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.order-header {
    display: flex;
    justify-content: space-between;
    padding-bottom: 20rpx;
    border-bottom: 1rpx solid #f0f0f0;

    .order-id {
        font-size: 28rpx;
        color: #666;
    }

    .order-status {
        font-size: 28rpx;
        color: #33a9ff;
        font-weight: bold;
    }
}

.service-info {
    padding: 20rpx 0;
    border-bottom: 1rpx solid #f0f0f0;

    .service-item {
        display: flex;
        margin-bottom: 20rpx;

        &:last-child {
            margin-bottom: 0;
        }
    }

    .service-image {
        width: 120rpx;
        height: 120rpx;
        border-radius: 8rpx;
        margin-right: 20rpx;
        background-color: #f9f9f9;
    }

    .service-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .service-name {
        font-size: 28rpx;
        color: #333;
        margin-bottom: 10rpx;
    }

    .service-sku {
        font-size: 24rpx;
        color: #999;
    }

    .service-price-qty {
        display: flex;
        justify-content: space-between;

        .service-price {
            font-size: 28rpx;
            color: #33a9ff;
            font-weight: bold;
        }

        .service-qty {
            font-size: 24rpx;
            color: #999;
        }
    }
}

.order-details {
    padding: 20rpx 0;
    background-color: #f9f9f9;
    border-radius: 8rpx;
    margin: 20rpx 0;
    padding: 16rpx;

    .detail-item {
        display: flex;
        margin-bottom: 10rpx;

        &:last-child {
            margin-bottom: 0;
        }
    }

    .detail-label {
        width: 160rpx;
        font-size: 26rpx;
        color: #666;
    }

    .detail-value {
        flex: 1;
        font-size: 26rpx;
        color: #333;
    }
}

.order-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 20rpx;

    button {
        margin-left: 20rpx;
        background-color: #33a9ff;
        color: #fff;
        font-size: 28rpx;
        padding: 10rpx 30rpx;
        border-radius: 8rpx;
        border: none;
        line-height: 1.5;

        &:first-child {
            margin-left: 0;
        }
    }
}
</style>
