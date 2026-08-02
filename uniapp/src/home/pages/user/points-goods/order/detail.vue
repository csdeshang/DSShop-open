<template>
    <view class="order-detail-container">
        <!-- 订单状态 -->
        <view class="status-section">
            <view class="status-icon">
                <uni-icons :type="getStatusIcon(orderDetail.order_status)" size="40" color="#fff"></uni-icons>
            </view>
            <view class="status-info">
                <view class="status-text">{{ orderDetail.order_status_desc }}</view>
                <view class="status-desc">{{ getStatusDescription(orderDetail.order_status) }}</view>
            </view>
        </view>

        <!-- 收货地址 -->
        <view class="ds-card" v-if="orderDetail.receiver_name">
            <view class="card-header">
                <uni-icons type="location" size="18"></uni-icons>
                <text class="card-title">收货地址</text>
            </view>
            <view class="info-item">
                <text class="info-label">收货人</text>
                <text class="info-value">{{ orderDetail.receiver_name }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">联系电话</text>
                <text class="info-value">{{ orderDetail.receiver_mobile }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">收货地址</text>
                <text class="info-value">{{ orderDetail.receiver_address }}</text>
            </view>
        </view>

        <!-- 快递信息 -->
        <view class="ds-card" v-if="orderDetail.express_no">
            <view class="card-header">
                <uni-icons type="car" size="18"></uni-icons>
                <text class="card-title">快递信息</text>
            </view>
            <view class="info-item">
                <text class="info-label">快递公司</text>
                <text class="info-value">{{ orderDetail.express_company }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">快递单号</text>
                <text class="info-value">{{ orderDetail.express_no }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.express_time">
                <text class="info-label">发货时间</text>
                <text class="info-value">{{ orderDetail.express_time }}</text>
            </view>
        </view>

        <!-- 订单信息 -->
        <view class="ds-card">
            <view class="card-header">
                <uni-icons type="info" size="18"></uni-icons>
                <text class="card-title">订单信息</text>
            </view>
            <view class="info-item">
                <text class="info-label">订单编号</text>
                <text class="info-value">{{ orderDetail.order_sn || '暂无' }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">下单时间</text>
                <text class="info-value">{{ orderDetail.create_at }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.express_time">
                <text class="info-label">发货时间</text>
                <text class="info-value">{{ orderDetail.express_time }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.receive_time">
                <text class="info-label">收货时间</text>
                <text class="info-value">{{ orderDetail.receive_time }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">配送方式</text>
                <text class="info-value">{{ orderDetail.delivery_method_desc }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.remark">
                <text class="info-label">订单备注</text>
                <text class="info-value">{{ orderDetail.remark }}</text>
            </view>
        </view>

        <!-- 商品信息 -->
        <view class="ds-card">
            <view class="card-header">
                <uni-icons type="gift" size="18"></uni-icons>
                <text class="card-title">商品信息</text>
            </view>
            <view class="goods-item" @click="goToGoodsDetail(orderDetail.goods_id)">
                <image class="goods-image" :src="formatImageUrl(orderDetail.goods_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill" />
                <view class="goods-info">
                    <view class="goods-name">{{ orderDetail.goods_name }}</view>
                    <view class="goods-price-qty">
                        <text class="goods-points">{{ orderDetail.points_price }}积分</text>
                        <text class="goods-qty">x{{ orderDetail.exchange_num }}</text>
                    </view>
                </view>
            </view>
        </view>

        <!-- 积分信息 -->
        <view class="ds-card">
            <view class="card-header">
                <uni-icons type="wallet" size="18"></uni-icons>
                <text class="card-title">积分信息</text>
            </view>
            <view class="info-item">
                <text class="info-label">单价积分</text>
                <text class="info-value">{{ orderDetail.points_price }}积分</text>
            </view>
            <view class="info-item">
                <text class="info-label">兑换数量</text>
                <text class="info-value">{{ orderDetail.exchange_num }}件</text>
            </view>
            <view class="info-item">
                <text class="info-label">总积分</text>
                <text class="info-value points-total">{{ orderDetail.total_points }}积分</text>
            </view>
        </view>

        <!-- 订单日志 -->
        <view class="ds-card" v-if="orderDetail.order_log_list && orderDetail.order_log_list.length > 0">
            <view class="card-header">
                <uni-icons type="list" size="18"></uni-icons>
                <text class="card-title">订单日志</text>
            </view>

            <view class="log-item" v-for="(log, index) in orderDetail.order_log_list" :key="index">
                <view class="log-content">{{ log.message }}</view>
                <view class="log-time">{{ log.create_at }}</view>
            </view>
        </view>

        <!-- 底部操作按钮 -->
        <view class="footer-actions" v-if="orderDetail.user_available_actions && orderDetail.user_available_actions.length > 0">
            <button class="ds-btn-md" v-if="orderDetail.user_available_actions.includes('cancel')"
                @click="cancelOrder(orderDetail)">取消订单</button>
            <button class="ds-btn-md primary" v-if="orderDetail.user_available_actions.includes('confirm')"
                @click="confirmReceive(orderDetail)">确认收货</button>
            <button class="ds-btn-md" v-if="orderDetail.user_available_actions.includes('evaluate')"
                @click="evaluateOrder(orderDetail)">评价</button>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { ref, reactive, onMounted } from 'vue';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import {
    getPointsGoodsOrderDetail,
    cancelPointsGoodsOrder,
    confirmPointsGoodsOrder
} from '@/home/api/user/userPointsGoodsOrder';
import { onLoad, onShow } from '@dcloudio/uni-app';

// 订单详情
const orderDetail = reactive({
    id: 0,
    order_sn: '',
    order_status: 0,
    order_status_desc: '',
    delivery_method: 'express',
    delivery_method_desc: '',
    receiver_name: '',
    receiver_mobile: '',
    receiver_address: '',
    express_company: '',
    express_no: '',
    express_time: 0,
    receive_time: 0,
    goods_id: 0,
    goods_name: '',
    goods_image: '',
    points_price: 0,
    exchange_num: 0,
    total_points: 0,
    remark: '',
    create_at: 0,
    update_at: 0,
    user_available_actions: []
});

// 页面参数 - 存储订单ID，用于页面显示时刷新数据
let orderId = 0;

/**
 * 页面加载时获取参数
 * 只在页面首次加载时执行一次，用于获取订单ID参数
 */
onLoad((options: any) => {
    if (options && options.id) {
        orderId = parseInt(options.id);
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

/**
 * 页面显示时刷新数据
 * 每次页面显示时都会执行，确保数据是最新的
 * 适用场景：
 * 1. 用户从评价页面返回时，订单状态需要更新
 * 2. 用户从后台恢复应用时，数据需要刷新
 * 3. 用户从其他页面返回时，确保看到最新状态
 */
onShow(() => {
    if (orderId > 0) {
        loadOrderDetail(orderId);
    }
});

// 加载订单详情
const loadOrderDetail = async (order_id: number) => {
    try {
        uni.showLoading({
            title: '加载中...'
        });

        const response = await getPointsGoodsOrderDetail(order_id);

        // 更新订单详情
        Object.assign(orderDetail, response.data);

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

// 获取状态图标
const getStatusIcon = (status: number) => {
    const iconMap = {
        0: 'closeempty',     // 已取消
        10: 'notification',  // 待发货
        20: 'car',           // 已发货
        30: 'checkmarkempty', // 已收货
        40: 'checkmarkempty' // 已完成
    };
    return iconMap[status] || 'info';
};


// 获取状态描述
const getStatusDescription = (status: number) => {
    const descMap = {
        0: '订单已取消',
        10: '商家正在处理您的订单',
        20: '商品已发出，请注意查收',
        30: '订单已完成，感谢您的兑换',
        40: '订单已完成，感谢您的兑换'
    };
    return descMap[status] || '';
};



// 取消订单
const cancelOrder = (orderInfo: any) => {
    uni.showModal({
        title: '提示',
        content: '确定要取消该订单吗？',
        success: async (res) => {
            if (res.confirm) {
                try {
                    await cancelPointsGoodsOrder(orderInfo.id);
                    uni.showToast({
                        title: '订单已取消',
                        icon: 'success'
                    });
                    // 重新加载订单详情
                    loadOrderDetail(orderInfo.id);
                } catch (error) {
                    console.error('取消订单失败:', error);
                    uni.showToast({
                        title: '取消订单失败',
                        icon: 'none'
                    });
                }
            }
        }
    });
};

// 确认收货
const confirmReceive = (orderInfo: any) => {
    uni.showModal({
        title: '提示',
        content: '确认已收到商品吗？',
        success: async (res) => {
            if (res.confirm) {
                try {
                    await confirmPointsGoodsOrder(orderInfo.id);
                    uni.showToast({
                        title: '确认收货成功',
                        icon: 'success'
                    });
                    // 重新加载订单详情
                    loadOrderDetail(orderInfo.id);
                } catch (error) {
                    console.error('确认收货失败:', error);
                    uni.showToast({
                        title: '确认收货失败',
                        icon: 'none'
                    });
                }
            }
        }
    });
};

// 评价订单
const evaluateOrder = (orderInfo: any) => {
    uni.navigateTo({
        url: `/home/pages/user/points-goods/evaluate/add?order_id=${orderInfo.id}`
    });
};

// 跳转到商品详情页
const goToGoodsDetail = (goodsId: number) => {
    if (goodsId) {
        uni.navigateTo({
            url: `/home/pages/points-goods/goods/detail/index?id=${goodsId}`
        });
    } else {
        uni.showToast({
            title: '商品信息不完整',
            icon: 'none'
        });
    }
};
</script>

<style lang="scss" scoped>
.order-detail-container {
    padding-bottom: 120rpx;
    background-color: #f5f5f5;
}

.status-section {
    display: flex;
    align-items: center;
    padding: 40rpx 30rpx;
    background: linear-gradient(135deg, #FF6034, #FF8C19);
    color: #fff;
    margin: 0 0 20rpx 0;
    border-radius: 0;
}

.status-icon {
    width: 100rpx;
    height: 100rpx;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.2);
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 30rpx;
}

.status-info {
    flex: 1;
}

.status-text {
    font-size: 36rpx;
    font-weight: bold;
    margin-bottom: 10rpx;
}

.status-desc {
    font-size: 26rpx;
    opacity: 0.8;
}

.goods-item {
    display: flex;
    align-items: center;
    padding: 20rpx 0;
    border-bottom: 1rpx solid #f0f0f0;
    cursor: pointer;
    transition: background-color 0.2s;
    
    &:active {
        background-color: #f8f8f8;
    }
}

.goods-image {
    width: 160rpx;
    height: 160rpx;
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
    font-size: 28rpx;
    color: #333;
    margin-bottom: 10rpx;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
}

.goods-price-qty {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.goods-points {
    font-size: 28rpx;
    color: #FF6034;
    font-weight: bold;
}

.goods-qty {
    font-size: 24rpx;
    color: #999;
}

.points-total {
    color: #FF6034;
    font-weight: bold;
    font-size: 32rpx;
}

.log-item {
    padding: 20rpx 0;
    border-bottom: 1rpx solid #f0f0f0;
}

.log-item:last-child {
    border-bottom: none;
}

.log-content {
    font-size: 26rpx;
    color: #333;
    margin-bottom: 10rpx;
}

.log-time {
    font-size: 24rpx;
    color: #999;
}

.footer-actions {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: flex-end;
    padding: 20rpx 30rpx;
    background-color: #fff;
    box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
    z-index: 100;
}
</style>
