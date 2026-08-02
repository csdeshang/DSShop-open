<template>
    <view class="order-detail-container">
        <!-- 订单状态 -->
        <view class="status-section">
            <view class="status-icon">
                <uni-icons :type="getStatusIcon(orderDetail.order_status)" size="40" color="#fff"></uni-icons>
            </view>
            <view class="status-info">
                <view class="status-text">{{ orderDetail.display_status_desc }}</view>
            </view>
        </view>


        <!-- 外卖订单、已付款，未完成，已接单状态 需要显示配送员位置信息 -->
        <DetailRider v-if="orderDetail.delivery_method == 'rider' && (orderDetail.order_status == 30 || orderDetail.order_status == 40 || orderDetail.order_status == 50) && orderDetail.orderDelivery.rider_id != 0" :order-detail="orderDetail" />


        <!--服务订单 已付款，未完成，已接单状态 需要显示师傅位置信息 -->
        <DetailTechnician v-if="orderDetail.delivery_method == 'technician' && (orderDetail.order_status == 30 || orderDetail.order_status == 40 || orderDetail.order_status == 50) && orderDetail.orderDelivery.technician_id != 0" :order-detail="orderDetail" />


        <!-- 收货地址 -->
        <view class="ds-card" v-if="orderDetail.orderAddress">
            <view class="card-header">
                <uni-icons type="location" size="18"></uni-icons>
                <text class="card-title">收货地址</text>
            </view>
            <view class="info-item">
                <text class="info-label">收货人</text>
                <text class="info-value">{{ orderDetail.orderAddress.reciver_name }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">联系电话</text>
                <text class="info-value">{{ orderDetail.orderAddress.reciver_mobile }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">收货地址</text>
                <text class="info-value">{{ orderDetail.orderAddress.reciver_address }}</text>
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
                <text class="info-value">{{ orderDetail.add_time }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.payment_time">
                <text class="info-label">支付时间</text>
                <text class="info-value">{{ orderDetail.payment_time }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.delivery_time">
                <text class="info-label">交付时间</text>
                <text class="info-value">{{ orderDetail.delivery_time }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.shipping_time">
                <text class="info-label">发货时间</text>
                <text class="info-value">{{ orderDetail.shipping_time }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.finnshed_time">
                <text class="info-label">完成时间</text>
                <text class="info-value">{{ orderDetail.finnshed_time }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.pay_channel">
                <text class="info-label">支付方式</text>
                <text class="info-value">{{ orderDetail.pay_channel_desc }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">配送方式</text>
                <text class="info-value">{{ orderDetail.delivery_method_desc }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.user_remark">
                <text class="info-label">订单备注</text>
                <text class="info-value">{{ orderDetail.user_remark }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.invoice_status_desc">
                <text class="info-label">开票状态</text>
                <text class="info-value">{{ orderDetail.invoice_status_desc }}</text>
            </view>
            <view class="info-item" v-if="orderDetail.id">
                <text class="info-label">开票记录</text>
                <text class="info-value link-invoice" @click="goInvoiceRecords(orderDetail)">查看</text>
            </view>
        </view>

        <!-- 商品信息 -->
        <view class="ds-card">
            <view class="card-header">
                <uni-icons type="shop" size="18"></uni-icons>
                <text class="card-title">商品信息</text>
                <!-- <button class="ds-btn-sm" @click="contactStore">联系商家</button> -->
            </view>

            <!-- 商品列表 -->
            <view class="goods-list">
                <view v-for="(goods, index) in orderDetail.orderGoodsList" :key="goods.id">
                    <view class="goods-item"
                        @click="goGoodsDetailPage({ id: goods.goods_id, platform: orderDetail.platform })">
                        <image class="goods-image" :src="formatImageUrl(goods.goods_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill" />
                        <view class="goods-info">
                            <view class="goods-name">
                                <text v-if="goods.promotion_type" class="promotion-type">{{ goods.promotion_type_desc
                                    }}</text>
                                {{ goods.goods_name }}
                            </view>
                            <view class="goods-sku" v-if="goods.sku_name">{{ goods.sku_name }}</view>
                            <view class="goods-price-qty">
                                <text class="goods-price">¥{{ goods.pay_price }}</text>
                                <text class="goods-qty">x{{ goods.goods_num }}</text>
                            </view>
                        </view>
                    </view>

                    <view class="mt-20">
                        <button class="ds-btn-sm"
                            v-if="!orderRefundGoods(goods) && orderDetail.user_available_actions.includes('refund')"
                            @click="applyRefundGoods(goods)">申请退款</button>
                        <button class="ds-btn-sm ds-btn-white" v-if="orderRefundGoods(goods)"
                            @click="viewRefund(orderRefundGoods(goods))">查看退款</button>
                    </view>
                </view>
            </view>
        </view>

        <!-- 订单金额 -->
        <view class="ds-card">
            <view class="card-header">
                <uni-icons type="wallet" size="18"></uni-icons>
                <text class="card-title">订单金额</text>
            </view>
            <view class="info-item">
                <text class="info-label">商品原价</text>
                <text class="info-value">¥{{ orderDetail.original_amount }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">商品优惠价</text>
                <text class="info-value">¥{{ orderDetail.goods_amount }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">运费</text>
                <text class="info-value">¥{{ orderDetail.shipping_amount }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">优惠金额</text>
                <text class="info-value">-¥{{ orderDetail.discount_amount }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">订单总价</text>
                <text class="info-value">¥{{ orderDetail.order_amount }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">实付款</text>
                <text class="info-value">¥{{ orderDetail.pay_amount }}</text>
            </view>
        </view>

        <!-- 订单日志 -->
        <view class="ds-card" v-if="orderDetail.orderLogList && orderDetail.orderLogList.length > 0">
            <view class="card-header">
                <uni-icons type="list" size="18"></uni-icons>
                <text class="card-title">订单日志</text>
            </view>

            <view class="log-item" v-for="(log, index) in orderDetail.orderLogList" :key="index">
                <view class="log-content">{{ log.message }}</view>
                <view class="log-time">{{ log.create_at }}</view>
            </view>

        </view>

        <!-- 底部操作按钮 -->
        <view class="footer-actions" v-if="orderDetail.user_available_actions.length > 0">
            <button class="ds-btn-md" v-if="orderDetail.user_available_actions.includes('close')"
                @click="closeOrder(orderDetail)">关闭订单</button>

            <!-- <button class="ds-btn-md" v-if="orderDetail.user_available_actions.includes('delete')"
                @click="deleteOrder(orderDetail)">删除订单</button> -->

            <button class="ds-btn-md" v-if="orderDetail.user_available_actions.includes('cancel')"
                @click="cancelOrder(orderDetail)">取消订单</button>
            <!-- 退款记录为空 且 -->
            <button class="ds-btn-md"
                v-if="orderDetail.user_available_actions.includes('refund') && orderDetail.orderRefundList.length == 0"
                @click="applyRefund(orderDetail)">申请退款</button>
            <button class="ds-btn-md primary" v-if="orderDetail.user_available_actions.includes('confirm')"
                @click="confirmReceive(orderDetail)">确认收货</button>
            <button class="ds-btn-md" v-if="orderDetail.user_available_actions.includes('evaluate')"
                @click="evaluateOrder(orderDetail)">评价</button>

            <button class="ds-btn-md" v-if="orderDetail.user_available_actions.includes('invoice')"
                @click="applyInvoice(orderDetail)">申请开票</button>

            <button class="ds-btn-md" v-if="orderDetail.user_available_actions.includes('pay')"
                @click="payOrder(orderDetail)">去支付</button>
        </view>

    </view>
</template>

<script lang="ts" setup>
import { ref, reactive, computed } from 'vue';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import {
    getTblOrderInfo,
    cancelTblOrder,
    confirmTblOrder,
    closeTblOrder
} from '@/home/api/tbl-order/tblOrder';
import {
    INVOICE_STATUS_NOT_SUBMITTED,
} from '@/home/api/tbl-order/tblOrderInvoice';

import DetailRider from './detail-rider.vue'
import DetailTechnician from './detail-technician.vue'
import { goGoodsDetailPage } from '@/utils/platform'
import { onLoad, onShow } from '@dcloudio/uni-app';



// 订单详情
const orderDetail = reactive({
    id: 0,
    order_sn: '',
    order_status: 0,
    order_status_desc: '',
    display_status_desc: '',
    delivery_method: '',
    delivery_method_desc: '',
    original_amount: '0.00',
    goods_amount: '0.00',
    shipping_amount: '0.00',
    discount_amount: '0.00',
    order_amount: '0.00',
    pay_amount: '0.00',
    pay_channel: '',
    pay_channel_desc: '',
    add_time: 0,
    payment_time: 0,
    delivery_time: 0,
    shipping_time: 0,
    finnshed_time: 0,
    user_remark: '',
    orderGoodsList: [],
    orderAddress: {},
    orderLogList: [],
    user_available_actions: [],
    orderRefundList: [],
    invoice_status: INVOICE_STATUS_NOT_SUBMITTED,
    invoice_status_desc: ''
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
 * 1. 用户从退款申请页面返回时，订单状态需要更新
 * 2. 用户从评价页面返回时，订单状态需要更新
 * 3. 用户从后台恢复应用时，数据需要刷新
 * 4. 用户从其他页面返回时，确保看到最新状态
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

        const response = await getTblOrderInfo(order_id);

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

// 获取状态图标 - 根据TblOrderEnum.php的实际状态
const getStatusIcon = (status: number) => {
    const iconMap = {
        0: 'closeempty',   // 已关闭
        10: 'closeempty',  // 已取消
        20: 'wallet',      // 待支付
        30: 'notification', // 已付款/待发货
        40: 'car',         // 已接受/已发货
        50: 'checkmarkempty', // 店铺已确认/派送中
        100: 'checkmarkempty' // 订单完成
    };
    return iconMap[status] || 'info';
};






// 联系商家
const contactStore = () => {
    uni.showToast({
        title: '联系商家功能开发中',
        icon: 'none'
    });
};


// 是否存在可退款商品，全额退款则每个商品都存在退款记录，部分退款则只存在一个退款记录
const orderRefundGoods = (orderGoods: any) => {
    // 判断是否存在退款记录
    if (orderDetail.orderRefundList.length > 0) {
        const refundGoods = orderDetail.orderRefundList.find(item => item.order_goods_id === orderGoods.id || item.order_goods_id === 0);
        return refundGoods;
    }
}

// 取消订单
const cancelOrder = (orderInfo: any) => {
    uni.showModal({
        title: '提示',
        content: '确定要取消该订单吗？',
        success: async (res) => {
            if (res.confirm) {
                try {
                    await cancelTblOrder(orderInfo.id);
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

const payOrder = (orderInfo: any) => {

    let params = {
        source_type: 'order',
        source_id: orderInfo.id,
        pay_amount: orderInfo.pay_amount,
        pay_info: '支付订单'
    }
    // 转换参数为查询字符串
    const queryString = Object.keys(params)
        .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(params[key]))
        .join('&');
    uni.navigateTo({
        url: '/home/pages/user/pay/index?' + queryString
    });

};

// 申请退款
const applyRefund = (orderInfo: any) => {
    uni.navigateTo({
        url: `/home/pages/user/refund/apply?order_id=${orderInfo.id}`
    });
};

const applyInvoice = (orderInfo: any) => {
    uni.navigateTo({
        url: `/home/pages/user/order/invoice-apply?order_id=${orderInfo.id}`
    });
};

const goInvoiceRecords = (orderInfo: any) => {
    uni.navigateTo({
        url: `/home/pages/user/order/invoice-list?order_id=${orderInfo.id}`,
    });
};

// 申请单个商品退款
const applyRefundGoods = (goodsInfo: any) => {
    uni.navigateTo({
        url: `/home/pages/user/refund/apply?order_id=${goodsInfo.order_id}&order_goods_id=${goodsInfo.id}`
    });
};

// 查看退款
const viewRefund = (refundInfo: any) => {
    uni.navigateTo({
        url: `/home/pages/user/refund/view?id=${refundInfo.id}`
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
                    await confirmTblOrder(orderInfo.id);
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

// 关闭订单
const closeOrder = async (orderInfo: any) => {
    uni.showModal({
        title: '提示',
        content: '确定要关闭该订单吗？',
        success: async (res) => {
            if (res.confirm) {
                try {
                    // 调用关闭订单API
                    await closeTblOrder(orderInfo.id);
                    uni.showToast({
                        title: '订单已关闭',
                        icon: 'success'
                    });
                    // 重新加载订单详情
                    loadOrderDetail(orderInfo.id);
                } catch (error) {
                    console.error('关闭订单失败:', error);
                    uni.showToast({
                        title: '关闭订单失败',
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
        url: `/home/pages/user/evaluate/add?order_id=${orderInfo.id}`
    });
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
    display: flex;
    align-items: center;
}

.status-text {
    font-size: 36rpx;
    font-weight: bold;
    line-height: 1.4;
    letter-spacing: 1rpx;
}





.goods-list {
    margin-top: 20rpx;
}

.goods-item {
    display: flex;
    padding: 20rpx 0;
    border-bottom: 1rpx solid #f0f0f0;
}

.goods-item:last-child {
    border-bottom: none;
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

.goods-sku {
    font-size: 24rpx;
    color: #999;
    margin-bottom: 10rpx;
}

.goods-price-qty {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.goods-price {
    font-size: 28rpx;
    color: #FF6034;
    font-weight: bold;
}

.goods-qty {
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

.link-invoice {
    color: #ff6034;
}
</style>
