<template>
    <view class="detail-container">
        <!-- 订单状态 -->
        <view class="status-card">
            <view class="status-title">{{ orderDelivery.delivery_status_desc }}</view>
            <view class="status-desc">订单编号：{{ orderDelivery.order?.order_sn }}</view>
        </view>

        <!-- 地图显示 -->
        <DetailMap v-if="orderDelivery.orderAddress && orderDelivery.orderAddress.reciver_latitude && orderDelivery.orderAddress.reciver_longitude" :orderDelivery="orderDelivery" />

        <!-- 取货地址信息 -->
        <view class="info-card">
            <view class="card-title">取货地址</view>
            <view class="info-item">
                <text class="label">店铺名称：</text>
                <text class="value">{{ orderDelivery.orderAddress?.shipper_name || '店铺未设置名称' }}</text>
            </view>
            <view class="info-item">
                <text class="label">联系电话：</text>
                <text class="value phone-value" @click="callStore">{{ orderDelivery.orderAddress?.shipper_mobile || '暂无联系方式' }}</text>
            </view>
            <view class="info-item">
                <text class="label">取货地址：</text>
                <text class="value">{{ orderDelivery.orderAddress?.shipper_address || '店铺未设置地址' }}</text>
            </view>
        </view>

        <!-- 收货地址信息 -->
        <view class="info-card">
            <view class="card-title">收货地址</view>
            <view class="info-item">
                <text class="label">收货人：</text>
                <text class="value">{{ orderDelivery.orderAddress?.reciver_name }}</text>
            </view>
            <view class="info-item">
                <text class="label">联系电话：</text>
                <text class="value phone-value" @click="callCustomer">{{ orderDelivery.orderAddress?.reciver_mobile }}</text>
            </view>
            <view class="info-item">
                <text class="label">收货地址：</text>
                <text class="value">{{ orderDelivery.orderAddress?.reciver_address }}</text>
            </view>
        </view>

        <!-- 商品信息 -->
        <view class="info-card">
            <view class="card-title">商品信息</view>
            <view class="goods-list">
                <view class="goods-item" v-for="(goods, index) in orderDelivery.orderGoodsList" :key="index">
                    <image class="goods-image" :src="formatImageUrl(goods.goods_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill"></image>
                    <view class="goods-info">
                        <view class="goods-name">{{ goods.goods_name }}</view>
                        <view class="goods-sku">规格：{{ goods.sku_name }}</view>
                        <view class="goods-price-qty">
                            <text class="goods-price">¥{{ goods.pay_price }}</text>
                            <text class="goods-qty">x{{ goods.goods_num }}</text>
                        </view>
                    </view>
                </view>
            </view>
        </view>

        <!-- 订单信息 -->
        <view class="info-card">
            <view class="card-title">订单信息</view>
            <view class="info-item">
                <text class="label">下单时间：</text>
                <text class="value">{{ orderDelivery.create_at }}</text>
            </view>
            <view class="info-item">
                <text class="label">支付时间：</text>
                <text class="value">{{ orderDelivery.order?.payment_time }}</text>
            </view>
            <view class="info-item" v-if="orderDelivery.order?.delivery_time">
                <text class="label">发货时间：</text>
                <text class="value">{{ orderDelivery.order.delivery_time }}</text>
            </view>
            <view class="info-item" v-if="orderDelivery.order?.finnshed_time">
                <text class="label">完成时间：</text>
                <text class="value">{{ orderDelivery.order?.finnshed_time }}</text>
            </view>
            <view class="info-item" v-if="orderDelivery.order?.user_remark">
                <text class="label">备注信息：</text>
                <text class="value">{{ orderDelivery.order?.user_remark }}</text>
            </view>
        </view>

        <!-- 费用信息 -->
        <view class="info-card">
            <view class="card-title">费用信息</view>
            <view class="info-item total-price">
                <text class="label">订单总额：</text>
                <text class="value price">¥{{ orderDelivery.order?.order_amount }}</text>
            </view>
            <view class="info-item">
                <text class="label">商品金额：</text>
                <text class="value">¥{{ orderDelivery.order?.goods_amount }}</text>
            </view>
            <view class="info-item">
                <text class="label">配送费：</text>
                <text class="value">¥{{ orderDelivery.order?.shipping_amount > 0 ? orderDelivery.order.shipping_amount : 0 }}</text>
            </view>
            <view class="info-item" v-if="orderDelivery.rider_fee">
                <text class="label">骑手收入：</text>
                <text class="value price">¥{{ orderDelivery.rider_fee }}</text>
            </view>
        </view>

        <!-- 分账明细 -->
        <view class="info-card" v-if="orderDelivery.orderFinance">
            <view class="card-title">分账明细</view>
            <view class="info-item">
                <text class="label">支付金额：</text>
                <text class="value">¥{{ orderDelivery.orderFinance.pay_amount }}</text>
            </view>
            <view class="info-item" v-if="orderDelivery.orderFinance.store_amount > 0">
                <text class="label">店铺收入：</text>
                <text class="value">¥{{ orderDelivery.orderFinance.store_amount }}</text>
            </view>
            <view class="info-item" v-if="orderDelivery.orderFinance.store_sys_amount > 0">
                <text class="label">平台抽成：</text>
                <text class="value">¥{{ orderDelivery.orderFinance.store_sys_amount }}</text>
            </view>
            <view class="info-item">
                <text class="label">骑手总收入：</text>
                <text class="value price">¥{{ orderDelivery.orderFinance.rider_amount }}</text>
            </view>
            <view class="info-item">
                <text class="label">骑手平台抽成：</text>
                <text class="value">¥{{ orderDelivery.orderFinance.rider_sys_amount }}</text>
            </view>
            <view class="info-item" v-if="orderDelivery.orderFinance.distributor_amount > 0">
                <text class="label">分销收入：</text>
                <text class="value">¥{{ orderDelivery.orderFinance.distributor_amount }}</text>
            </view>
            <view class="info-item" v-if="orderDelivery.orderFinance.refund_amount > 0">
                <text class="label">退款金额：</text>
                <text class="value">¥{{ orderDelivery.orderFinance.refund_amount }}</text>
            </view>
            <view class="info-item">
                <text class="label">结算状态：</text>
                <text class="value">{{ orderDelivery.orderFinance.finance_status === 1 ? '已结算' : '未结算' }}</text>
            </view>
            <view class="info-item" v-if="orderDelivery.orderFinance.settle_time">
                <text class="label">结算时间：</text>
                <text class="value">{{ orderDelivery.orderFinance.settle_time }}</text>
            </view>
        </view>
        <view class="info-card" v-else>
            <view class="card-title">结算信息</view>
            <view class="info-item">
                <text class="label">结算状态：</text>
                <text class="value">未结算(用户确认收货后结算)</text>
            </view>
        </view>

        <!-- 底部操作区域 -->
        <view class="bottom-actions" v-if="orderDelivery.rider_available_actions && orderDelivery.rider_available_actions.length > 0">
            <OrderButtons :orderDelivery="orderDelivery" @refresh="refreshPage" />
        </view>
    </view>
</template>

<script setup lang="ts">
import {
    getOrderDeliveryInfo
} from '@/rider/api/order/orderDelivery';
import { onLoad } from '@dcloudio/uni-app';
import { ref } from 'vue';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import OrderButtons from './order-buttons.vue';
import DetailMap from './detail-map.vue';

const orderDelivery = ref<any>({});

// 加载订单详情
const loadOrderDeliveryDetail = async (order_id: number) => {
    try {
        uni.showLoading({
            title: '加载中...'
        });

        const response = await getOrderDeliveryInfo(order_id);

        // 更新订单详情
        orderDelivery.value = response.data;

        uni.hideLoading();
    } catch (error) {
        console.error('获取订单详情失败:', error);
        uni.hideLoading();
        uni.showToast({
            title: '获取订单详情失败',
            icon: 'none'
        });
    }
};

// 刷新订单详情
const refreshPage = () => {
    if (orderDelivery.value && orderDelivery.value.order_id) {
        loadOrderDeliveryDetail(orderDelivery.value.order_id);
    }
};

// 获取页面参数
onLoad((options: any) => {
    if (options && options.order_id) {
        loadOrderDeliveryDetail(parseInt(options.order_id));
    } else {
        uni.showToast({
            title: '订单ID不存在',
            icon: 'none'
        });
        setTimeout(() => {
            uni.navigateBack();
        }, 1500);
    }
});

// 拨打店铺电话
const callStore = () => {
    const mobile = orderDelivery.value?.orderAddress?.shipper_mobile;
    if (!mobile) {
        uni.showToast({
            title: '暂无联系方式',
            icon: 'none'
        });
        return;
    }

    uni.makePhoneCall({
        phoneNumber: mobile,
        fail: () => {
            uni.showToast({
                title: '拨打电话失败',
                icon: 'none'
            });
        }
    });
};

// 拨打客户电话
const callCustomer = () => {
    const mobile = orderDelivery.value?.orderAddress?.reciver_mobile;
    if (!mobile) {
        uni.showToast({
            title: '暂无联系方式',
            icon: 'none'
        });
        return;
    }

    uni.makePhoneCall({
        phoneNumber: mobile,
        fail: () => {
            uni.showToast({
                title: '拨打电话失败',
                icon: 'none'
            });
        }
    });
};
</script>

<style lang="scss" scoped>
.detail-container {
    background-color: #f5f5f5;
    min-height: 100vh;
    padding-bottom: 140rpx;
}

.status-card {
    background-color: #33a9ff;
    color: #fff;
    padding: 40rpx 30rpx;
    
    .status-title {
        font-size: 36rpx;
        font-weight: bold;
        margin-bottom: 16rpx;
    }
    
    .status-desc {
        font-size: 28rpx;
        opacity: 0.9;
    }
}

.info-card {
    background-color: #fff;
    border-radius: 12rpx;
    margin: 20rpx;
    padding: 24rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
    
    .card-title {
        font-size: 32rpx;
        font-weight: bold;
        color: #333;
        padding-bottom: 20rpx;
        border-bottom: 1rpx solid #f0f0f0;
        margin-bottom: 20rpx;
    }
    
    .info-item {
        display: flex;
        margin-bottom: 16rpx;
        
        &:last-child {
            margin-bottom: 0;
        }
        
        .label {
            width: 180rpx;
            font-size: 28rpx;
            color: #666;
        }
        
        .value {
            flex: 1;
            font-size: 28rpx;
            color: #333;
        }
        
        .phone-value {
            color: #33a9ff;
            text-decoration: underline;
        }
    }
    
    .total-price {
        margin-top: 16rpx;
        padding-top: 16rpx;
        border-top: 1rpx dashed #eee;
        
        .label {
            font-weight: bold;
            color: #333;
        }
        
        .price {
            color: #ff6b00;
            font-weight: bold;
        }
    }
}

.goods-list {
    .goods-item {
        display: flex;
        padding: 20rpx 0;
        border-bottom: 1rpx solid #f0f0f0;
        
        &:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
    }
    
    .goods-image {
        width: 140rpx;
        height: 140rpx;
        border-radius: 8rpx;
        margin-right: 20rpx;
        background-color: #f9f9f9;
    }
    
    .goods-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .goods-name {
        font-size: 30rpx;
        font-weight: 500;
        color: #333;
        margin-bottom: 8rpx;
    }
    
    .goods-sku {
        font-size: 26rpx;
        color: #999;
        margin-bottom: 16rpx;
    }
    
    .goods-price-qty {
        display: flex;
        justify-content: space-between;
        align-items: center;
        
        .goods-price {
            font-size: 32rpx;
            color: #ff6b00;
            font-weight: bold;
        }
        
        .goods-qty {
            font-size: 26rpx;
            color: #999;
        }
    }
}

.bottom-actions {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: flex-end;
    padding: 20rpx;
    background-color: #fff;
    box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
}
</style>
