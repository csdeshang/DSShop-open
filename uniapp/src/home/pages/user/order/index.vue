<template>
    <view class="order-container">
        <z-paging ref="paging" v-model="orders" @query="queryList">
            <!-- 订单状态选项卡 - 使用z-paging的fixed特性 -->
            <template v-slot:top>
                <view class="order-tabs">
                    <view v-for="(tab, index) in tabs" :key="index" class="tab-item"
                        :class="{ active: currentTab === index }" @click="switchTab(index)">
                        {{ tab.label }}
                    </view>
                </view>
            </template>

            <!-- 订单列表 -->
            <view class="order-list" v-if="orders.length > 0">
                <view class="order-item" v-for="order in orders" :key="order.id">
                    <!-- 订单头部信息 -->
                    <view class="order-header">
                        <view class="store-name">商品信息</view>
                        <view class="order-status">{{ order.display_status_desc }}</view>
                    </view>

                    <!-- 订单商品列表 -->
                    <view class="order-goods" v-for="goods in order.orderGoodsList" :key="goods.id"
                        @click="goToOrderDetail(order.id)">
                        <image class="goods-image" :src="formatImageUrl(goods.goods_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill" />
                        <view class="goods-info">
                            <view class="goods-name">
                                <text v-if="goods.promotion_type" class="promotion-type">{{ goods.promotion_type_desc }}</text>
                                {{ goods.goods_name }}
                            </view>
                            <view class="goods-sku" v-if="goods.sku_name">{{ goods.sku_name }}</view>
                            <view class="goods-price-qty">
                                <text class="goods-price">¥{{ goods.pay_price }}</text>
                                <text class="goods-qty">x{{ goods.goods_num }}</text>
                            </view>
                        </view>
                    </view>

                    <!-- 订单金额信息 -->
                    <view class="order-total">
                        <text>共{{ getTotalQuantity(order.orderGoodsList) }}件商品</text>
                        <text>实付款：<text class="price">¥{{ order.pay_amount }}</text></text>
                    </view>

                    <!-- 订单操作按钮 -->
                    <view class="order-actions">
                        <button class="ds-btn-md" v-if="order.user_available_actions?.includes('close')"
                            @click="closeOrder(order)">关闭订单</button>
                        <button class="ds-btn-md" v-if="order.user_available_actions?.includes('cancel')"
                            @click="cancelOrder(order)">取消订单</button>
                        <button class="ds-btn-md" v-if="order.user_available_actions?.includes('confirm')"
                            @click="confirmReceive(order)">确认收货</button>
                        <button class="ds-btn-md" v-if="order.user_available_actions?.includes('evaluate')"
                            @click="evaluateOrder(order)">评价</button>
                        <button class="ds-btn-md" v-if="order.user_available_actions?.includes('invoice')"
                            @click="applyInvoice(order)">申请开票</button>
                        <button class="ds-btn-md" v-if="order.user_available_actions?.includes('pay')"
                            @click="payOrder(order)">去支付</button>
                    </view>
                </view>
            </view>
        </z-paging>
    </view>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import { goPlatformIndexPage } from '@/utils/platform';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import {
    getTblOrderPages,
    cancelTblOrder,
    confirmTblOrder,
    closeTblOrder,
} from '@/home/api/tbl-order/tblOrder';


// 定义类型
interface OrderGoods {
    id: number;
    goods_image: string;
    goods_name: string;
    sku_name?: string;
    goods_num: number;
    pay_price: number;
}

interface Order {
    id: number;
    order_status_desc: string;
    display_status_desc: string;
    orderGoodsList: OrderGoods[];
    order_amount: number;
    user_available_actions?: string[];
    pay_amount: number;
    order_status: string | number;
}

// 定义订单状态选项卡（参考技师端实现）
const tabs = [
    { label: '全部', value: '' },
    { label: '待付款', value: 'pending' },
    { label: '待收货/使用', value: 'accepted' },
    { label: '待评价', value: 'comment' },
    { label: '退款', value: 'refund' }
];

// 当前选中的选项卡
const currentTab = ref(0);

// 订单列表
const orders = ref<Order[]>([]);

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
        const result = await getTblOrderPages({
            page_current,
            page_size,
            tab_selected: tabs[currentTab.value].value
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

// 计算商品总数量
const getTotalQuantity = (goods: OrderGoods[]): number => {
    return goods.reduce((total, item) => total + item.goods_num, 0);
};

// 跳转到订单详情
const goToOrderDetail = (orderId: number) => {
    uni.navigateTo({
        url: `/home/pages/user/order/detail?id=${orderId}`
    });
};


const payOrder = (order: Order) => {
    let params = {
        source_type: 'order',
        source_id: order.id,
        pay_amount: order.pay_amount,
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

// 取消订单
const cancelOrder = async (orderInfo: Order) => {
    uni.showModal({
        title: '提示',
        content: '确定要取消该订单吗？',
        success: async (res) => {
            if (res.confirm) {
                try {
                    // 调用取消订单API
                    await cancelTblOrder(orderInfo.id);
                    uni.showToast({
                        title: '订单已取消',
                        icon: 'success'
                    });
                    // 刷新列表
                    if (paging.value) {
                        paging.value.reload();
                    }
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

// 关闭订单
const closeOrder = async (orderInfo: Order) => {
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
                    // 刷新列表
                    if (paging.value) {
                        paging.value.reload();
                    }
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

// 确认收货
const confirmReceive = async (orderInfo: Order) => {
    uni.showModal({
        title: '提示',
        content: '确认已收到商品吗？',
        success: async (res) => {
            if (res.confirm) {
                try {
                    // 调用确认收货API
                    const result = await confirmTblOrder(orderInfo.id);
                    if (result.code == 10000) {
                        uni.showToast({
                            title: '确认收货成功',
                            icon: 'success'
                        });
                        // 刷新列表
                        if (paging.value) {
                            paging.value.reload();
                        }
                    } else {
                        uni.showToast({
                            title: result.message,
                            icon: 'none'
                        });
                    }
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
const evaluateOrder = (orderInfo: Order) => {
    uni.navigateTo({
        url: `/home/pages/user/evaluate/add?order_id=${orderInfo.id}`
    });
};

// 申请开票（与订单详情页一致）
const applyInvoice = (orderInfo: Order) => {
    uni.navigateTo({
        url: `/home/pages/user/order/invoice-apply?order_id=${orderInfo.id}`
    });
};

// 页面显示时刷新数据
onShow(() => {
    if (paging.value) {
        paging.value.reload();
    }
});


/**
 * 页面加载时获取参数
 * 只在页面首次加载时执行一次，用于获取订单状态参数
 */
 onLoad((options: any) => {
    if (options && options.tab_selected) {
        // 根据传入的tab_selected找到对应的tab索引
        const tabIndex = tabs.findIndex(tab => tab.value === options.tab_selected);
        if (tabIndex !== -1) {
            currentTab.value = tabIndex;
        }
    }
});

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
        color: #FF6034;
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
        background-color: #FF6034;
        border-radius: 2rpx;
    }
}

.order-list {
    padding: 20rpx;
    margin-bottom: 20rpx;
}

.order-item {
    background-color: #fff;
    border-radius: 10rpx;
    margin-bottom: 20rpx;
    padding: 20rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20rpx;
        border-bottom: 1rpx solid #f0f0f0;

        .store-name {
            font-size: 28rpx;
            font-weight: bold;
            color: #333;
        }

        .order-status {
            font-size: 26rpx;
            color: #FF6034;
        }
    }

    .order-goods {
        display: flex;
        padding: 20rpx 0;
        border-bottom: 1rpx solid #f0f0f0;

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

                .goods-price {
                    font-size: 28rpx;
                    color: #FF6034;
                    font-weight: bold;
                }

                .goods-qty {
                    font-size: 24rpx;
                    color: #999;
                }
            }
        }
    }

    .order-total {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 20rpx 0;
        font-size: 26rpx;
        color: #666;

        .price {
            font-size: 30rpx;
            color: #FF6034;
            font-weight: bold;
            margin-left: 10rpx;
        }
    }

    .order-actions {
        display: flex;
        justify-content: flex-end;
        padding-top: 20rpx;
    }
}
</style>
