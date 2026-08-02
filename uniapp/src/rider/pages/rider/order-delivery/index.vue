<template>
    <view>
        <z-paging ref="paging" v-model="dataList" @query="queryList">
            <view class="order-list-container">
                <view v-for="order in dataList" :key="order.id" class="order-item">
                    <view class="order-info">
                        <view class="order-header">
                            <view class="order-id">订单编号：{{ order.order_id }}</view>
                            <view class="order-status">{{ order.delivery_status_desc }}</view>
                        </view>
                        <view class="order-address" @click="goToOrderDeliveryDetail(order)">
                            <view class="pickup">
                                <view class="location-icon">
                                    <uni-icons type="shop-filled" size="24" color="#FFA500"></uni-icons>
                                </view>
                                <view class="location-content">
                                    <view class="location-name">{{ order.orderAddress.shipper_name }}</view>
                                    <view class="location-address">{{ order.orderAddress.shipper_address ? order.orderAddress.shipper_address : '店铺未设置地址' }}</view>
                                </view>
                            </view>
                            <view class="delivery">
                                <view class="location-icon">
                                    <uni-icons type="location-filled" size="24" color="#33c9a6"></uni-icons>
                                </view>
                                <view class="location-content">
                                    <view class="location-name">{{ order.orderAddress.reciver_name }}</view>
                                    <view class="location-address">{{ order.orderAddress.reciver_address }}</view>
                                </view>
                            </view>
                        </view>
                        <view class="order-details">
                            <view class="detail-item">
                                <view class="detail-label">配送费：</view>
                                <view class="detail-value">¥{{ order.rider_fee }}</view>
                            </view>
                            <view class="detail-item">
                                <view class="detail-label">配送时间：</view>
                                <view class="detail-value">{{ order.rider_complete_time }}</view>
                            </view>
                            <view class="detail-item">
                                <view class="detail-label">配送费说明：</view>
                                <view class="detail-value">{{ order.rider_fee_desc }}</view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
        </z-paging>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getOrderDeliveryPages } from '@/rider/api/order/orderDelivery'
import { goToPage } from '@/utils/util'





const paging = ref<any>(null)
const dataList = ref([])

// 获取配送订单记录
const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getOrderDeliveryPages({
            page_current,
            page_size,
        })
        paging.value.complete(result.data.data)
    } catch (e) {
        console.error('获取订单记录失败', e)
        paging.value.complete(false)
    }
}

// 跳转到订单详情
const goToOrderDeliveryDetail = (orderDelivery: any) => {
    // 抢单不能看详情
    if (orderDelivery.rider_id == 0) {
        return;
    }

    goToPage({ url: `/rider/pages/rider/order-delivery/detail?order_id=${orderDelivery.order_id}` });
};

</script>

<style lang="scss" scoped>
.order-list-container {
    padding: 20rpx;
    background-color: #f5f5f5;
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
}

.order-header {
    display: flex;
    justify-content: space-between;
    padding-bottom: 20rpx;
    border-bottom: 1px solid #eee;
    margin-bottom: 20rpx;
}

.order-id {
    font-size: 28rpx;
    color: #666;
}

.order-status {
    font-size: 28rpx;
    color: #4cd964;
    font-weight: bold;
}

.order-address {
    padding: 10rpx 0;
    margin-bottom: 20rpx;

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

.order-details {
    background-color: #f9f9f9;
    border-radius: 8rpx;
    padding: 16rpx;
    
    .detail-item {
        display: flex;
        margin-bottom: 10rpx;
        
        &:last-child {
            margin-bottom: 0;
        }
        
        .detail-label {
            font-size: 26rpx;
            color: #666;
            min-width: 160rpx;
        }
        
        .detail-value {
            font-size: 26rpx;
            color: #333;
            flex: 1;
        }
    }
}
</style>


